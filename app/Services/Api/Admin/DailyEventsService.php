<?php

namespace App\Services\Api\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyEventsService
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function handle(string $date): array
    {
        try {

            $selectedDate = Carbon::parse($date);

            $groupedEvents = $this->calendarService
                ->getDailyEventsGrouped($selectedDate);

            $formattedEvents = [];

            foreach ($groupedEvents as $type => $events) {

                $formattedEvents[$type] = $events->map(function ($event) {

                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'start_time' => $event->start_time,
                        'event_type' => $event->event_type,
                        'color_code' => $event->color_code
                            ?? $this->calendarService->getEventTypeColor($event->event_type),
                    ];

                })->values();
            }

            return [
                'success' => true,
                'data' => $formattedEvents,
                'statistics' => [
                'total_events' => collect($groupedEvents)->flatten()->count(),
                'by_type' => collect($groupedEvents)->map->count()
                ]
            ];

        } catch (\Throwable $e) {

            Log::error('Get daily events error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to load events',
                'data' => []
            ];
        }
    }
}