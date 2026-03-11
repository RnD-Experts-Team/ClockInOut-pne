<?php

namespace App\Services\Api\Admin;
use App\Models\MaintenanceRequest;
use App\Models\UrgencyLevel;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\Api\Admin\UpdateMaintenanceStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\Api\Admin\CognitoFormsService;
use Illuminate\Support\Facades\DB;
class MaintenanceRequestService
{
    public function ticketReport($request)
    {
        $query = MaintenanceRequest::with(['store', 'requester', 'assignedTo', 'urgencyLevel'])
            ->select([
                'id',
                'entry_number',
                'store_id',
                'equipment_with_issue',
                'status',
                'costs',
                'urgency_level_id',
                'assigned_to',
                'due_date',
                'task_end_date',
                'created_at',
                'assignment_source',
                'current_task_assignment_id'
            ]);

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Urgency filter
        if ($request->filled('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
        }

        // Store filter
        if ($request->filled('store') && $request->store !== 'all') {
            $query->where('store', 'LIKE', '%' . $request->store . '%');
        }

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('store', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('equipment_with_issue', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('description_of_issue', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Date range filters
        if ($request->filled('date_range') && $request->date_range !== 'all') {
            switch ($request->date_range) {

                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;

                case 'this_month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;

                case 'this_year':
                    $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;

                case 'custom':

                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }

                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }

                    break;
            }
        }

        return $query->get();
    }
    public function updateStatus($request, MaintenanceRequest $maintenanceRequest)
    {

        try {
            DB::beginTransaction();
            $newStatus = $request->input('status');
            $reason = $request->input('reason');
            $costs = $request->input('costs');
            $howWeFixedIt = $request->input('how_we_fixed_it');
            $assignedTo = $request->input('assigned_to');
            $dueDate = $request->input('due_date');
            $progressDescription = $request->input('progress_description');
            $taskEndDate = $request->input('task_end_date'); // NEW: Get manual input
            $userId = auth()->id() ?? 1;
            // Validation checks
            if ($newStatus === 'done' && (empty($costs) || empty($howWeFixedIt) || empty($taskEndDate))) {
                return [
                    'success' => false,
                    'errors' => [
                        'costs' => 'Costs are required when marking as done.',
                        'how_we_fixed_it' => 'How we fixed it is required when marking as done.',
                        'task_end_date' => 'Task end date is required when marking as done.' // NEW
                    ]
                ];
            }

            if (($newStatus === 'in_progress' || $newStatus === 'done') && empty($assignedTo)) {
                return [
                    'success' => false,
                    'errors' => [
                        'assigned_to' => 'Assigned to is required when marking as in progress or done.'
                    ]
                ];
            }

            if ($newStatus === 'on_hold' && empty($reason)) {
                return [
                    'success' => false,
                    'errors' => [
                        'reason' => 'Reason is required when marking as on hold.'
                    ]
                ];
            }
            // Update main fields
            $updateData = [
                'status' => $newStatus,
                'reason' => $reason,
                'costs' => $costs,
                'how_we_fixed_it' => $howWeFixedIt,
                'progress_description' => $progressDescription,
            ];
            // Set task_end_date when status is marked as done (manual input)
            if ($newStatus === 'done' && $taskEndDate) {
                $updateData['task_end_date'] = Carbon::parse($taskEndDate);
            } else {
                // Clear task_end_date if status is changed from done to something else
                $updateData['task_end_date'] = null;
            }
            // Handle assignment for in_progress and done statuses
            if (($newStatus == 'in_progress' || $newStatus == 'done') && $assignedTo) {
                // Direct assignment from admin
                $maintenanceRequest->assignDirectly($assignedTo, $dueDate);
            } elseif ($newStatus !== 'in_progress' && $newStatus !== 'done') {
                // Clear assignment if not in progress or done
                $updateData['assigned_to'] = null;
                $updateData['due_date'] = null;
                $updateData['assignment_source'] = 'direct';
                $updateData['current_task_assignment_id'] = null;
            }

            $maintenanceRequest->update($updateData);
            // Record status change
            $maintenanceRequest->statusHistories()->create([
                'old_status' => $maintenanceRequest->getOriginal('status'),
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'changed_at' => now(),
                'notes' => $newStatus === 'done'
                    ? $howWeFixedIt
                    : (($newStatus === 'on_hold')
                        ? $reason
                        : ($newStatus === 'in_progress'
                            ? $progressDescription
                            : null)),
            ]);
            // Get form ID dynamically from the maintenance request

            $formId = $maintenanceRequest->form_id;
            $entryId = $maintenanceRequest->entry_number;
            // Only update Cognito if we have a form ID

            $cognitoSuccess = false;

            if ($formId) {
                try {
                    $cognitoService = app(CognitoFormsService::class);

                    $cognitoStatusMap = [
                        'on_hold' => 'On Hold',
                        'received' => 'Received',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                        'canceled' => 'Canceled'
                    ];

                    $cognitoData = [
                        'CorrespondenceInternalUseOnly' => [
                            'Status' => $cognitoStatusMap[$newStatus] ?? $newStatus,
                            'NotesFromMaintenanceTeam' => $howWeFixedIt,
                        ],
                        'Entry' => [
                            'Action' => 'Update',
                            'Role' => 'Internal',
                        ]
                    ];

                    $cognitoService->updateEntry($formId, $entryId, $cognitoData);
                    $cognitoSuccess = true;

                } catch (\Exception $e) {
                    // Log the error but don't fail the entire operation

                    Log::error('Failed to update Cognito Forms', [
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'form_id' => $formId,
                        'entry_id' => $entryId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $message = 'Status updated successfully in local database.';

            if ($cognitoSuccess) {
                $message = 'Status updated successfully in both local database and Cognito Forms.';
            } elseif ($formId) {
                $message = 'Status updated in local database. Warning: Could not sync with Cognito Forms.';
            }

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {

            DB::rollback();

            Log::error('Failed to update maintenance request status', [
                'maintenance_request_id' => $maintenanceRequest->id,
                'new_status' => $newStatus ?? 'unknown',
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }
    public function bulkUpdateStatus(array $data, $userId)
    {
    try{
        DB::beginTransaction();
        $requestIds = $data['request_ids'];
        $newStatus = $data['status'];
        $reason = $data['reason'] ?? null;
        $costs = $data['costs'] ?? null;
        $howWeFixedIt = $data['how_we_fixed_it'] ?? null;
        $assignedTo = $data['assigned_to'] ?? null;
        $dueDate = $data['due_date'] ?? null;
        
        $progressDescription = $data['progress_description'] ?? null;
        // IMPROVED: Add validation checks before processing

        if (($newStatus === 'on_hold' || $newStatus === 'received') && empty($reason)) {
            throw new \Exception('Reason is required when marking as on hold or received.');
        }

        if ($newStatus === 'done' && (empty($costs) || empty($howWeFixedIt))) {
            throw new \Exception('Costs: and how we fixed it are required when marking as done, how_we_fixed_it: How we fixed it is required when marking as done.');
        }

        if ($newStatus === 'in_progress' && empty($assignedTo)) {
            throw new \Exception('Assigned to is required when marking as in progress.');
        }

        $requests = MaintenanceRequest::whereIn('id', $requestIds)->get();
        $updatedCount = 0;

        foreach ($requests as $maintenanceRequest) {
                // Skip if already in the target status
            if ($maintenanceRequest->status === $newStatus) {
                continue;
            }

            $oldStatus = $maintenanceRequest->status;
                // Prepare update data

            $updateData = [
                'status' => $newStatus,
                'reason' => ($newStatus === 'on_hold') ? $reason : null,
                'costs' => $newStatus === 'done' ? $costs : null,
                'how_we_fixed_it' => $newStatus === 'done' ? $howWeFixedIt : null,
                'progress_description' => $newStatus === 'in_progress' ? $progressDescription : null,
            ];
            // Handle assignment logic
            if ($newStatus === 'in_progress') {
                $updateData['assigned_to'] = $assignedTo;
                $updateData['due_date'] = $dueDate;
                $updateData['assignment_source'] = 'direct';
                    // IMPROVED: Create task assignment if needed

                if ($assignedTo) {
                    $maintenanceRequest->assignDirectly($assignedTo, $dueDate);
                }

            } elseif ($newStatus === 'done') {
                    // Keep existing assignment for done status

                $updateData['assigned_to'] =
                    $maintenanceRequest->assigned_to ?: $assignedTo;

            } else {
                    // Clear assignment for other statuses

                $updateData['assigned_to'] = null;
                $updateData['due_date'] = null;
                $updateData['assignment_source'] = 'direct';
                $updateData['current_task_assignment_id'] = null;
            }

            $maintenanceRequest->update($updateData);
                // Record status change

            $maintenanceRequest->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'changed_at' => now(),
                'notes' => $newStatus === 'done'
                    ? $howWeFixedIt
                    : (($newStatus === 'on_hold')
                        ? $reason
                        : ($newStatus === 'in_progress'
                            ? $progressDescription
                            : null)),
            ]);
              // IMPROVED: Update Cognito if form ID exists
            $this->updateCognitoStatus($maintenanceRequest, $newStatus, $howWeFixedIt);


            $updatedCount++;
        }

        DB::commit();

        return $updatedCount;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

    }
       private function updateCognitoStatus(MaintenanceRequest $maintenanceRequest, string $newStatus, ?string $howWeFixedIt = null): void
    {
        $formId = $maintenanceRequest->form_id;
        $entryId = $maintenanceRequest->entry_number;

        if (!$formId) {
            return;
        }

        try {
            $cognitoService = app(CognitoFormsService::class);

            $cognitoStatusMap = [
                'on_hold' => 'On Hold',
                'received' => 'Received', // CHANGED: reserved → received
                'in_progress' => 'In Progress',
                'done' => 'Done',
                'canceled' => 'Canceled'
            ];

            $cognitoData = [
                'CorrespondenceInternalUseOnly' => [
                    'Status' => $cognitoStatusMap[$newStatus] ?? $newStatus,
                    'NotesFromMaintenanceTeam' => $howWeFixedIt,
                ],
                'Entry' => [
                    'Action' => 'Update',
                    'Role' => 'Internal',
                ]
            ];

            $cognitoService->updateEntry($formId, $entryId, $cognitoData);
        } catch (\Exception $e) {
            // Log but don't fail the entire operation
            Log::warning('Failed to update Cognito for maintenance request', [
                'maintenance_request_id' => $maintenanceRequest->id,
                'form_id' => $formId,
                'entry_id' => $entryId,
                'error' => $e->getMessage()
            ]);
        }
    }
}