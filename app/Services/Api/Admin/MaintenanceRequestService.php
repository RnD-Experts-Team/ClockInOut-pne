<?php

namespace App\Services\Api\Admin;
use App\Models\MaintenanceRequest;
use App\Models\UrgencyLevel;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
 
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
    public function export($request)
    {
        try {

            $query = MaintenanceRequest::with([
                'requester',
                'reviewedByManager',
                'urgencyLevel',
                'store',
                'assignedTo'
            ])->select([
                'id',
                'entry_number',
                'store_id',
                'equipment_with_issue',
                'description_of_issue',
                'status',
                'costs',
                'how_we_fixed_it',
                'urgency_level_id',
                'assigned_to',
                'due_date',
                'task_end_date', // ADD THIS
                'request_date',
                'date_submitted',
                'created_at',
                'requester_id',
                'reviewed_by_manager_id'
            ]);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('urgency') && $request->urgency !== 'all') {
                $query->where('urgency_level_id', $request->urgency);
            }

            if ($request->has('store_id') && $request->store_id !== 'all') {
                $query->where('store_id', $request->store_id);
            }

            if ($request->has('date_range') && $request->date_range !== 'all') {

                switch ($request->date_range) {

                    case 'this_week':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;

                    case 'this_month':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfMonth(),
                            Carbon::now()->endOfMonth()
                        ]);
                        break;

                    case 'this_year':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfYear(),
                            Carbon::now()->endOfYear()
                        ]);
                        break;

                    case 'custom':
                        if ($request->has('start_date') && $request->has('end_date')) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($request->start_date)->startOfDay(),
                                Carbon::parse($request->end_date)->endOfDay()
                            ]);
                        }
                        break;
                }
            }

            $requests = $query->get();

            $csvData = [];
        // UPDATED: Add Task End Date to CSV headers

            $csvData[] = [
                'ID',
                'Entry Number',
                'Store Number',
                'Store Name',
                'Requester',
                'Manager',
                'Equipment',
                'Description',
                'Urgency',
                'Status',
                'Assigned To',
                'Due Date',
                'Task End Date', // NEW COLUMN
                'Request Date',
                'Submitted Date',
                'Costs',
                'How We Fixed It',
                'Created At'
            ];

             foreach ($requests as $request) {
            $csvData[] = [
                $request->id,
                $request->entry_number,
                $request->store ? $request->store->store_number : $request->store,
                $request->store ? $request->store->name : 'N/A',
                $request->requester ? $request->requester->name : 'N/A',
                $request->reviewedByManager ? $request->reviewedByManager->name : 'N/A',
                $request->equipment_with_issue,
                substr($request->description_of_issue, 0, 100) . (strlen($request->description_of_issue) > 100 ? '...' : ''),
                $request->urgencyLevel ? $request->urgencyLevel->name : 'N/A',
                ucfirst(str_replace('_', ' ', $request->status)),
                $request->assignedTo ? $request->assignedTo->name : 'N/A',
                $request->due_date ? $request->due_date->format('Y-m-d H:i') : 'N/A',
                $request->task_end_date ? $request->task_end_date->format('Y-m-d H:i') : 'N/A', // NEW DATA
                $request->request_date ? $request->request_date->format('Y-m-d') : 'N/A',
                $request->date_submitted ? $request->date_submitted->format('Y-m-d H:i:s') : 'N/A',
                $request->costs ?? '',
                $request->how_we_fixed_it ?? '',
                $request->created_at->format('Y-m-d H:i:s')
            ];
        }

            return $csvData;

        } catch (\Exception $e) {

            throw $e;

        }
    }
    public function index($request)
    {

        $query = MaintenanceRequest::with([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'attachments',
            'links',
            'store',
            'assignedTo',
            'taskAssignments' => function ($q) {
                $q->orderBy('assigned_at', 'desc')->with('assignedUser');
            }
        ])->select([
            'id',
            'entry_number',
            'form_id',
            'store_id',
            'description_of_issue',
            'urgency_level_id',
            'equipment_with_issue',
            'basic_troubleshoot_done',
            'request_date',
            'date_submitted',
            'status',
            'costs',
            'how_we_fixed_it',
            'requester_id',
            'reviewed_by_manager_id',
            'webhook_id',
            'not_in_cognito',
            'assigned_to',
            'due_date',
            'assignment_source',
            'current_task_assignment_id',
            'task_end_date',
            'created_at',
            'updated_at'
        ]);
        // Create a base query for status counts

        $baseQuery = clone $query;
        // Filter by urgency if provided

        if ($request->has('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
            $baseQuery->where('urgency_level_id', $request->urgency);
        }
        // Filter by store if provided

        if ($request->has('store') && $request->store !== 'all') {

            if ($request->store) {

                $storeValue = $request->store;

                if (is_string($storeValue) && str_starts_with($storeValue, '{')) {
                    $storeData = json_decode($storeValue, true);
                    $storeId = $storeData['id'] ?? null;
                } else {
                    $storeId = is_numeric($storeValue) ? $storeValue : null;
                }

                if ($storeId) {

                    $storeFilter = function ($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    };

                } else {

                    $storeFilter = function ($q) use ($storeValue) {
                        $q->whereHas('store', function ($subQ) use ($storeValue) {
                            $subQ->where('store_number', $storeValue)
                                ->orWhere('name', $storeValue)
                                ->orWhere(function ($partialQ) use ($storeValue) {
                                    $partialQ->where('store_number', 'LIKE', '%' . $storeValue . '%')
                                        ->orWhere('name', 'LIKE', '%' . $storeValue . '%');
                                });
                        });
                    };

                }

                $query->where($storeFilter);
                $baseQuery->where($storeFilter);
            }
        }
        // Date range filter

        if ($request->has('date_range') && $request->date_range !== 'all') {
            $dateFilter = function ($q) use ($request) {
                switch ($request->date_range) {
                    case 'this_week':
                        $q->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'this_month':
                        $q->whereBetween('created_at', [
                            Carbon::now()->startOfMonth(),
                            Carbon::now()->endOfMonth()
                        ]);
                        break;
                    case 'this_year':
                        $q->whereBetween('created_at', [
                            Carbon::now()->startOfYear(),
                            Carbon::now()->endOfYear()
                        ]);
                        break;
                    case 'custom':
                        if ($request->has('start_date') && $request->has('end_date')) {
                            $q->whereBetween('created_at', [
                                Carbon::parse($request->start_date)->startOfDay(),
                                Carbon::parse($request->end_date)->endOfDay()
                            ]);
                        }
                        break;
                }

            };

            $query->where($dateFilter);
            $baseQuery->where($dateFilter);
        }
        // Search functionality

        if ($request->has('search') && $request->search) {
            $searchFilter = function ($q) use ($request) {
                $search = $request->search;
                $q->where('description_of_issue', 'like', "%{$search}%")
                    ->orWhere('equipment_with_issue', 'like', "%{$search}%")
                    ->orWhere('entry_number', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignedTo', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });

            };

            $query->where($searchFilter);
            $baseQuery->where($searchFilter);
        }
        // Apply status filter to main query

        $selectedStatus = null;

        if ($request->has('status') && $request->status !== 'all') {
            $selectedStatus = $request->status;
            $query->where('status', $selectedStatus);
            $baseQuery->where('status', $selectedStatus);
        }

        $query->orderBy('urgency_level_id', 'asc')
            ->orderBy('created_at', 'desc');

        $maintenanceRequests = $query->paginate(15)->withQueryString();

        $urgencyLevels = UrgencyLevel::orderBy('priority_order')->get();
        $stores = Store::orderBy('store_number')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        if ($selectedStatus) {
            $totalCount = $baseQuery->count();
            $statusCounts = [
                'all' => $totalCount,
                'on_hold' => $selectedStatus === 'on_hold' ? $totalCount : 0,
                'received' => $selectedStatus === 'received' ? $totalCount : 0,
                'in_progress' => $selectedStatus === 'in_progress' ? $totalCount : 0,
                'done' => $selectedStatus === 'done' ? $totalCount : 0,
                'canceled' => $selectedStatus === 'canceled' ? $totalCount : 0,
            ];

        } else {

            $statusCounts = [
                'all' => $baseQuery->count(),
                'on_hold' => (clone $baseQuery)->where('status', 'on_hold')->count(),
                'received' => (clone $baseQuery)->where('status', 'received')->count(),
                'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'done' => (clone $baseQuery)->where('status', 'done')->count(),
                'canceled' => (clone $baseQuery)->where('status', 'canceled')->count(),
            ];

        }

        $assignedUsers = User::whereHas('taskAssignments')
            ->orderBy('name')
            ->get();

       return [
        'maintenanceRequests' => $maintenanceRequests,
        'urgencyLevels' => $urgencyLevels,
        'stores' => $stores,
        'statusCounts' => $statusCounts,
        'users' => $users,
        'assignedUsers' => $assignedUsers,
    ];
    }
    public function show($maintenanceRequest): array
    {
        $maintenanceRequest->load([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'attachments',
            'links',
            'store',
            'statusHistories.changedByUser',
            'assignedTo'
        ]);

        $users = User::where('role', 'user')
            ->orderBy('name')
            ->get();

        return [
            'maintenanceRequest' => $maintenanceRequest,
            'users' => $users
        ];
    }
    public function destroy(MaintenanceRequest $maintenanceRequest): bool
    {
        try {

            DB::beginTransaction();

            // Delete related records
            $maintenanceRequest->attachments()->delete();
            $maintenanceRequest->links()->delete();
            $maintenanceRequest->statusHistories()->delete();
            $maintenanceRequest->webhookNotifications()->delete();

            // Force delete
            $maintenanceRequest->forceDelete();

            DB::commit();

            return true;

        } catch (\Exception $e) {

            DB::rollback();

            Log::error('Delete failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
    public function getByStore($storeId)
    {
        try {
            $maintenanceRequests = MaintenanceRequest::where('store_id', $storeId)
                ->whereNotIn('status', ['done', 'completed', 'canceled'])
                ->select('id', 'equipment_with_issue', 'status')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($maintenanceRequests);
        } catch (\Exception $e) {
            Log::error('Error fetching maintenance requests by store: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load maintenance requests'], 500);
        }
    }

    public function getLatestByStore(Request $request, $storeId)
    {
        try {
            // Subtask 6: Validate store_id format (must end with at least 2 digits)
            if (!preg_match('/\d{2}$/', $storeId)) {
                return response()->json([
                    'error' => 'Invalid store_id format. Must end with 2 digits.'
                ], 400);
            }

            // Subtask 2: Extract last 2 digits from store_id
            // Example: "03759-0001" → "01"
            $lastTwoDigits = substr($storeId, -2);

            // Convert to integer to remove leading zeros
            // "01" → 1 (to match store_number '1' in database)
            $storeNumber = (int) $lastTwoDigits;

            // First, find the store by store_number
            $store = Store::where('store_number', (string) $storeNumber)->first();

            // If store doesn't exist, return informative message
            if (!$store) {
                return response()->json([
                    'message' => 'No store found with store number: ' . $storeNumber,
                    'data' => []
                ], 200);
            }

            // Check if limit parameter is provided
            $limit = $request->query('limit');

            // Subtask 3: Query maintenance requests
            $query = MaintenanceRequest::where('store_id', $store->id)
                ->orderBy('date_submitted', 'desc');

            $perPage = $request->integer('per_page', 15); // default 15 if not provided

            // If limit is provided, use it (with validation)
            if ($limit !== null) {
                // Validate limit is a positive integer between 1 and 100
                if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
                    return response()->json([
                        'error' => 'Invalid limit. Must be between 1 and 100.'
                    ], 400);
                }

                // Get limited results without pagination
                $requests = $query->limit($limit)
                    ->get(['id', 'entry_number', 'status', 'equipment_with_issue', 'date_submitted']);

                // Subtask 4: Map response to required fields only
                $response = $requests->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'entry_number' => $request->entry_number,
                        'status' => $request->status,
                        'broken_item' => $request->equipment_with_issue,
                        'submitted_at' => $request->date_submitted
                    ];
                });

                return response()->json([
                    'store_number' => $store->store_number,
                    'store_name' => $store->name,
                    'limit' => (int) $limit,
                    'count' => $response->count(),
                    'data' => $response
                ], 200);
            } else {
                if ($perPage < 1 || $perPage > 100) {
                    return response()->json([
                        'error' => 'Invalid per_page. Must be between 1 and 100.'
                    ], 400);
                }
                // No limit provided - return all with pagination (15 per page)
                $paginatedRequests = $query->paginate(
                    $perPage,
                    ['id', 'entry_number', 'status', 'equipment_with_issue', 'date_submitted']
                );
                // Map the paginated data
                $mappedData = $paginatedRequests->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'entry_number' => $request->entry_number,
                        'status' => $request->status,
                        'broken_item' => $request->equipment_with_issue,
                        'submitted_at' => $request->date_submitted
                    ];
                });

                return response()->json([
                    'store_number' => $store->store_number,
                    'store_name' => $store->name,
                    'pagination' => [
                        'current_page' => $paginatedRequests->currentPage(),
                        'per_page' => $paginatedRequests->perPage(),
                        'total' => $paginatedRequests->total(),
                        'last_page' => $paginatedRequests->lastPage(),
                        'from' => $paginatedRequests->firstItem(),
                        'to' => $paginatedRequests->lastItem(),
                    ],
                    'links' => [
                        'first' => $paginatedRequests->url(1),
                        'last' => $paginatedRequests->url($paginatedRequests->lastPage()),
                        'prev' => $paginatedRequests->previousPageUrl(),
                        'next' => $paginatedRequests->nextPageUrl(),
                    ],
                    'data' => $mappedData
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching latest maintenance requests by store', [
                'store_id' => $storeId,
                'limit' => $limit ?? 'not set',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load maintenance requests'
            ], 500);
        }
    }

    public function showAPI(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'attachments',
            'links',
            'store',
            'statusHistories.changedByUser',
            'assignedTo'
        ]);
        return response()->json($maintenanceRequest);
    }
}