<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\DailyClockEvent;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ClockEventController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Clock events overview page
     */
    public function index(Request $request): View
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        $userId = $request->get('user_id');

        $query = DailyClockEvent::byDate($date)->with('user');

        if ($userId) {
            $query->byUser($userId);
        }

        $clockEvents = $query->orderBy('event_timestamp')->get();

        // Group events by user
        $eventsByUser = $clockEvents->groupBy('user_id');

        // Calculate work hours for each user
        $workHoursSummary = [];
        foreach ($eventsByUser as $userIdKey => $userEvents) {
            $workHours = DailyClockEvent::calculateWorkHours($userIdKey, $date);
            $user = $userEvents->first()->user;

            $workHoursSummary[] = [
                'user_id' => $userIdKey,
                'user_name' => $user ? $user->name : 'Unknown User',
                'total_events' => $userEvents->count(),
                'work_hours' => $workHours,
                'first_clock_in' => $userEvents->where('event_type', 'clock_in')->first(),
                'last_clock_out' => $userEvents->where('event_type', 'clock_out')->last(),
            ];
        }

        // Get all users for filter dropdown
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return view('calendar.clock-events.index', compact(
            'clockEvents',
            'eventsByUser',
            'workHoursSummary',
            'date',
            'users',
            'userId'
        ));
    }

    /**
     * Record new clock event
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'event_type' => 'required|in:clock_in,clock_out,break_start,break_end',
            'notes' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location' => 'nullable|string|max:255',
        ]);

        // Check for duplicate recent events
        $recentEvent = DailyClockEvent::byUser($validated['user_id'])
            ->where('event_type', $validated['event_type'])
            ->where('event_timestamp', '>', Carbon::now()->subMinutes(2))
            ->first();

        if ($recentEvent) {
            return response()->json([
                'success' => false,
                'message' => 'Similar event was recorded recently. Please wait before recording again.'
            ], 422);
        }

        // Validate event sequence (e.g., can't clock out without clocking in)
        $validationResult = $this->validateEventSequence($validated['user_id'], $validated['event_type']);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 422);
        }

        $clockEvent = $this->activityLogService->logClockEvent(
            $validated['user_id'],
            $validated['event_type'],
            $validated['location'],
            $validated['latitude'],
            $validated['longitude'],
            $validated['notes']
        );

        return response()->json([
            'success' => true,
            'message' => 'Clock event recorded successfully',
            'event' => [
                'id' => $clockEvent->id,
                'event_type' => $clockEvent->event_type,
                'event_type_label' => $clockEvent->getEventTypeLabel(),
                'timestamp' => $clockEvent->getFormattedDateTime(),
                'location' => $clockEvent->getLocationString(),
                'notes' => $clockEvent->notes,
            ]
        ]);
    }

    /**
     * Get clock events for specific date and user
     */
    public function getEvents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $date = Carbon::parse($validated['date']);
        $query = DailyClockEvent::byDate($date)->with('user');

        if ($validated['user_id']) {
            $query->byUser($validated['user_id']);
        }

        $events = $query->orderBy('event_timestamp')->get();

        return response()->json([
            'success' => true,
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'user_name' => $event->user ? $event->user->name : 'Unknown',
                    'event_type' => $event->event_type,
                    'event_type_label' => $event->getEventTypeLabel(),
                    'timestamp' => $event->getFormattedDateTime(),
                    'time_only' => $event->getFormattedTime(),
                    'location' => $event->getLocationString(),
                    'notes' => $event->notes,
                    'icon' => $event->getEventTypeIcon(),
                    'color' => $event->getEventTypeColor(),
                ];
            })
        ]);
    }

    /**
     * Get work hours summary for date range
     */
    public function getWorkHoursSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $query = DailyClockEvent::byDateRange($startDate, $endDate);

        if (!empty($validated['user_ids'])) {
            $query->whereIn('user_id', $validated['user_ids']);
        }

        $clockEvents = $query->with('user')->get()->groupBy('user_id');
        $summary = [];

        foreach ($clockEvents as $userId => $userEvents) {
            $eventsByDate = $userEvents->groupBy(function($event) {
                return $event->event_timestamp->format('Y-m-d');
            });

            $totalHours = 0;
            $workingDays = 0;
            $dailyBreakdown = [];

            foreach ($eventsByDate as $date => $dayEvents) {
                $dayHours = DailyClockEvent::calculateWorkHours($userId, Carbon::parse($date));
                $totalHours += $dayHours;

                if ($dayHours > 0) {
                    $workingDays++;
                }

                $dailyBreakdown[$date] = $dayHours;
            }

            $user = $userEvents->first()->user;
            $summary[] = [
                'user_id' => $userId,
                'user_name' => $user ? $user->name : 'Unknown User',
                'total_hours' => round($totalHours, 2),
                'working_days' => $workingDays,
                'average_hours_per_day' => $workingDays > 0 ? round($totalHours / $workingDays, 2) : 0,
                'daily_breakdown' => $dailyBreakdown,
                'total_events' => $userEvents->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $startDate->diffInDays($endDate) + 1,
            ]
        ]);
    }

    /**
     * Show detailed clock event
     */
    public function show(DailyClockEvent $clockEvent): View
    {
        $clockEvent->load('user');

        // Get related events for the same day and user
        $relatedEvents = DailyClockEvent::byUser($clockEvent->user_id)
            ->byDate($clockEvent->event_timestamp->toDateString())
            ->where('id', '!=', $clockEvent->id)
            ->orderBy('event_timestamp')
            ->get();

        $workHours = DailyClockEvent::calculateWorkHours(
            $clockEvent->user_id,
            $clockEvent->event_timestamp
        );

        return view('calendar.clock-events.show', compact(
            'clockEvent',
            'relatedEvents',
            'workHours'
        ));
    }

    /**
     * Update clock event
     */
    public function update(Request $request, DailyClockEvent $clockEvent): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
        ]);

        $clockEvent->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Clock event updated successfully'
        ]);
    }

    /**
     * Delete clock event
     */
    public function destroy(DailyClockEvent $clockEvent): JsonResponse
    {
        // Delete related calendar event if exists
        $clockEvent->calendarEvent?->delete();

        $clockEvent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Clock event deleted successfully'
        ]);
    }

    /**
     * Validate event sequence
     */
    private function validateEventSequence(int $userId, string $eventType): array
    {
        $lastEvent = DailyClockEvent::byUser($userId)
            ->whereDate('event_timestamp', Carbon::today())
            ->orderBy('event_timestamp', 'desc')
            ->first();

        if (!$lastEvent) {
            // First event of the day
            if ($eventType !== 'clock_in') {
                return [
                    'valid' => false,
                    'message' => 'First event of the day must be clock in.'
                ];
            }
            return ['valid' => true];
        }

        // Validate sequence based on last event
        $invalidSequences = [
            'clock_in' => ['clock_in', 'break_end'],
            'clock_out' => ['clock_out', 'break_start', 'break_end'],
            'break_start' => ['clock_out', 'break_start'],
            'break_end' => ['clock_in', 'break_end'],
        ];

        if (in_array($eventType, $invalidSequences[$lastEvent->event_type] ?? [])) {
            return [
                'valid' => false,
                'message' => "Cannot {$eventType} after {$lastEvent->event_type}."
            ];
        }

        return ['valid' => true];
    }
}
