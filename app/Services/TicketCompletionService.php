<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use Modules\Invoice\Models\InvoiceCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketCompletionService
{
    protected $cognitoService;

    public function __construct(CognitoFormsService $cognitoService)
    {
        $this->cognitoService = $cognitoService;
    }

    /**
     * Check if all tasks for a maintenance request are completed
     *
     * @param int $maintenanceRequestId
     * @return bool
     */
    public function checkTicketCompletion(int $maintenanceRequestId): bool
    {
        // Prefer checking per-card-per-task status (newer implementation)
        try {
            $taskRows = DB::table('invoice_card_maintenance_requests')
                ->where('maintenance_request_id', $maintenanceRequestId)
                ->select('task_status')
                ->get();

            if ($taskRows->isEmpty()) {
                Log::info("No invoice card tasks found for maintenance request {$maintenanceRequestId}");
                return false;
            }

            // If any task has a status other than 'completed', the ticket is not complete
            $allCompleted = $taskRows->every(function ($row) {
                return ($row->task_status ?? null) === 'completed';
            });

            Log::info("Ticket completion check (by task_status) for maintenance request {$maintenanceRequestId}", [
                'total_tasks' => $taskRows->count(),
                'all_completed' => $allCompleted,
            ]);

            return $allCompleted;
        } catch (\Exception $e) {
            // Fallback to older behavior: check invoice_cards.status
            $cards = DB::table('invoice_card_maintenance_requests')
                ->where('maintenance_request_id', $maintenanceRequestId)
                ->join('invoice_cards', 'invoice_card_maintenance_requests.invoice_card_id', '=', 'invoice_cards.id')
                ->select('invoice_cards.status')
                ->get();

            if ($cards->isEmpty()) {
                Log::info("No invoice cards found for maintenance request {$maintenanceRequestId}");
                return false;
            }

            $allCompleted = $cards->every(function ($card) {
                return $card->status === 'completed';
            });

            Log::info("Ticket completion check (fallback by card status) for maintenance request {$maintenanceRequestId}", [
                'total_cards' => $cards->count(),
                'all_completed' => $allCompleted,
            ]);

            return $allCompleted;
        }
    }

    /**
     * Update ticket status to 'done'
     *
     * @param int $maintenanceRequestId
     * @return bool
     */
    public function updateTicketStatus(int $maintenanceRequestId): bool
    {
        DB::beginTransaction();

        try {
            // Update maintenance request status
            $maintenanceRequest = MaintenanceRequest::findOrFail($maintenanceRequestId);
            $oldStatus = $maintenanceRequest->status;
            
            $maintenanceRequest->status = 'done';
            $maintenanceRequest->save();

            // Update pivot table status (also set task_status/completed_at for new implementation)
            DB::table('invoice_card_maintenance_requests')
                ->where('maintenance_request_id', $maintenanceRequestId)
                ->update([
                    'status' => 'done',
                    'task_status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            Log::info("Updated ticket status to 'done'", [
                'maintenance_request_id' => $maintenanceRequestId,
                'old_status' => $oldStatus,
                'new_status' => 'done',
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update ticket status for maintenance request {$maintenanceRequestId}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Sync ticket status to Cognito Forms
     *
     * @param MaintenanceRequest $maintenanceRequest
     * @return array
     */
    public function syncToCognito(MaintenanceRequest $maintenanceRequest): array
    {
        try {
            // Get Cognito form ID and entry ID
            $formId = config('services.cognito_forms.form_id');
            $entryId = $maintenanceRequest->entry_number;

            // Check if Cognito Forms is configured
            if (!$formId) {
                Log::warning("Cognito Forms not configured - skipping sync for maintenance request {$maintenanceRequest->id}");
                return [
                    'success' => false,
                    'message' => 'Cognito Forms not configured'
                ];
            }

            if (!$entryId) {
                Log::warning("No entry number for maintenance request {$maintenanceRequest->id}");
                return [
                    'success' => false,
                    'message' => 'No Cognito entry number found'
                ];
            }

            // Get invoice card data for this ticket
            $invoiceCards = InvoiceCard::whereHas('maintenanceRequests', function ($query) use ($maintenanceRequest) {
                $query->where('maintenance_requests.id', $maintenanceRequest->id);
            })->get();

            // Aggregate costs
            $totalCost = $invoiceCards->sum('total_cost');
            
            // Combine notes/fixes from all cards
            $howWeFixedIt = $invoiceCards->pluck('notes')->filter()->implode("\n\n");

            // Prepare data for Cognito
            $data = [
                'Status' => 'Done',
                'Costs' => $totalCost,
                'HowWeFixedIt' => $howWeFixedIt ?: 'Work completed as requested.',
            ];

            // Call Cognito API
            $response = $this->cognitoService->updateEntry($formId, $entryId, $data);

            // Update local record
            $maintenanceRequest->update([
                'costs' => $totalCost,
                'how_we_fixed_it' => $howWeFixedIt,
                'not_in_cognito' => false,
            ]);

            Log::info("Synced ticket to Cognito Forms", [
                'maintenance_request_id' => $maintenanceRequest->id,
                'entry_id' => $entryId,
                'total_cost' => $totalCost,
            ]);

            return [
                'success' => true,
                'message' => 'Synced to Cognito Forms successfully',
                'response' => $response
            ];

        } catch (\Exception $e) {
            Log::error("Failed to sync ticket to Cognito: {$e->getMessage()}", [
                'maintenance_request_id' => $maintenanceRequest->id,
            ]);

            // Mark as not synced but don't fail the operation
            $maintenanceRequest->update(['not_in_cognito' => true]);

            return [
                'success' => false,
                'message' => 'Failed to sync to Cognito: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process invoice card completion
     * Check all linked tickets and update status if complete
     *
     * @param InvoiceCard $invoiceCard
     * @return array
     */
    public function processCardCompletion(InvoiceCard $invoiceCard): array
    {
        if ($invoiceCard->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Invoice card is not completed'
            ];
        }

        // Get all maintenance requests linked to this card
        $maintenanceRequests = $invoiceCard->maintenanceRequests;

        $results = [];

        foreach ($maintenanceRequests as $request) {
            // Check if this ticket is now complete
            if ($this->checkTicketCompletion($request->id)) {
                // Update status
                $statusUpdated = $this->updateTicketStatus($request->id);

                if ($statusUpdated) {
                    // Sync to Cognito
                    $request->refresh();
                    $syncResult = $this->syncToCognito($request);

                    $results[] = [
                        'maintenance_request_id' => $request->id,
                        'status_updated' => true,
                        'synced' => $syncResult['success'],
                        'sync_message' => $syncResult['message'],
                    ];
                } else {
                    $results[] = [
                        'maintenance_request_id' => $request->id,
                        'status_updated' => false,
                        'error' => 'Failed to update status',
                    ];
                }
            } else {
                $results[] = [
                    'maintenance_request_id' => $request->id,
                    'status_updated' => false,
                    'reason' => 'Not all tasks completed yet',
                ];
            }
        }

        Log::info("Processed card completion for card {$invoiceCard->id}", [
            'results' => $results,
        ]);

        return [
            'success' => true,
            'message' => 'Card completion processed',
            'results' => $results
        ];
    }
}
