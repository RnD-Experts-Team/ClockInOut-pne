<?php

namespace App\Services\Api\Admin;

use App\Models\DailyClockEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClockEventService
{
     
    public function getClockEventsData($request)
    {
        $date = $request->get('date')
            ? Carbon::parse($request->get('date'))
            : Carbon::today();

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
                'first_clock_in' => $userEvents
                    ->where('event_type', 'clock_in')
                    ->first(),
                'last_clock_out' => $userEvents
                    ->where('event_type', 'clock_out')
                    ->last(),
            ];
        }

        // Users for filter
        $users = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return [
            'date' => $date->toDateString(),
            'user_id_filter' => $userId,
            'clock_events' => $clockEvents,
            'events_by_user' => $eventsByUser,
            'work_hours_summary' => $workHoursSummary,
            'users' => $users,
        ];
    }
    public function store(array $validated): array
    {
        // duplicate check
        $recentEvent = DailyClockEvent::byUser($validated['user_id'])
            ->where('event_type', $validated['event_type'])
            ->where('event_timestamp', '>', Carbon::now()->subMinutes(2))
            ->first();

        if ($recentEvent) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'Similar event was recorded recently. Please wait before recording again.'
                ]
            ];
        }

        // validate sequence
        $validationResult = $this->validateEventSequence(
            $validated['user_id'],
            $validated['event_type']
        );

        if (!$validationResult['valid']) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => $validationResult['message']
                ]
            ];
        }

        // create event
        $clockEvent = $this->logClockEvent(
            $validated['user_id'],
            $validated['event_type'],
            $validated['location'] ?? null,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
            $validated['notes'] ?? null
        );

        return [
            'status' => 200,
            'data' => [
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
            ]
        ];
    }
    public function show(DailyClockEvent $clockEvent): array
    {
        $clockEvent->load('user');

        // related events
        $relatedEvents = DailyClockEvent::byUser($clockEvent->user_id)
            ->byDate($clockEvent->event_timestamp->toDateString())
            ->where('id', '!=', $clockEvent->id)
            ->orderBy('event_timestamp')
            ->get();

        // work hours
        $workHours = DailyClockEvent::calculateWorkHours(
            $clockEvent->user_id,
            $clockEvent->event_timestamp
        );

        return [
            'success' => true,
            'event' => [
                'id' => $clockEvent->id,
                'event_type' => $clockEvent->event_type,
                'event_type_label' => $clockEvent->getEventTypeLabel(),
                'timestamp' => $clockEvent->getFormattedDateTime(),
                'location' => $clockEvent->getLocationString(),
                'notes' => $clockEvent->notes,
                'user' => [
                    'id' => $clockEvent->user->id,
                    'name' => $clockEvent->user->name,
                ],
            ],

            'related_events' => $relatedEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'event_type_label' => $event->getEventTypeLabel(),
                    'timestamp' => $event->getFormattedDateTime(),
                    'location' => $event->getLocationString(),
                    'notes' => $event->notes,
                ];
            }),

            'work_hours' => $workHours
        ];
    }
    public function update(DailyClockEvent $clockEvent, array $validated): array
    {
        $clockEvent->update($validated);

        return [
            'success' => true,
            'message' => 'Clock event updated successfully',
        ];
    }
    public function destroy(DailyClockEvent $clockEvent): array
    {
        // Delete related calendar event if exists
        $clockEvent->calendarEvent?->delete();

        $clockEvent->delete();

        return [
            'success' => true,
            'message' => 'Clock event deleted successfully',
        ];
    }
    public function getEvents(array $validated): array
    {
        $date = Carbon::parse($validated['date']);

        $query = DailyClockEvent::byDate($date)->with('user');

        if (!empty($validated['user_id'])) {
            $query->byUser($validated['user_id']);
        }

        $events = $query->orderBy('event_timestamp')->get();

        return [
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
            }),
        ];
    }
    public function getWorkHoursSummary(array $validated): array
    {
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $query = DailyClockEvent::byDateRange($startDate, $endDate);

        if (!empty($validated['user_ids'])) {
            $query->whereIn('user_id', $validated['user_ids']);
        }

        $clockEvents = $query->with('user')->get()->groupBy('user_id');

        $summary = [];

        foreach ($clockEvents as $userId => $userEvents) {

            $eventsByDate = $userEvents->groupBy(function ($event) {
                return $event->event_timestamp->format('Y-m-d');
            });

            $totalHours = 0;
            $workingDays = 0;
            $dailyBreakdown = [];

            foreach ($eventsByDate as $date => $dayEvents) {

                $dayHours = DailyClockEvent::calculateWorkHours(
                    $userId,
                    Carbon::parse($date)
                );

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
                'average_hours_per_day' => $workingDays > 0
                    ? round($totalHours / $workingDays, 2)
                    : 0,
                'daily_breakdown' => $dailyBreakdown,
                'total_events' => $userEvents->count(),
            ];
        }

        return [
            'success' => true,
            'summary' => $summary,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $startDate->diffInDays($endDate) + 1,
            ]
        ];
    }


 
    private function validateEventSequence(int $userId, string $eventType): array
    {
        $lastEvent = DailyClockEvent::byUser($userId)
            ->whereDate('event_timestamp', Carbon::today())
            ->orderBy('event_timestamp', 'desc')
            ->first();

        if (!$lastEvent) {
            if ($eventType !== 'clock_in') {
                return [
                    'valid' => false,
                    'message' => 'First event of the day must be clock in.'
                ];
            }
            return ['valid' => true];
        }

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

 
    public function logClockEvent(  int $userId,string $eventType,?string $location = null,?float $latitude = null,?float $longitude = null,?string $notes = null ): DailyClockEvent 
    {
        $clockEvent = DailyClockEvent::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'event_timestamp' => Carbon::now(),
            'location' => $location,
            'ip_address' => request()->ip(),
            'notes' => $notes,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $clockEvent->createCalendarEvent();

        return $clockEvent;
    }
    
}