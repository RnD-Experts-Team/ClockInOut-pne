<?php
// app/Http/Controllers/MaintenanceWebhookController.php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Requester;
use App\Models\Manager;
use App\Models\UrgencyLevel;
use App\Models\MaintenanceAttachment;
use App\Models\MaintenanceLink;
use App\Models\Store;
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
                'status' => 'reserved',
                'requester_id' => $requester->id,
                'reviewed_by_manager_id' => $manager->id,
                'webhook_id' => $payload['Id']
            ]);

            // Handle attachments
            if (isset($payload['ImagesVideos']) && is_array($payload['ImagesVideos'])) {
                $this->createAttachments($maintenanceRequest->id, $payload['ImagesVideos']);
            }

            // Handle links
            $this->createLinks($maintenanceRequest->id, $payload['Entry']);

            DB::commit();

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
}
