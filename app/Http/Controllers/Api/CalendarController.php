<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\CalendarEventFormatterService;
use Illuminate\Http\Request;
use App\Services\Api\CalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class CalendarController extends Controller
{

    protected $calendarService;
    protected $formatterService;

    public function __construct(CalendarService $calendarService, CalendarEventFormatterService $formatterService)
    {
        $this->calendarService = $calendarService;
        $this->formatterService = $formatterService;

    }
 
   

    public function index(Request $request)
    {
        try {

            $currentDate = Carbon::today();
            // Use CURRENT MONTH for statistics, not extended range

            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();

            Log::info("Calendar API - using date range: "
                . $startOfMonth->format('Y-m-d')
                . " to "
                . $endOfMonth->format('Y-m-d'));
            // Get upcoming events for sidebar (next 7 days from today)

            $upcomingEvents = $this->calendarService->getUpcomingEvents(7);
            // Get calendar statistics for CURRENT MONTH ONLY

            $statistics = $this->calendarService
                ->getCalendarStatistics($startOfMonth, $endOfMonth);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_date' => $currentDate,
                    'upcoming_events' => $upcomingEvents,
                    'statistics' => $statistics
                ]
            ], 200);

        } catch (\Exception $e) {

            Log::error('Calendar API index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => [
                    'current_date' => Carbon::today(),
                    'upcomingEvents' => collect(),
                    'statistics' => [
                        'lease_expirations' => 0,
                        'lease_renewals' => 0,
                        'maintenance_requests' => 0,
                        'clock_events' => 0,
                        'admin_actions' => 0,
                        'reminders' => 0,
                        'urgency_breakdown' => [
                            'overdue' => 0,
                            'critical' => 0,
                            'high' => 0,
                            'normal' => 0,
                            'info' => 0,
                            'completed' => 0
                        ]
                    ]
                ]
            ], 500);
        }
    }
      public function getEvents(Request $request): JsonResponse
    {
        try {

            Log::info("=== Calendar getEvents called ===");
            Log::info("Request params: " . json_encode($request->all()));
            // Get and clean start/end dates
            $startParam = $request->get('start');
            $endParam = $request->get('end');

            Log::info("Raw start param: " . $startParam);
            Log::info("Raw end param: " . $endParam);
            // Clean the date parameters (remove timezone info)
            $start = $this->formatterService->cleanAndParseDate($startParam);
            $end = $this->formatterService->cleanAndParseDate($endParam);

            Log::info("Parsed start date: " . $start->format('Y-m-d'));
            Log::info("Parsed end date: " . $end->format('Y-m-d'));

            $filters = [
                'event_type' => $request->get('event_type'),
                'user_id' => $request->get('user_id'),
            ];

            Log::info("Applied filters: " . json_encode($filters));
            // Get formatted events from service
            $events = $this->calendarService
                ->getFormattedEvents($start, $end, $filters);

            Log::info("Events returned from service: " . count($events));

            return response()->json($events);

        } catch (\Exception $e) {

            Log::error("❌ Error in getEvents: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([]);
        }
    }


}