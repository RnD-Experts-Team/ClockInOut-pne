<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateMaintenanceStatusRequest;
use App\Http\Requests\Api\Admin\BulkUpdateMaintenanceStatusRequest;
use App\Models\MaintenanceRequest;
use App\Services\Api\Admin\MaintenanceRequestService;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        protected MaintenanceRequestService $maintenanceService
    ) {}

    public function updateStatus(
        UpdateMaintenanceStatusRequest $request,
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestService $service
        ): JsonResponse {

        $result = $service->updateStatus($request, $maintenanceRequest);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Validation error',
                'errors' => $result['errors'] ?? null
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message']
        ]);
    }

 
    public function bulkUpdateStatus(
        BulkUpdateMaintenanceStatusRequest $request,
        MaintenanceRequestService $service
            ) {

        try {

            $updatedCount = $service->bulkUpdateStatus(
                $request->validated(),
                auth()->id() ?? 1
            );

            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "Successfully updated {$updatedCount} maintenance requests."
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function export(Request $request)
    {
        try {

            $csvData = $this->maintenanceService->export($request);

             $filename = 'maintenance_requests_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            return response()->stream(function () use ($csvData) {

                $file = fopen('php://output', 'w');

                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);

            }, 200, $headers);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try{
            $data = $this->maintenanceService->index($request);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance requests fetched successfully.',
                'data' => $data,
            ], 200);
        }catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }

        
    }
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        try {

            $data = $this->maintenanceService->show($maintenanceRequest);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request fetched successfully.',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch maintenance request.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function destroy(MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        try {

            $this->maintenanceService->destroy($maintenanceRequest);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance request deleted successfully.'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    
    
  

}