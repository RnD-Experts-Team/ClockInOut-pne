<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateNativeRequestStatusRequest;
use App\Services\Api\Admin\AdminNativeRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Native\NativeRequest;

class AdminNativeRequestController extends Controller
{
    protected AdminNativeRequestService $service;

    public function __construct(AdminNativeRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->service->getAll($request);
            $requests = $data['maintenance_requests'];

            return response()->json([
                'success' => true,
                'data' => $requests->items(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
                'status_counts' => $data['status_counts'],
                'filters' => [
                    'stores' => $data['stores'],
                    'urgency_levels' => $data['urgency_levels'],
                    'users' => $data['users'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('NativeRequest Index Error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch native requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show(NativeRequest $request)
    {
        try {
            

            $data = $this->service->getDetails($request);

            return response()->json([
                'success' => true,
                'data' => $data['request'],
                'technicians' => $data['technicians']
            ]);

        } catch (\Exception $e) {

            Log::error('NativeRequest Show Error', [
                'message' => $e->getMessage(),
                'request_id' => $request->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch request details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(UpdateNativeRequestStatusRequest $formRequest, NativeRequest $request)
    {
        try {

            $validated = $formRequest->validated();

            $nativeRequest = $this->service->updateStatus($validated, $request);

            if ($formRequest->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request updated successfully.',
                    'request' => $request,
                ]);
            }

            return redirect()
                ->route('admin.native.index')
                ->with('success', 'Request #' . $request->id . ' updated successfully.');

        } catch (\Exception $e) {

            Log::error('Update Native Request Status Error', [
                'message' => $e->getMessage(),
                'request_id' => $request->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update request status'
            ], 500);
        }
    }
    public function ticketReport(Request $request)
    {
        try {

            $maintenanceRequests = $this->service->ticketReport($request);

            return response()->json([
                'success' => true,
                'data' => $maintenanceRequests
            ]);

        } catch (\Exception $e) {

            Log::error('Ticket Report Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate ticket report'
            ], 500);
        }
    }
}