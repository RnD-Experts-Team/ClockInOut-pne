<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateTaskAssignmentRequest;
use App\Http\Requests\Api\Admin\UpdateTaskAssignmentStatusRequest;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use App\Services\Api\Admin\TaskAssignmentService;
use Illuminate\Support\Facades\Log;

class TaskAssignmentController extends Controller
{
    protected $service;

    public function __construct(TaskAssignmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {

            $data = $this->service->getTaskAssignments($request);

            return response()->json([
                'success' => true,
                'data' => $data['task_assignments']->items(),
                'pagination' => [
                    'current_page' => $data['task_assignments']->currentPage(),
                    'last_page' => $data['task_assignments']->lastPage(),
                    'per_page' => $data['task_assignments']->perPage(),
                    'total' => $data['task_assignments']->total()
                ],
                'filters' => [
                    'employees' => $data['employees'],
                    'stores' => $data['stores']
                ]
            ]);

        } catch (\Exception $e) {

            Log::error('TaskAssignment Index Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch task assignments'
            ], 500);
        }
    }
    public function show(TaskAssignment $taskAssignment)
    {
        try {

            $taskAssignment = $this->service->getTaskAssignmentDetails($taskAssignment);

            return response()->json([
                'success' => true,
                'data' => $taskAssignment
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch task assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(UpdateTaskAssignmentRequest $request, TaskAssignment $taskAssignment)
    {
        try {

            $taskAssignment = $this->service->updateTaskAssignment($request, $taskAssignment);

            return response()->json([
                'success' => true,
                'message' => 'Task assignment updated successfully.',
                'data' => $taskAssignment
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(UpdateTaskAssignmentStatusRequest $request, TaskAssignment $taskAssignment)
    {
        try {
            $taskAssignment = $this->service->updateStatus($request, $taskAssignment);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully!',
                'data' => $taskAssignment,
            ]);
        } catch (\Exception $e) {
            Log::error('TaskAssignment Update Status Error', [
                'message' => $e->getMessage(),
                'task_assignment_id' => $taskAssignment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
  
}