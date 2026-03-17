<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\AssignTaskRequest;
use App\Http\Requests\Api\Admin\GetTaskCalendarEventsRequest;
use App\Http\Requests\Api\Admin\GetTaskEventsRequest;
use App\Http\Requests\Api\Admin\GetUserWorkloadRequest;
use App\Http\Requests\Api\Admin\UpdateTaskScheduleRequest;
use App\Models\CalendarEvent;
use App\Services\Api\Admin\TaskCalendarService;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Log;

class TaskCalendarController extends Controller
{
    public function __construct(
        private TaskCalendarService $service
    ) {}

    public function index(GetTaskEventsRequest $request): JsonResponse
    {
        try {
            $result = $this->service->index($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('Task Events API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch task events.',
                'data' => [
                    'current_date' => now()->toDateString(),
                    'task_events' => [],
                    'users' => [],
                    'statistics' => [
                        'total_tasks' => 0,
                        'completed_tasks' => 0,
                        'pending_tasks' => 0,
                        'overdue_tasks' => 0,
                    ]
                ]
            ], 500);
        }
    }
    public function getTaskEvents(GetTaskCalendarEventsRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getTaskEvents($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('Task Events API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch task events.',
                'events' => []
            ], 500);
        }
    }
    public function getUserWorkload(GetUserWorkloadRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getUserWorkload($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('User Workload Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user workload.',
                'workload' => [],
                'summary' => []
            ], 500);
        }
    }
    public function assignTask(AssignTaskRequest $request): JsonResponse
    {
        try {
            $result = $this->service->assignTask($request->validated());

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'event' => $result['event'] ?? null,
                'conflicts' => $result['conflicts'] ?? null,
            ], $result['status']);

        } catch (Throwable $e) {

            Log::error('Assign Task Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign task.',
            ], 500);
        }
    }

    public function updateTaskSchedule(UpdateTaskScheduleRequest $request, CalendarEvent $event): JsonResponse {
        try {
            $result = $this->service->updateTaskSchedule($event, $request->validated());

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'event' => $result['event'] ?? null,
                'conflicts' => $result['conflicts'] ?? null,
            ], $result['status']);

        } catch (Throwable $e) {

            Log::error('Update Task Schedule Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task schedule.',
            ], 500);
        }
    }

}