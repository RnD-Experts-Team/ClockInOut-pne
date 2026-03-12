<?php

namespace App\Services\Api;

use App\Models\TaskAssignment;
use App\Services\Api\Admin\CognitoFormsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class UserTaskService
{
    public function index()
    {
        $user = Auth::user();

        // Get user's task assignments with related data
        $taskAssignments = TaskAssignment::with([
            'maintenanceRequest.store',
            'maintenanceRequest.urgencyLevel',
            'assignedUser',
            'scheduleShift'
        ])
            ->where('assigned_user_id', $user->id)
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get task statistics
        $taskStats = [
            'total' => $taskAssignments->total(),
            'pending' => TaskAssignment::where('assigned_user_id', $user->id)
                ->where('status', 'pending')->count(),
            'in_progress' => TaskAssignment::where('assigned_user_id', $user->id)
                ->where('status', 'in_progress')->count(),
            'completed' => TaskAssignment::where('assigned_user_id', $user->id)
                ->where('status', 'completed')->count(),
        ];

        return [
            'taskAssignments' => $taskAssignments,
            'taskStats' => $taskStats
        ];
    }
     public function updateStatus($request, TaskAssignment $taskAssignment)
    {
        // Ensure user can only update their own tasks
        if ($taskAssignment->assigned_user_id !== Auth::id()) {
            throw new \Exception('Unauthorized action.');
        }


        try {
            DB::beginTransaction();

            $statusMap = [
                'pending' => 'on_hold',
                'in_progress' => 'in_progress',
                'completed' => 'done',
            ];

            $newTaskStatus = $request->input('status');
            $newMaintenanceStatus = $statusMap[$newTaskStatus];
            $costs = $request->input('costs');
            $howWeFixedIt = $request->input('how_we_fixed_it');
            $userId = Auth::id();

            // Update TaskAssignment
            $taskAssignment->update([
                'status' => $newTaskStatus,
                'updated_at' => now(),
            ]);

            $maintenanceRequest = $taskAssignment->maintenanceRequest;

            if (!$maintenanceRequest) {
                throw new \Exception('No related maintenance request found for this task.');
            }

            $maintenanceRequest->updateStatus(
                $newMaintenanceStatus,
                $newTaskStatus === 'completed' ? $costs : null,
                $newTaskStatus === 'completed' ? $howWeFixedIt : null,
                $userId
            );

            if ($maintenanceRequest->form_id) {
                $cognitoService = app(CognitoFormsService::class);

                $cognitoStatusMap = [
                    'on_hold' => 'On Hold',
                    'in_progress' => 'In Progress',
                    'done' => 'Done',
                    'canceled' => 'Canceled',
                ];

                $cognitoData = [
                    'CorrespondenceInternalUseOnly' => [
                        'Status' => $cognitoStatusMap[$newMaintenanceStatus] ?? $newMaintenanceStatus,
                        'NotesFromMaintenanceTeam' => $newTaskStatus === 'completed' ? $howWeFixedIt : null,
                    ],
                    'Entry' => [
                        'Action' => 'Update',
                        'Role' => 'Internal',
                    ],
                ];

                if ($newTaskStatus === 'completed') {
                    $cognitoData['CorrespondenceInternalUseOnly']['Costs'] = $costs;
                }

                $cognitoService->updateEntry(
                    $maintenanceRequest->form_id,
                    $maintenanceRequest->entry_number,
                    $cognitoData
                );

                Log::info('Task status updated and sent to Cognito Forms', [
                    'task_id' => $taskAssignment->id,
                    'maintenance_request_id' => $maintenanceRequest->id,
                    'new_status' => $newTaskStatus,
                    'cognito_form_id' => $maintenanceRequest->form_id,
                    'cognito_entry_id' => $maintenanceRequest->entry_number,
                ]);
            }

            DB::commit();

            return $taskAssignment;

        } catch (\Exception $e) {

            DB::rollback();

            Log::error('Failed to update task status', [
                'task_id' => $taskAssignment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}