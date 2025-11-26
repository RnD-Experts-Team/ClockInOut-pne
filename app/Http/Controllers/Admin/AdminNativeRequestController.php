<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNativeRequestStatusRequest;
use App\Models\Native\NativeRequest;
use App\Models\Native\NativeUrgencyLevel;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class AdminNativeRequestController extends Controller
{
    /**
     * Display all native requests dashboard.
     */
    public function index(Request $request)
    {
        // Build query
        $query = NativeRequest::query()
            ->with(['store', 'requester', 'urgencyLevel', 'assignedTo', 'attachments']);

        // Calculate status counts
        $statusCounts = [
            'all' => NativeRequest::count(),
            'pending' => NativeRequest::where('status', 'pending')->count(),
            'in_progress' => NativeRequest::where('status', 'in_progress')->count(),
            'done' => NativeRequest::where('status', 'done')->count(),
            'canceled' => NativeRequest::where('status', 'canceled')->count(),
        ];

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
        }

        if ($request->filled('store') && $request->store !== 'all') {
            $query->where('store_id', $request->store);
        }

        if ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_with_issue', 'LIKE', "%{$search}%")
                  ->orWhere('description_of_issue', 'LIKE', "%{$search}%")
                  ->orWhereHas('store', function ($storeQuery) use ($search) {
                      $storeQuery->where('store_number', 'LIKE', "%{$search}%")
                                  ->orWhere('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('requester', function ($requesterQuery) use ($search) {
                      $requesterQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->where('request_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('request_date', '<=', $request->date_to);
        }

        // Order and paginate
        $maintenanceRequests = $query->orderBy('request_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Get filter options
        $stores = Store::orderBy('store_number')->get();
        $urgencyLevels = NativeUrgencyLevel::orderBy('level')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.native-requests.index', compact(
            'maintenanceRequests',
            'statusCounts',
            'stores',
            'urgencyLevels',
            'users'
        ));
    }

    /**
     * Display the specified request.
     */
    public function show(NativeRequest $request)
    {
        // Eager load relationships
        $request->load(['store', 'requester', 'urgencyLevel', 'assignedTo', 'attachments']);

        // Get technicians for assignment dropdown
        $technicians = User::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.native-requests.show', compact('request', 'technicians'));
    }

    /**
     * Update the request status and related fields.
     */
    public function updateStatus(UpdateNativeRequestStatusRequest $formRequest, NativeRequest $request)
    {
        // Get validated data
        $validated = $formRequest->validated();
        
        // Update fields
        $request->status = $validated['status'];
        $request->assigned_to = $validated['assigned_to'] ?? null;
        $request->costs = $validated['costs'] ?? null;
        $request->how_we_fixed_it = $validated['how_we_fixed_it'] ?? null;
        
        // Save the changes
        $request->save();

        // Return response based on request type
        if ($formRequest->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request updated successfully.',
                'request' => $request->load(['store', 'requester', 'urgencyLevel', 'assignedTo']),
            ]);
        }

        return redirect()->route('admin.native.index')
            ->with('success', 'Request #' . $request->id . ' updated successfully.');
    }

    /**
     * Generate ticket report for modal display.
     */
    public function ticketReport(Request $request)
    {
        // Build query with same filters as index()
        $query = NativeRequest::query()
            ->with(['store', 'requester', 'urgencyLevel', 'assignedTo', 'attachments']);

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
        }

        if ($request->filled('store') && $request->store !== 'all') {
            $query->where('store_id', $request->store);
        }

        if ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_with_issue', 'LIKE', "%{$search}%")
                  ->orWhere('description_of_issue', 'LIKE', "%{$search}%")
                  ->orWhereHas('store', function ($storeQuery) use ($search) {
                      $storeQuery->where('store_number', 'LIKE', "%{$search}%")
                                  ->orWhere('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('requester', function ($requesterQuery) use ($search) {
                      $requesterQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Order by date
        $maintenanceRequests = $query->orderBy('request_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Return the Excel-style report view
        //return view('admin.native-requests._ticket_report', compact('maintenanceRequests'));
        
        // TODO: Replace this with your actual Excel-style report HTML
        // For now, returning a basic table
        return view('admin.native-requests._ticket_report', compact('maintenanceRequests'));
    }
}
