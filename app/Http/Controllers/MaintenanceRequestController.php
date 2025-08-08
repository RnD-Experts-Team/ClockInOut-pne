<?php
// app/Http/Controllers/MaintenanceRequestController.php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\UrgencyLevel;
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
        'links'
    ]);

    // Filter by status if provided
    if ($request->has('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    // Filter by urgency if provided
    if ($request->has('urgency') && $request->urgency !== 'all') {
        $query->where('urgency_level_id', $request->urgency);
    }

    // Filter by store if provided - NEW FILTER
    if ($request->has('store') && $request->store !== 'all') {
        $query->where('store', $request->store);
    }

    // Search functionality
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('store', 'like', "%{$search}%")
              ->orWhere('description_of_issue', 'like', "%{$search}%")
              ->orWhere('equipment_with_issue', 'like', "%{$search}%")
              ->orWhere('entry_number', 'like', "%{$search}%")
              ->orWhereHas('requester', function($q) use ($search) {
                  $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
              });
        });
    }

    // Sort by urgency priority and created date
    $query->orderBy('urgency_level_id', 'asc')
          ->orderBy('created_at', 'desc');

    $maintenanceRequests = $query->paginate(15)->withQueryString();

    $urgencyLevels = UrgencyLevel::orderBy('priority_order')->get();

    // Get all unique stores - NEW
    $stores = MaintenanceRequest::distinct()->pluck('store')->sort()->values();

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
        'stores', // NEW
        'statusCounts'
    ));
}

    public function show(MaintenanceRequest $maintenanceRequest): View
    {
        $maintenanceRequest->load([
            'requester',
            'reviewedByManager',
            'urgencyLevel',
            'attachments',
            'links',
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

        // Validation for status transitions
        if ($newStatus === 'done' && (empty($costs) || empty($howWeFixedIt))) {
            return back()->withErrors([
                'costs' => 'Costs and how we fixed it are required when marking as done.'
            ]);
        }

        // Check valid status transitions
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

    public function destroy(MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Delete related records (cascade should handle this, but being explicit)
            $maintenanceRequest->attachments()->delete();
            $maintenanceRequest->links()->delete();
            $maintenanceRequest->statusHistories()->delete();
            
            // Delete the main request
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

    public function bulkUpdateStatus(Request $request): RedirectResponse
{
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
            // Skip if already in the target status
            if ($maintenanceRequest->status === $newStatus) {
                continue;
            }

            // Check if transition is valid
            if (!$maintenanceRequest->canMoveToStatus($newStatus)) {
                continue; // Skip invalid transitions
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
        'urgencyLevel'
    ]);

    // Apply same filters as index
    if ($request->has('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    if ($request->has('urgency') && $request->urgency !== 'all') {
        $query->where('urgency_level_id', $request->urgency);
    }

    // Add store filter to export as well - NEW
    if ($request->has('store') && $request->store !== 'all') {
        $query->where('store', $request->store);
    }

    $requests = $query->get();

    $csvData = [];
    $csvData[] = [
        'ID', 'Entry Number', 'Store', 'Requester', 'Manager', 'Equipment',
        'Description', 'Urgency', 'Status', 'Request Date', 'Submitted Date',
        'Completion Notes', 'Created At'
    ];

    foreach ($requests as $request) {
        $csvData[] = [
            $request->id,
            $request->entry_number,
            $request->store,
            $request->requester->full_name,
            $request->reviewedByManager->full_name,
            $request->equipment_with_issue,
            substr($request->description_of_issue, 0, 100) . (strlen($request->description_of_issue) > 100 ? '...' : ''),
            $request->urgencyLevel->name,
            ucfirst(str_replace('_', ' ', $request->status)),
            $request->request_date->format('Y-m-d'),
            $request->date_submitted->format('Y-m-d H:i:s'),
            $request->completion_notes ?? '',
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
