<?php

namespace App\Services\Api\Admin;
use App\Models\Store;
use App\Models\User;
use App\Models\Native\NativeRequest;
use App\Models\Native\NativeUrgencyLevel;
 
use Illuminate\Http\Request;

class AdminNativeRequestService
{
    protected $filterService;

    public function __construct(RequestFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function getAll($request): array
    {
        $query = NativeRequest::query()
            ->with(['store', 'requester', 'urgencyLevel', 'assignedTo', 'attachments']);

        $statusCounts = [
            'all' => NativeRequest::count(),
            'pending' => NativeRequest::where('status', 'pending')->count(),
            'in_progress' => NativeRequest::where('status', 'in_progress')->count(),
            'done' => NativeRequest::where('status', 'done')->count(),
            'canceled' => NativeRequest::where('status', 'canceled')->count(),
        ];
        // Apply filters from the service
        $query = $this->filterService->applyFilters($query, $request);

       

        if ($request->filled('date_from')) {
            $query->where('request_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('request_date', '<=', $request->date_to);
        }

        $maintenanceRequests = $query->orderBy('request_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $stores = Store::orderBy('store_number')->get();
        $urgencyLevels = NativeUrgencyLevel::orderBy('level')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return [
            'maintenance_requests' => $maintenanceRequests,
            'status_counts' => $statusCounts,
            'stores' => $stores,
            'urgency_levels' => $urgencyLevels,
            'users' => $users,
        ];
    }
    public function getDetails(NativeRequest $nativeRequest)
    {
        $nativeRequest->load([
            'store',
            'requester',
            'urgencyLevel',
            'assignedTo',
            'attachments'
        ]);
  

        $technicians = User::where('is_active', true)
            ->orderBy('name')
            ->get();

        return [
            'request' => $nativeRequest,
            'technicians' => $technicians
        ];
    }
    public function updateStatus($validated, NativeRequest $nativeRequest)
    {
        $nativeRequest->status = $validated['status'];
        $nativeRequest->assigned_to = $validated['assigned_to'] ?? null;
        $nativeRequest->costs = $validated['costs'] ?? null;
        $nativeRequest->how_we_fixed_it = $validated['how_we_fixed_it'] ?? null;

        $nativeRequest->save();

        return $nativeRequest->load([
            'store',
            'requester',
            'urgencyLevel',
            'assignedTo'
        ]);
    }
    public function ticketReport($request)
    {
        $query = NativeRequest::query()
            ->with(['store', 'requester', 'urgencyLevel', 'assignedTo', 'attachments']);

        // Apply filters from the service
        $query = $this->filterService->applyFilters($query, $request);


        return $query->orderBy('request_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}