<?php
// app/Http/Controllers/MaintenanceWebhookController.php

namespace App\Http\Controllers;

use App\Events\MaintenanceRequestReceived;
use App\Models\MaintenanceRequest;
use App\Models\Native\NativeRequest;
use App\Models\Native\NativeUrgencyLevel;
use App\Models\Requester;
use App\Models\Manager;
use App\Models\UrgencyLevel;
use App\Models\MaintenanceAttachment;
use App\Models\MaintenanceLink;
use App\Models\Store;
use App\Models\WebhookNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaintenanceWebhookController extends Controller
{
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $payload = $request->all();

            // Validate required fields
            $this->validatePayload($payload);

            // Check if this request already exists
            if (MaintenanceRequest::where('webhook_id', $payload['Id'])->exists()) {
                return response()->json(['message' => 'Request already processed'], 200);
            }

            // Create or get requester
            $requester = $this->createOrGetRequester($payload['Name']);

            // Create or get manager
            $manager = $this->createOrGetManager($payload['TheGroupManagerWhoReviewedThisIssueBeforeSubmittingIt']);

            // Get urgency level
            $urgencyLevel = UrgencyLevel::where('name', $payload['UrgencyLevel'])->first();
            if (!$urgencyLevel) {
                throw new \Exception("Invalid urgency level: {$payload['UrgencyLevel']}");
            }

            // Handle store - extract store number and create/get store
            $storeId = $this->handleStore($payload['Store']);

            // Create maintenance request
            $maintenanceRequest = MaintenanceRequest::create([
                'form_id' => $payload['Form']['Id'],
                'store_id' => $storeId, // Use store_id instead of store string
                'description_of_issue' => $payload['DescriptionOfTheIssue'],
                'urgency_level_id' => $urgencyLevel->id,
                'equipment_with_issue' => $payload['TheEquipmentWhichHasTheIssue'],
                'basic_troubleshoot_done' => $payload['DidYouGoThroughTheBasicTroubleshootsAndTheIssueStillNotFixed'],
                'request_date' => Carbon::parse($payload['TodaysDate'])->format('Y-m-d'),
                'date_submitted' => Carbon::parse($payload['Entry']['DateSubmitted'])->format('Y-m-d H:i:s'),
                'entry_number' => $payload['Entry']['Number'],
                'status' => 'received',
                'requester_id' => $requester->id,
                'reviewed_by_manager_id' => $manager->id,
                'webhook_id' => $payload['Id']
            ]);

            // Create corresponding native request immediately (within same transaction)
            // Build external requester name from CognitoForms requester
            $externalRequester = trim($requester->first_name . ' ' . $requester->last_name);
            
            // Map urgency level from maintenance to native
            $nativeUrgencyLevelId = $this->mapUrgencyLevel($maintenanceRequest->urgencyLevel);
            
            // Find the store manager user ID for this store
            // Fallback to maintenance request's requester_id if no match found
            $storeManagerUserId = $this->findStoreManagerUserId($maintenanceRequest->store_id, $manager) 
                ?? $maintenanceRequest->requester_id;
            
            // Validate that the requester_id exists in users table
            $userExists = $storeManagerUserId && \App\Models\User::where('id', $storeManagerUserId)->exists();
            
            if (!$userExists) {
                // If user doesn't exist, try to find any active user as fallback
                $fallbackUser = \App\Models\User::where('is_active', true)->first();
                
                if ($fallbackUser) {
                    $storeManagerUserId = $fallbackUser->id;
                    Log::warning('Using fallback user for native request', [
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'original_requester_id' => $maintenanceRequest->requester_id,
                        'fallback_user_id' => $fallbackUser->id,
                        'fallback_user_name' => $fallbackUser->name,
                        'external_requester' => $externalRequester,
                    ]);
                } else {
                    Log::error('No valid user found for native request, skipping creation', [
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'requester_id' => $storeManagerUserId,
                        'external_requester' => $externalRequester,
                    ]);
                    $storeManagerUserId = null;
                }
            }
            
            // Only create native request if we have a valid user ID
            if ($storeManagerUserId) {
                // Create the native request with CognitoForms tracking
                NativeRequest::create([
                    'store_id' => $maintenanceRequest->store_id,
                    'requester_id' => $storeManagerUserId,
                    'external_requester' => $externalRequester,
                    'is_from_cognito' => true,
                    'equipment_with_issue' => $maintenanceRequest->equipment_with_issue,
                    'description_of_issue' => $maintenanceRequest->description_of_issue,
                    'urgency_level_id' => $nativeUrgencyLevelId,
                    'basic_troubleshoot_done' => (bool) $maintenanceRequest->basic_troubleshoot_done,
                    'request_date' => $maintenanceRequest->request_date ?? $maintenanceRequest->date_submitted,
                    'status' => 'received', // Set to 'received' for CognitoForms requests
                    'assigned_to' => null,
                    'costs' => null,
                    'how_we_fixed_it' => null,
                    'maintenance_request_id' => $maintenanceRequest->id,
                ]);
                
                Log::info('Native request created from CognitoForms webhook', [
                    'maintenance_request_id' => $maintenanceRequest->id,
                    'native_request_requester_id' => $storeManagerUserId,
                    'external_requester' => $externalRequester,
                    'is_from_cognito' => true,
                ]);
            }

            // Handle attachments
            if (isset($payload['ImagesVideos']) && is_array($payload['ImagesVideos'])) {
                $this->createAttachments($maintenanceRequest->id, $payload['ImagesVideos']);
            }

            // Handle links
            $this->createLinks($maintenanceRequest->id, $payload['Entry']);

            DB::commit();

            $this->sendNotifications($maintenanceRequest);

            Log::info('Maintenance request created successfully', [
                'request_id' => $maintenanceRequest->id,
                'webhook_id' => $payload['Id']
            ]);

            return response()->json([
                'message' => 'Maintenance request processed successfully',
                'request_id' => $maintenanceRequest->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Failed to process maintenance webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload ?? null
            ]);

            return response()->json([
                'message' => 'Failed to process request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle store extraction and creation/retrieval
     * Takes "1 - Chatterton" format and returns store ID
     */
    private function handleStore(string $storeString): int
    {
        // Extract store number and name from "1 - Chatterton"
        $parts = explode(' - ', $storeString, 2);

        if (count($parts) < 2) {
            throw new \Exception("Invalid store format: {$storeString}. Expected format: 'number - name'");
        }

        $storeNumber = trim($parts[0]);
        $storeName = trim($parts[1]);

        // First try to find store by store_number
        $store = Store::where('store_number', $storeNumber)->first();

        if ($store) {
            // Update name if it's different or empty
            if (empty($store->name) || $store->name !== $storeName) {
                $store->update(['name' => $storeName]);
                Log::info("Updated store name", [
                    'store_id' => $store->id,
                    'store_number' => $storeNumber,
                    'old_name' => $store->name,
                    'new_name' => $storeName
                ]);
            }
            return $store->id;
        }

        // Store doesn't exist, create new one
        $newStore = Store::create([
            'store_number' => $storeNumber,
            'name' => $storeName,
            'is_active' => true
        ]);

        Log::info("Created new store", [
            'store_id' => $newStore->id,
            'store_number' => $storeNumber,
            'name' => $storeName
        ]);

        return $newStore->id;
    }

    private function validatePayload(array $payload): void
    {
        $requiredFields = [
            'Id', 'Form.Id', 'Name.First', 'Store', 'DescriptionOfTheIssue',
            'UrgencyLevel', 'TheEquipmentWhichHasTheIssue',
            'DidYouGoThroughTheBasicTroubleshootsAndTheIssueStillNotFixed',
            'TodaysDate', 'Entry.DateSubmitted', 'Entry.Number',
            'TheGroupManagerWhoReviewedThisIssueBeforeSubmittingIt.First'
        ];

        foreach ($requiredFields as $field) {
            if (!data_get($payload, $field)) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
    }

    private function createOrGetRequester(array $nameData): Requester
    {
        return Requester::firstOrCreate([
            'first_name' => $nameData['First'],
            'last_name' => $nameData['Last']
        ]);
    }

    private function createOrGetManager(array $managerData): Manager
    {
        return Manager::firstOrCreate([
            'first_name' => $managerData['First'],
            'last_name' => $managerData['Last']
        ]);
    }

    private function createAttachments(int $maintenanceRequestId, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            MaintenanceAttachment::create([
                'maintenance_request_id' => $maintenanceRequestId,
                'content_type' => $attachment['ContentType'],
                'file_name' => $attachment['Name'],
                'file_size' => $attachment['Size'],
                'download_url' => $attachment['File']
            ]);
        }
    }

    private function createLinks(int $maintenanceRequestId, array $entryData): void
    {
        $links = [
            'public_link' => $entryData['PublicLink'] ?? null,
            'internal_link' => $entryData['InternalLink'] ?? null,
            'document1' => $entryData['Document1'] ?? null,
            'document2' => $entryData['Document2'] ?? null
        ];

        foreach ($links as $linkType => $url) {
            if ($url) {
                MaintenanceLink::create([
                    'maintenance_request_id' => $maintenanceRequestId,
                    'link_type' => $linkType,
                    'download_url' => $url,
                    'description' => ucfirst(str_replace('_', ' ', $linkType))
                ]);
            }
        }
    }

    private function sendNotifications($maintenanceRequest): void
    {
        try {
            // Add debugging
            Log::info('🔔 Starting to send notifications', [
                'request_id' => $maintenanceRequest->id,
                'store_name' => $maintenanceRequest->store->name ?? 'No store',
                'urgency' => $maintenanceRequest->urgencyLevel->name ?? 'No urgency'
            ]);

            $notificationType = $maintenanceRequest->urgencyLevel->name === 'Urgent'
                ? 'urgent_request'
                : 'new_request';

            // 1. Fire broadcast event
            Log::info('🚀 Firing broadcast event');
            event(new MaintenanceRequestReceived($maintenanceRequest, $notificationType));

            // 2. Store in database
            WebhookNotification::create([
                'maintenance_request_id' => $maintenanceRequest->id,
                'type' => $notificationType,
                'message' => $this->generateNotificationMessage($maintenanceRequest, $notificationType),
                'is_broadcast' => true
            ]);

            Log::info('✅ Notifications sent successfully', [
                'request_id' => $maintenanceRequest->id,
                'type' => $notificationType
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to send notifications', [
                'request_id' => $maintenanceRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Map urgency level from maintenance to native
     */
    private function mapUrgencyLevel(?UrgencyLevel $urgencyLevel): ?int
    {
        if (!$urgencyLevel) {
            // Return the first native urgency level as fallback
            return NativeUrgencyLevel::first()?->id ?? null;
        }
        
        // Try to find by name first
        $nativeUrgency = NativeUrgencyLevel::where('name', $urgencyLevel->name)->first();
        if ($nativeUrgency) {
            return $nativeUrgency->id;
        }
        
        // Try to find by level numeric value
        if (isset($urgencyLevel->level)) {
            $nativeUrgency = NativeUrgencyLevel::where('level', $urgencyLevel->level)->first();
            if ($nativeUrgency) {
                return $nativeUrgency->id;
            }
        }
        
        // Fallback to first available
        return NativeUrgencyLevel::first()?->id ?? null;
    }

    /**
     * Find the store manager user ID for the given store
     * Uses the manager who reviewed the request if they have a user account
     */
    private function findStoreManagerUserId(int $storeId, Manager $manager): ?int
    {
        // Build the full name from manager
        $managerFullName = trim($manager->first_name . ' ' . $manager->last_name);
        
        // Try to find a user with the same name as the manager
        $user = \App\Models\User::where('name', $managerFullName)->first();
        
        if ($user) {
            return $user->id;
        }
        
        // Fallback: Find any store manager for this store
        $user = \App\Models\User::whereHas('managedStores', function($query) use ($storeId) {
            $query->where('stores.id', $storeId);
        })->first();
        
        if ($user) {
            Log::warning('Using fallback store manager for native request', [
                'store_id' => $storeId,
                'manager_name' => $managerFullName,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
            return $user->id;
        }
        
        Log::warning('No store manager found for native request', [
            'store_id' => $storeId,
            'manager_name' => $managerFullName,
        ]);
        
        return null;
    }

    private function generateNotificationMessage($maintenanceRequest, $notificationType): string
    {
        $storeName = $maintenanceRequest->store->name ?? 'Store';
        $urgency = $maintenanceRequest->urgencyLevel->name;

        if ($urgency === 'Urgent') {
            return "🚨 URGENT: New maintenance request from {$storeName} - {$maintenanceRequest->equipment_with_issue}";
        }

        return "🔧 New maintenance request from {$storeName} - {$maintenanceRequest->equipment_with_issue}";
    }
}
