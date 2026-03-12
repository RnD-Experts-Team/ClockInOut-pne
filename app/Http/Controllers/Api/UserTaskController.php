<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateTaskStatusRequest;
use App\Models\TaskAssignment;
use App\Services\Api\UserTaskService;

class UserTaskController extends Controller
{
    public function __construct(
        private UserTaskService $taskService
    ) {}

    public function index(Request $request)
    {
        try {

            $data = $this->taskService->index();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to load tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(UpdateTaskStatusRequest $request, TaskAssignment $taskAssignment)
    {
        try {

            $task = $this->taskService->updateStatus($request, $taskAssignment);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully.',
                'data' => $task
            ]);

        } catch (\Exception $e) {

            if ($e->getMessage() === 'Unauthorized action.') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}