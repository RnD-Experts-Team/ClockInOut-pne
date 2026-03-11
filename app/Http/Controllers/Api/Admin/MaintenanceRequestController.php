<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateMaintenanceStatusRequest;
use App\Http\Requests\Api\Admin\BulkUpdateMaintenanceStatusRequest;
use App\Models\MaintenanceRequest;
use App\Services\Api\Admin\MaintenanceRequestService;
use App\Services\CognitoFormsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Maintenance requests retrieved successfully',
            'data' => [],
        ], 200);
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