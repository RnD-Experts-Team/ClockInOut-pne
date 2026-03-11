<?php

namespace App\Services\Api\Admin;

use App\Services\Api\Admin\CalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyOverviewService
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function handle(?string $date = null): array
    {
        try {

            $selectedDate = $date
                ? Carbon::parse($date)
                : Carbon::today();
            // Get calendar events grouped by type
            $groupedEvents = $this->calendarService
                ->getDailyEventsGrouped($selectedDate);

            return [
                'success' => true,
                'data' => [
                    'selected_date' => $selectedDate->toDateString(),
                    'events' => $groupedEvents
                ]
            ];

        } catch (\Throwable $e) {

            Log::error('Daily overview error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to load daily overview',
                'data' => [
                    'selected_date' => Carbon::today()->toDateString(),
                    'events' => []
                ]
            ];
        }
    }
}