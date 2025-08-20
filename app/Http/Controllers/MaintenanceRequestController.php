<?php
// app/Http/Controllers/MaintenanceRequestController.php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\UrgencyLevel;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
            'store' // Add store relationship
        ]);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by urgency if provided
        if ($request->has('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
        }

        // Filter by store if provided - Updated to use store_id
        if ($request->has('store_id') && $request->store_id !== 'all') {
            $query->where('store_id', $request->store_id);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store', 'like', "%{$search}%") // Keep old store field for webhook compatibility
                ->orWhere('description_of_issue', 'like', "%{$search}%")
                    ->orWhere('equipment_with_issue', 'like', "%{$search}%")
                    ->orWhere('entry_number', 'like', "%{$search}%")
                    ->orWhereHas('requester', function($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function($q) use ($search) {
                        $q->where('store_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort by urgency priority and created date
        $query->orderBy('urgency_level_id', 'asc')
            ->orderBy('created_at', 'desc');

        $maintenanceRequests = $query->paginate(15)->withQueryString();

        $urgencyLevels = UrgencyLevel::orderBy('priority_order')->get();

        // Get all stores for filter dropdown
        $stores = Store::orderBy('store_number')->get();

        $statusCounts = [
            'all' => MaintenanceRequest::count(),
            'on_hold' => MaintenanceRequest::where('status', 'on_hold')->count(),
            'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'done' => MaintenanceRequest::where('status', 'done')->count(),
            'canceled' => MaintenanceRequest::where('status', 'canceled')->count(),
        ];

        return view('admin.maintenance-requests.index', compact(
            'maintenanceRequests',
            'urgencyLevels',
            'stores',
            'statusCounts'
        ));
    }

    // Add create method for manual creation
    public function create(): View
    {
        $urgencyLevels = UrgencyLevel::orderBy('priority_order')->get();
        $stores = Store::orderBy('store_number')->get();

        return view('admin.maintenance-requests.create', compact('urgencyLevels', 'stores'));
    }

    // Add store method for manual creation
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
        ]);

        DB::beginTransaction();
        try {
            // Handle store creation if needed
            if (!$validated['store_id'] && $validated['new_store_number']) {
                $store = Store::create([
                    'store_number' => $validated['new_store_number'],
                    'name' => $validated['new_store_name'],
                    'is_active' => true,
                ]);
                $validated['store_id'] = $store->id;
                $validated['store'] = $store->store_number; // For webhook compatibility
            } elseif ($validated['store_id']) {
                $store = Store::find($validated['store_id']);
                $validated['store'] = $store->store_number; // For webhook compatibility
            }

            // Create maintenance request
            $validated['form_id'] = 'MANUAL-' . time();
            $validated['date_submitted'] = now();
            $validated['entry_number'] = MaintenanceRequest::max('entry_number') + 1;
            $validated['webhook_id'] = 'MANUAL-' . uniqid();
            $validated['status'] = 'on_hold';
            $validated['requester_id'] = 1; // Default or current user
            $validated['reviewed_by_manager_id'] = 1; // Default or current user

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

    // Rest of the methods remain the same...
    public function show(MaintenanceRequest $maintenanceRequest): View
    {
        $maintenanceRequest->load([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'attachments',
            'links',
            'store', // Add store relationship
            'statusHistories.changedByUser'
        ]);

        return view('admin.maintenance-requests.show', compact('maintenanceRequest'));
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:on_hold,in_progress,done,canceled',
            'costs' => 'required_if:status,done|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,done|nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $newStatus = $request->input('status');
            $costs = $request->input('costs');
            $howWeFixedIt = $request->input('how_we_fixed_it');
            $userId = auth()->id();

            if ($newStatus === 'done' && (empty($costs) || empty($howWeFixedIt))) {
                return back()->withErrors([
                    'costs' => 'Costs and how we fixed it are required when marking as done.'
                ]);
            }

            if (!$maintenanceRequest->canMoveToStatus($newStatus)) {
                return back()->withErrors([
                    'status' => "Cannot change status from {$maintenanceRequest->status} to {$newStatus}."
                ]);
            }

            $maintenanceRequest->updateStatus($newStatus, $costs, $howWeFixedIt, $userId);

            DB::commit();

            return back()->with('success', 'Status updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();

            return back()->withErrors([
                'error' => 'Failed to update status: ' . $e->getMessage()
            ]);
        }
    }

    // Keep all other methods exactly the same...
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

            return back()->withErrors([
                'error' => 'Failed to delete request: ' . $e->getMessage()
            ]);
        }
    }

    // Keep all other methods exactly the same as in your original file...
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        // Keep the same implementation
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:maintenance_requests,id',
            'status' => 'required|in:on_hold,in_progress,done,canceled',
            'costs' => 'required_if:status,done|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,done|nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $requestIds = $request->input('request_ids');
            $newStatus = $request->input('status');
            $costs = $request->input('costs');
            $howWeFixedIt = $request->input('how_we_fixed_it');
            $userId = auth()->id();

            $requests = MaintenanceRequest::whereIn('id', $requestIds)->get();

            foreach ($requests as $maintenanceRequest) {
                if ($maintenanceRequest->status === $newStatus) {
                    continue;
                }

                if (!$maintenanceRequest->canMoveToStatus($newStatus)) {
                    continue;
                }

                $maintenanceRequest->updateStatus($newStatus, $costs, $howWeFixedIt, $userId);
            }

            DB::commit();

            $count = count($requestIds);
            return back()->with('success', "Successfully updated {$count} maintenance requests.");

        } catch (\Exception $e) {
            DB::rollback();

            return back()->withErrors([
                'error' => 'Failed to update requests: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $query = MaintenanceRequest::with([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'store'
        ]);

        // Apply same filters as index
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
        }

        if ($request->has('store_id') && $request->store_id !== 'all') {
            $query->where('store_id', $request->store_id);
        }

        $requests = $query->get();

        $csvData = [];
        $csvData[] = [
            'ID', 'Entry Number', 'Store Number', 'Store Name', 'Requester', 'Manager', 'Equipment',
            'Description', 'Urgency', 'Status', 'Request Date', 'Submitted Date',
            'Costs', 'How We Fixed It', 'Created At'
        ];

        foreach ($requests as $request) {
            $csvData[] = [
                $request->id,
                $request->entry_number,
                $request->store ? $request->store->store_number : $request->store,
                $request->store ? $request->store->name : 'N/A',
                $request->requester->full_name,
                $request->reviewedByManager->full_name,
                $request->equipment_with_issue,
                substr($request->description_of_issue, 0, 100) . (strlen($request->description_of_issue) > 100 ? '...' : ''),
                $request->urgencyLevel->name,
                ucfirst(str_replace('_', ' ', $request->status)),
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
}
