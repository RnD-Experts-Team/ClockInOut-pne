<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TaskCalendarController extends Controller
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Task assignment calendar view
     */
    public function index(Request $request): View
    {
        $currentDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        $userId = $request->get('user_id');

        // Get task-related events
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $query = CalendarEvent::byDateRange($startOfMonth, $endOfMonth)
            ->where('event_type', 'task_assignment')
            ->with('creator', 'relatedModel');

        if ($userId) {
            $query->byUser($userId);
        }

        $taskEvents = $query->orderBy('start_date')->get();

        // Get all users for assignment
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        // Get task statistics
        $statistics = [
            'total_tasks' => $taskEvents->count(),
            'completed_tasks' => $taskEvents->where('end_date', '<=', Carbon::today())->count(),
            'pending_tasks' => $taskEvents->where('start_date', '>', Carbon::today())->count(),
            'overdue_tasks' => $taskEvents->where('start_date', '<', Carbon::today())
                ->whereNull('end_date')->count(),
        ];

        return view('calendar.tasks.index', compact(
            'currentDate',
            'taskEvents',
            'users',
            'userId',
            'statistics'
        ));
    }

    /**
     * Get task events for calendar (AJAX)
     */
    public function getTaskEvents(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));
        $userId = $request->get('user_id');

        $filters = [
            'event_type' => 'task_assignment',
            'user_id' => $userId,
        ];

        $events = $this->calendarService->getFormattedEvents($start, $end, $filters);

        // Add task-specific styling
        $events = collect($events)->map(function ($event) {
            if ($event['extendedProps']['event_type'] === 'task_assignment') {
                $event['classNames'] = ['task-event'];

                // Color code by status
                if (isset($event['extendedProps']['status'])) {
                    $event['backgroundColor'] = $this->getTaskStatusColor($event['extendedProps']['status']);
                    $event['borderColor'] = $event['backgroundColor'];
                }
            }
            return $event;
        })->toArray();

        return response()->json($events);
    }

    /**
     * Assign task to user for specific date
     */
    public function assignTask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date|after_or_equal:today',
            'assigned_time' => 'nullable|date_format:H:i',
            'estimated_hours' => 'nullable|numeric|min:0.5|max:12',
            'priority' => 'required|in:low,normal,high,urgent',
            'task_type' => 'required|in:maintenance,inspection,cleaning,repair,installation',
            'task_assignment_id' => 'nullable|exists:task_assignments,id',
        ]);

        // Check for scheduling conflicts
        $conflicts = $this->checkSchedulingConflicts(
            $validated['assigned_user_id'],
            $validated['assigned_date'],
            $validated['assigned_time'] ?? '09:00',
            $validated['estimated_hours'] ?? 2
        );

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduling conflict detected',
                'conflicts' => $conflicts
            ], 422);
        }

        // Create calendar event for task assignment
        $calendarEvent = CalendarEvent::create([
            'title' => 'Task: ' . $validated['title'],
            'description' => $this->buildTaskDescription($validated),
            'event_type' => 'task_assignment',
            'start_date' => $validated['assigned_date'],
            'start_time' => $validated['assigned_time'] ?? '09:00',
            'end_time' => $this->calculateTaskEndTime($validated['assigned_time'] ?? '09:00', $validated['estimated_hours'] ?? 2),
            'is_all_day' => false,
            'color_code' => $this->getTaskTypeColor($validated['task_type']),
            'related_model_type' => $validated['task_assignment_id'] ? 'App\Models\TaskAssignment' : null,
            'related_model_id' => $validated['task_assignment_id'],
            'created_by' => $validated['assigned_user_id'],
        ]);

        // Update task assignment if provided
        if ($validated['task_assignment_id']) {
            if (class_exists('App\Models\TaskAssignment')) {
                $taskAssignment = \App\Models\TaskAssignment::find($validated['task_assignment_id']);
                if ($taskAssignment) {
                    $taskAssignment->update([
                        'assigned_user_id' => $validated['assigned_user_id'],
                        'assigned_date' => $validated['assigned_date'],
                        'estimated_hours' => $validated['estimated_hours'] ?? 2,
                        'status' => 'assigned',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Task assigned successfully',
            'event' => $calendarEvent->formatForCalendar()
        ]);
    }

    /**
     * Update task schedule
     */
    public function updateTaskSchedule(Request $request, CalendarEvent $event): JsonResponse
    {
        if ($event->event_type !== 'task_assignment') {
            return response()->json([
                'success' => false,
                'message' => 'Only task events can be rescheduled'
            ], 422);
        }

        $validated = $request->validate([
            'new_date' => 'required|date',
            'new_time' => 'nullable|date_format:H:i',
            'estimated_hours' => 'nullable|numeric|min:0.5|max:12',
        ]);

        // Check for conflicts with new schedule
        $assignedUserId = $event->created_by; // Assuming created_by is the assigned user
        $conflicts = $this->checkSchedulingConflicts(
            $assignedUserId,
            $validated['new_date'],
            $validated['new_time'] ?? $event->start_time,
            $validated['estimated_hours'] ?? 2,
            $event->id // Exclude current event from conflict check
        );

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduling conflict detected',
                'conflicts' => $conflicts
            ], 422);
        }

        $event->update([
            'start_date' => $validated['new_date'],
            'start_time' => $validated['new_time'] ?? $event->start_time,
            'end_time' => $this->calculateTaskEndTime(
                $validated['new_time'] ?? $event->start_time,
                $validated['estimated_hours'] ?? 2
            ),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task schedule updated successfully',
            'event' => $event->formatForCalendar()
        ]);
    }

    /**
     * Get user workload for specific period
     */
    public function getUserWorkload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $taskEvents = CalendarEvent::byDateRange($startDate, $endDate)
            ->byUser($validated['user_id'])
            ->where('event_type', 'task_assignment')
            ->get();

        $workload = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayTasks = $taskEvents->where('start_date', $currentDate->format('Y-m-d'));
            $totalHours = 0;

            foreach ($dayTasks as $task) {
                // Extract estimated hours from description or use default
                $hours = $this->extractHoursFromTask($task);
                $totalHours += $hours;
            }

            $workload[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'task_count' => $dayTasks->count(),
                'total_hours' => $totalHours,
                'is_overloaded' => $totalHours > 8, // More than 8 hours
                'tasks' => $dayTasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'start_time' => $task->start_time,
                        'estimated_hours' => $this->extractHoursFromTask($task),
                    ];
                })->values(),
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'workload' => $workload,
            'summary' => [
                'total_tasks' => $taskEvents->count(),
                'total_hours' => array_sum(array_column($workload, 'total_hours')),
                'overloaded_days' => count(array_filter($workload, fn($day) => $day['is_overloaded'])),
                'average_hours_per_day' => count($workload) > 0 ? array_sum(array_column($workload, 'total_hours')) / count($workload) : 0,
            ]
        ]);
    }

    /**
     * Check for scheduling conflicts
     */
    private function checkSchedulingConflicts(int $userId, string $date, string $startTime, float $estimatedHours, ?int $excludeEventId = null): array
    {
        $startDateTime = Carbon::parse($date . ' ' . $startTime);
        $endDateTime = $startDateTime->copy()->addHours($estimatedHours);

        $query = CalendarEvent::whereDate('start_date', $date)
            ->byUser($userId)
            ->where('event_type', 'task_assignment');

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        $existingTasks = $query->get();
        $conflicts = [];

        foreach ($existingTasks as $task) {
            $taskStart = Carbon::parse($task->start_date->format('Y-m-d') . ' ' . $task->start_time);
            $taskHours = $this->extractHoursFromTask($task);
            $taskEnd = $taskStart->copy()->addHours($taskHours);

            // Check for time overlap
            if ($startDateTime->lt($taskEnd) && $endDateTime->gt($taskStart)) {
                $conflicts[] = [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'existing_start' => $taskStart->format('H:i'),
                    'existing_end' => $taskEnd->format('H:i'),
                    'conflict_type' => 'time_overlap',
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Build task description
     */
    private function buildTaskDescription(array $data): string
    {
        $description = $data['description'] ?? '';
        $description .= "\n\nTask Type: " . ucfirst($data['task_type']);
        $description .= "\nPriority: " . ucfirst($data['priority']);

        if ($data['estimated_hours']) {
            $description .= "\nEstimated Hours: " . $data['estimated_hours'];
        }

        $user = \App\Models\User::find($data['assigned_user_id']);
        if ($user) {
            $description .= "\nAssigned to: " . $user->name;
        }

        return trim($description);
    }

    /**
     * Get task type color
     */
    private function getTaskTypeColor(string $taskType): string
    {
        return match($taskType) {
            'maintenance' => '#dc3545',  // red
            'inspection' => '#17a2b8',   // blue
            'cleaning' => '#28a745',     // green
            'repair' => '#fd7e14',       // orange
            'installation' => '#6f42c1', // purple
            default => '#6c757d',        // gray
        };
    }

    /**
     * Get task status color
     */
    private function getTaskStatusColor(string $status): string
    {
        return match($status) {
            'completed' => '#28a745',    // green
            'in_progress' => '#ffc107',  // yellow
            'assigned' => '#17a2b8',     // blue
            'overdue' => '#dc3545',      // red
            default => '#6c757d',        // gray
        };
    }

    /**
     * Calculate task end time
     */
    private function calculateTaskEndTime(string $startTime, float $hours): string
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = $start->addHours($hours);
        return $end->format('H:i');
    }

    /**
     * Extract hours from task description
     */
    private function extractHoursFromTask(CalendarEvent $task): float
    {
        if (preg_match('/Estimated Hours:\s*(\d+(?:\.\d+)?)/i', $task->description, $matches)) {
            return (float) $matches[1];
        }
        return 2.0; // default 2 hours
    }
}
