<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\UrgencyLevel;
use App\Models\Store;
use App\Models\User;
use App\Services\CognitoFormsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaintenanceRequest::with([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'attachments',
            'links',
            'store',
            'assignedTo',
            'taskAssignments' => function($q) {
                $q->orderBy('assigned_at', 'desc')->with('assignedUser');
            }
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
                    $storeFilter = function($q) use ($storeId) {
                        $q->where('store_id', $storeId);
                    };
                } else {
                    $storeFilter = function($q) use ($storeValue) {
                        $q->whereHas('store', function($subQ) use ($storeValue) {
                            // Try exact match first, then partial match
                            $subQ->where('store_number', $storeValue)
                                ->orWhere('name', $storeValue)
                                // Only if no exact match, do partial search
                                ->orWhere(function($partialQ) use ($storeValue) {
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
            $dateFilter = function($q) use ($request) {
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
            $searchFilter = function($q) use ($request) {
                $search = $request->search;
                $q->where('description_of_issue', 'like', "%{$search}%")
                    ->orWhere('equipment_with_issue', 'like', "%{$search}%")
                    ->orWhere('entry_number', 'like', "%{$search}%")
                    ->orWhereHas('requester', function($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignedTo', function($q) use ($search) {
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

        // Sort by urgency priority and created date
        $query->orderBy('urgency_level_id', 'asc')
            ->orderBy('created_at', 'desc');

        $maintenanceRequests = $query->paginate(15)->withQueryString();

        $urgencyLevels = UrgencyLevel::orderBy('priority_order')->get();
        $stores = Store::orderBy('store_number')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        // Calculate status counts
        if ($selectedStatus) {
            $totalCount = $baseQuery->count();
            $statusCounts = [
                'all' => $totalCount,
                'on_hold' => $selectedStatus === 'on_hold' ? $totalCount : 0,
                'in_progress' => $selectedStatus === 'in_progress' ? $totalCount : 0,
                'done' => $selectedStatus === 'done' ? $totalCount : 0,
                'canceled' => $selectedStatus === 'canceled' ? $totalCount : 0,
            ];
        } else {
            $statusCounts = [
                'all' => $baseQuery->count(),
                'on_hold' => (clone $baseQuery)->where('status', 'on_hold')->count(),
                'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'done' => (clone $baseQuery)->where('status', 'done')->count(),
                'canceled' => (clone $baseQuery)->where('status', 'canceled')->count(),
            ];
        }

//        $assignedUserIds = DB::table('task_assignments')
//            ->distinct()
//            ->whereNotNull('assigned_user_id')
//            ->pluck('assigned_user_id');
//
//        $assignedUsers = User::whereIn('id', $assignedUserIds)
//            ->orderBy('name')
//            ->get();
//
//        dd($assignedUsers);

// Method 2: Using exists query
//        $assignedUsers = User::whereExists(function ($query) {
//            $query->select(DB::raw(1))
//                ->from('task_assignments')
//                ->whereColumn('task_assignments.assigned_user_id', 'users.id')
//                ->whereNotNull('assigned_user_id');
//        })
//            ->orderBy('name')
//            ->get();
//
//        dd($assignedUsers);

        $assignedUsers = User::whereHas('taskAssignments')
            ->orderBy('name')
            ->get();

//        dd($maintenanceRequests);
        return view('admin.maintenance-requests.index', compact(
            'maintenanceRequests',
            'urgencyLevels',
            'stores',
            'statusCounts',
            'users',
            'assignedUsers'
        ));
    }


    public function create(): View
    {
        $urgencyLevels = UrgencyLevel::orderBy('priority_order')->get();
        $stores = Store::orderBy('store_number')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.maintenance-requests.create', compact('urgencyLevels', 'stores', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'description_of_issue' => 'required|string',
            'urgency_level_id' => 'required|exists:urgency_levels,id',
            'equipment_with_issue' => 'required|string',
            'basic_troubleshoot_done' => 'boolean',
            'request_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:request_date',
        ]);

        DB::beginTransaction();
        try {
            if (!$validated['store_id'] && $validated['new_store_number']) {
                $store = Store::create([
                    'store_number' => $validated['new_store_number'],
                    'name' => $validated['new_store_name'],
                    'is_active' => true,
                ]);
                $validated['store_id'] = $store->id;
                $validated['store'] = $store->store_number;
            } elseif ($validated['store_id']) {
                $store = Store::find($validated['store_id']);
                $validated['store'] = $store->store_number;
            }

            $validated['form_id'] = 'MANUAL-' . time();
            $validated['date_submitted'] = now();
            $validated['entry_number'] = MaintenanceRequest::max('entry_number') + 1;
            $validated['webhook_id'] = 'MANUAL-' . uniqid();
            $validated['status'] = 'on_hold';
            $validated['requester_id'] = auth()->id() ?? 1;
            $validated['reviewed_by_manager_id'] = 1;

            unset($validated['new_store_number'], $validated['new_store_name']);

            MaintenanceRequest::create($validated);

            DB::commit();

            return redirect()->route('maintenance-requests.index')
                ->with('success', 'Maintenance request created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create maintenance request: ' . $e->getMessage()]);
        }
    }

    public function show(MaintenanceRequest $maintenanceRequest): View
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

        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.maintenance-requests.show', compact('maintenanceRequest', 'users'));
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {

        $request->validate([
            'status' => 'required|in:on_hold,in_progress,done,canceled',
            'costs' => 'required_if:status,done|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,done|nullable|string|max:1000',
            'assigned_to' => 'required_if:status,in_progress|nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today'
        ]);


        try {
            DB::beginTransaction();
            $newStatus = $request->input('status');
            $costs = $request->input('costs');
            $howWeFixedIt = $request->input('how_we_fixed_it');
            $assignedTo = $request->input('assigned_to');
            $dueDate = $request->input('due_date');
            $userId = auth()->id() ?? 1;

            // Validation checks
            if ($newStatus === 'done' && (empty($costs) || empty($howWeFixedIt))) {
                return back()->withErrors([
                    'costs' => 'Costs and how we fixed it are required when marking as done.'
                ]);
            }

            if ($newStatus === 'in_progress' && empty($assignedTo)) {
                return back()->withErrors([
                    'assigned_to' => 'Assigned to is required when marking as in progress.'
                ]);
            }
            // Update main fields
            $updateData = [
                'status' => $newStatus,
                'costs' => $costs,
                'how_we_fixed_it' => $howWeFixedIt,
            ];

            if ($newStatus == 'in_progress' && $assignedTo) {
                // Direct assignment from admin
                $maintenanceRequest->assignDirectly($assignedTo, $dueDate);
            } elseif ($newStatus !== 'in_progress') {
                // Clear assignment if not in progress
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
                'notes' => $newStatus === 'done' ? $howWeFixedIt : null,
            ]);
            // Get form ID dynamically from the maintenance request
            $formId = $maintenanceRequest->form_id; // Use the stored form_id
            $entryId = $maintenanceRequest->entry_number;

            // Only update Cognito if we have a form ID
            if ($formId) {
                $cognitoService = app(CognitoFormsService::class);

                $cognitoStatusMap = [
                    'on_hold' => 'On Hold',
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
            }

            DB::commit();

            return back()->with('success', 'Status updated successfully in both local database and Cognito Forms.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    public function destroy(MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $maintenanceRequest->attachments()->delete();
            $maintenanceRequest->links()->delete();
            $maintenanceRequest->statusHistories()->delete();
            $maintenanceRequest->delete();

            DB::commit();

            return redirect()->route('maintenance-requests.index')
                ->with('success', 'Maintenance request deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete request: ' . $e->getMessage()]);
        }
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:maintenance_requests,id',
            'status' => 'required|in:on_hold,in_progress,done,canceled',
            'costs' => 'required_if:status,done|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,done|nullable|string|max:1000',
            'assigned_to' => 'required_if:status,in_progress|nullable|exists:users,id',
            'due_date' => 'nullable|date'
        ]);

        try {
            DB::beginTransaction();

            $requestIds = $request->input('request_ids');
            $newStatus = $request->input('status');
            $costs = $request->input('costs');
            $howWeFixedIt = $request->input('how_we_fixed_it');
            $assignedTo = $request->input('assigned_to');
            $dueDate = $request->input('due_date');
            $userId = auth()->id() ?? 1;

            $requests = MaintenanceRequest::whereIn('id', $requestIds)->get();

            foreach ($requests as $maintenanceRequest) {
                if ($maintenanceRequest->status === $newStatus) {
                    continue;
                }

                $maintenanceRequest->update([
                    'status' => $newStatus,
                    'costs' => $newStatus === 'done' ? $costs : null,
                    'how_we_fixed_it' => $newStatus === 'done' ? $howWeFixedIt : null,
                    'assigned_to' => $newStatus === 'in_progress' ? $assignedTo : null,
                    'due_date' => $newStatus === 'in_progress' ? $dueDate : null,
                ]);

                $maintenanceRequest->statusHistories()->create([
                    'old_status' => $maintenanceRequest->getOriginal('status'),
                    'new_status' => $newStatus,
                    'changed_by' => $userId,
                    'changed_at' => now(),
                    'notes' => $newStatus === 'done' ? $howWeFixedIt : null,
                ]);
            }

            DB::commit();

            $count = count($requestIds);
            return back()->with('success', "Successfully updated {$count} maintenance requests.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update requests: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $query = MaintenanceRequest::with([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'store',
            'assignedTo'
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
        $csvData[] = [
            'ID', 'Entry Number', 'Store Number', 'Store Name', 'Requester', 'Manager', 'Equipment',
            'Description', 'Urgency', 'Status', 'Assigned To', 'Due Date', 'Request Date',
            'Submitted Date', 'Costs', 'How We Fixed It', 'Created At'
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
                $request->due_date ? $request->due_date->format('Y-m-d') : 'N/A',
                $request->request_date->format('Y-m-d'),
                $request->date_submitted->format('Y-m-d H:i:s'),
                $request->costs ?? '',
                $request->how_we_fixed_it ?? '',
                $request->created_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = 'maintenance_requests_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function ticketReport(Request $request)
    {
        try {
            // Get the same filtered data as index
            $query = MaintenanceRequest::with(['store', 'requester', 'assignedTo', 'urgencyLevel']);

            // Apply the same filters as index
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->filled('urgency') && $request->urgency !== 'all') {
                $query->where('urgency_level_id', $request->urgency);
            }

            if ($request->filled('store') && $request->store !== 'all') {
                $query->where('store', 'LIKE', '%' . $request->store . '%');
            }

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

            $maintenanceRequests = $query->get();

            return view('admin.maintenance-requests.ticket-report', compact('maintenanceRequests'));

        } catch (\Exception $e) {
            \Log::error('Ticket Report Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
