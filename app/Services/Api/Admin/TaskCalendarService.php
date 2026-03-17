<?php

namespace App\Services\Api\Admin;

use App\Models\CalendarEvent;
use App\Models\TaskAssignment;
use App\Models\User;
use Carbon\Carbon;

class TaskCalendarService
{
      public function __construct(
        private CalendarService $calendarService
    ) {}

    public function getTaskEvents(array $validated): array
    {
        $start = Carbon::parse($validated['start']);
        $end = Carbon::parse($validated['end']);
        $userId = $validated['user_id'] ?? null;

        $filters = [
            'event_type' => 'task_assignment',
            'user_id' => $userId,
        ];

        $events = $this->calendarService->getFormattedEvents($start, $end, $filters);

        $events = collect($events)->map(function ($event) {

            if (($event['extendedProps']['event_type'] ?? null) === 'task_assignment') {

                $event['classNames'] = ['task-event'];

                if (isset($event['extendedProps']['status'])) {
                    $color = $this->getTaskStatusColor($event['extendedProps']['status']);
                    $event['backgroundColor'] = $color;
                    $event['borderColor'] = $color;
                }
            }

            return $event;

        })->toArray();

        return [
            'success' => true,
            'events' => $events
        ];
    }

    private function getTaskStatusColor(string $status): string
    {
        return match($status) {
            'completed' => '#28a745',   // green
            'in_progress' => '#17a2b8', // blue
            'pending' => '#ffc107',     // yellow
            'overdue' => '#dc3545',     // red
            default => '#6c757d',       // gray
        };
    }
    public function index(array $validated): array
    {
        $currentDate = isset($validated['date'])
            ? Carbon::parse($validated['date'])
            : Carbon::today();

        $userId = $validated['user_id'] ?? null;

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $query = CalendarEvent::byDateRange($startOfMonth, $endOfMonth)
            ->where('event_type', 'task_assignment')
            ->with(['creator', 'relatedModel']);

        if ($userId) {
            $query->byUser($userId);
        }

        $taskEvents = $query->orderBy('start_date')->get();

        // users
        $users = User::select('id', 'name')->orderBy('name')->get();

        // statistics
        $statistics = [
            'total_tasks' => $taskEvents->count(),
            'completed_tasks' => $taskEvents->where('end_date', '<=', Carbon::today())->count(),
            'pending_tasks' => $taskEvents->where('start_date', '>', Carbon::today())->count(),
            'overdue_tasks' => $taskEvents->where('start_date', '<', Carbon::today())
                ->whereNull('end_date')
                ->count(),
        ];

        return [
            'success' => true,
            'data' => [
                'current_date' => $currentDate->toDateString(),
                'user_id_filter' => $userId,
                'task_events' => $taskEvents,
                'users' => $users,
                'statistics' => $statistics,
            ]
        ];
    }
    public function getUserWorkload(array $validated): array
    {
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $taskEvents = CalendarEvent::byDateRange($startDate, $endDate)
            ->byUser($validated['user_id'])
            ->where('event_type', 'task_assignment')
            ->get();

        $workload = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {

            $dayTasks = $taskEvents->where(
                'start_date',
                $currentDate->format('Y-m-d')
            );

            $totalHours = 0;

            foreach ($dayTasks as $task) {
                $hours = $this->extractHoursFromTask($task);
                $totalHours += $hours;
            }

            $workload[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'task_count' => $dayTasks->count(),
                'total_hours' => $totalHours,
                'is_overloaded' => $totalHours > 8,
                'tasks' => $dayTasks->map(function ($task) {
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

        return [
            'success' => true,
            'workload' => $workload,
            'summary' => [
                'total_tasks' => $taskEvents->count(),
                'total_hours' => array_sum(array_column($workload, 'total_hours')),
                'overloaded_days' => count(array_filter($workload, fn($day) => $day['is_overloaded'])),
                'average_hours_per_day' => count($workload) > 0
                    ? array_sum(array_column($workload, 'total_hours')) / count($workload)
                    : 0,
            ]
        ];
    }

    private function extractHoursFromTask(CalendarEvent $task): float
    {
        if (preg_match('/Estimated Hours:\s*(\d+(?:\.\d+)?)/i', $task->description, $matches)) {
            return (float) $matches[1];
        }

        return 2.0;
    }
    public function assignTask(array $validated): array
    {
        // check conflicts
        $conflicts = $this->checkSchedulingConflicts(
            $validated['assigned_user_id'],
            $validated['assigned_date'],
            $validated['assigned_time'] ?? '09:00',
            $validated['estimated_hours'] ?? 2
        );

        if (!empty($conflicts)) {
            return [
                'success' => false,
                'message' => 'Scheduling conflict detected',
                'conflicts' => $conflicts,
                'status' => 422
            ];
        }

        // create event
        $calendarEvent = CalendarEvent::create([
            'title' => 'Task: ' . $validated['title'],
            'description' => $this->buildTaskDescription($validated),
            'event_type' => 'task_assignment',
            'start_date' => $validated['assigned_date'],
            'start_time' => $validated['assigned_time'] ?? '09:00',
            'end_time' => $this->calculateTaskEndTime(
                $validated['assigned_time'] ?? '09:00',
                $validated['estimated_hours'] ?? 2
            ),
            'is_all_day' => false,
            'color_code' => $this->getTaskTypeColor($validated['task_type']),
            'related_model_type' => $validated['task_assignment_id']
                ? 'App\Models\TaskAssignment'
                : null,
            'related_model_id' => $validated['task_assignment_id'],
            'created_by' => $validated['assigned_user_id'],
        ]);

        // update assignment
        if (!empty($validated['task_assignment_id'])) {

            if (class_exists('App\Models\TaskAssignment')) {

                $taskAssignment = TaskAssignment::find($validated['task_assignment_id']);

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

        return [
            'success' => true,
            'message' => 'Task assigned successfully',
            'event' => $calendarEvent->formatForCalendar(),
            'status' => 200
        ];
    }
    public function updateTaskSchedule(CalendarEvent $event, array $validated): array
    {
        if ($event->event_type !== 'task_assignment') {
            return [
                'success' => false,
                'message' => 'Only task events can be rescheduled',
                'status' => 422
            ];
        }

        $assignedUserId = $event->created_by;

        $conflicts = $this->checkSchedulingConflicts(
            $assignedUserId,
            $validated['new_date'],
            $validated['new_time'] ?? $event->start_time,
            $validated['estimated_hours'] ?? 2,
            $event->id
        );

        if (!empty($conflicts)) {
            return [
                'success' => false,
                'message' => 'Scheduling conflict detected',
                'conflicts' => $conflicts,
                'status' => 422
            ];
        }

        $event->update([
            'start_date' => $validated['new_date'],
            'start_time' => $validated['new_time'] ?? $event->start_time,
            'end_time' => $this->calculateTaskEndTime(
                $validated['new_time'] ?? $event->start_time,
                $validated['estimated_hours'] ?? 2
            ),
        ]);

        return [
            'success' => true,
            'message' => 'Task schedule updated successfully',
            'event' => $event->formatForCalendar(),
            'status' => 200
        ];
    }

 
    private function buildTaskDescription(array $data): string
    {
        $description = $data['description'] ?? '';
        $description .= "\n\nPriority: " . ucfirst($data['priority']);
        $description .= "\nType: " . ucfirst($data['task_type']);

        if (!empty($data['estimated_hours'])) {
            $description .= "\nEstimated Hours: " . $data['estimated_hours'];
        }

        if (!empty($data['assigned_user_id'])) {
            $user = User::find($data['assigned_user_id']);
            if ($user) {
                $description .= "\nAssigned to: " . $user->name;
            }
        }

        return trim($description);
    }

    private function calculateTaskEndTime(string $startTime, float $hours): string
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = $start->addMinutes($hours * 60);

        return $end->format('H:i');
    }

    private function getTaskTypeColor(string $type): string
    {
        return match($type) {
            'maintenance' => '#dc3545',
            'inspection' => '#17a2b8',
            'cleaning' => '#28a745',
            'repair' => '#fd7e14',
            'installation' => '#6f42c1',
            default => '#6c757d',
        };
    }

    private function checkSchedulingConflicts(
        int $userId,
        string $date,
        string $startTime,
        float $hours
    ): array {
        $start = Carbon::createFromFormat('Y-m-d H:i', "$date $startTime");
        $end = $start->copy()->addMinutes($hours * 60);

        $existingEvents = CalendarEvent::where('event_type', 'task_assignment')
            ->whereDate('start_date', $date)
            ->where('created_by', $userId)
            ->get();

        $conflicts = [];

        foreach ($existingEvents as $event) {
            $eventStart = Carbon::createFromFormat('Y-m-d H:i', $event->start_date . ' ' . $event->start_time);
            $eventEnd = Carbon::createFromFormat('Y-m-d H:i', $event->start_date . ' ' . $event->end_time);

            if ($start < $eventEnd && $end > $eventStart) {
                $conflicts[] = [
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                ];
            }
        }

        return $conflicts;
    }
}