<?php

namespace App\Http\Controllers;

use App\Services\CalendarService;
use App\Services\ActivityLogService;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CalendarController extends Controller
{
    protected CalendarService $calendarService;
    protected ActivityLogService $activityLogService;

    public function __construct(CalendarService $calendarService, ActivityLogService $activityLogService)
    {
        $this->calendarService = $calendarService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Main calendar dashboard
     */
    public function index(Request $request): View
    {
        try {
            $currentDate = Carbon::today();

            // Use CURRENT MONTH for statistics, not extended range
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();

            Log::info("Calendar index - using date range: " . $startOfMonth->format('Y-m-d') . " to " . $endOfMonth->format('Y-m-d'));

            // Get upcoming events for sidebar (next 7 days from today)
            $upcomingEvents = $this->calendarService->getUpcomingEvents(7);

            // Get calendar statistics for CURRENT MONTH ONLY
            $statistics = $this->calendarService->getCalendarStatistics($startOfMonth, $endOfMonth);

            return view('calendar.dashboard', compact(
                'currentDate',
                'upcomingEvents',
                'statistics'
            ));
        } catch (\Exception $e) {
            Log::error('Calendar index error: ' . $e->getMessage());

            return view('calendar.dashboard', [
                'currentDate' => Carbon::today(),
                'upcomingEvents' => collect(),
                'statistics' => [
                    'lease_expirations' => 0,
                    'lease_renewals' => 0,        // ➕ NEW
                    'maintenance_requests' => 0,
                    'clock_events' => 0,
                    'admin_actions' => 0,
                    'reminders' => 0,
                    'urgency_breakdown' => [      // ➕ NEW
                        'overdue' => 0,
                        'critical' => 0,
                        'high' => 0,
                        'normal' => 0,
                        'info' => 0,
                        'completed' => 0,
                    ]
                ]
            ]);
        }
    }

    /**
     * Get events for calendar view (AJAX endpoint)
     */
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
            if (is_string($startParam)) {
                $startParam = preg_replace('/\+\d{2}:\d{2}$/', '', $startParam);
                $startParam = preg_replace('/T\d{2}:\d{2}:\d{2}/', '', $startParam);
            }

            if (is_string($endParam)) {
                $endParam = preg_replace('/\+\d{2}:\d{2}$/', '', $endParam);
                $endParam = preg_replace('/T\d{2}:\d{2}:\d{2}/', '', $endParam);
            }

            $start = Carbon::parse($startParam);
            $end = Carbon::parse($endParam);

            Log::info("Parsed start date: " . $start->format('Y-m-d'));
            Log::info("Parsed end date: " . $end->format('Y-m-d'));

            $filters = [
                'event_type' => $request->get('event_type'),
                'user_id' => $request->get('user_id'),
            ];

            Log::info("Applied filters: " . json_encode($filters));

            // Get formatted events from service
            $events = $this->calendarService->getFormattedEvents($start, $end, $filters);

            Log::info("Events returned from service: " . count($events));

            return response()->json($events);

        } catch (\Exception $e) {
            Log::error("❌ Error in getEvents: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            // Return empty array instead of error to prevent 500
            return response()->json([]);
        }
    }
    /**
     * Daily overview page
     */
    public function dailyOverview(Request $request, ?string $date = null): View
    {
        try {
            $selectedDate = $date ? Carbon::parse($date) : Carbon::today();

            // Get calendar events grouped by type
            $groupedEvents = $this->calendarService->getDailyEventsGrouped($selectedDate);

            return view('calendar.daily-overview', compact(
                'selectedDate',
                'groupedEvents'
            ));
        } catch (\Exception $e) {
            Log::error('Daily overview error: ' . $e->getMessage());

            return view('calendar.daily-overview', [
                'selectedDate' => Carbon::today(),
                'groupedEvents' => []
            ]);
        }
    }
    /**
     * Export daily data
     */
    public function exportDailyData(Request $request, string $date)
    {
        $selectedDate = Carbon::parse($date);
        $overview = $this->activityLogService->getDailyOverview($selectedDate);
        $groupedEvents = $this->calendarService->getDailyEventsGrouped($selectedDate);

        $fileName = "daily_overview_{$selectedDate->format('Y-m-d')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($overview, $groupedEvents, $selectedDate) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['Daily Overview Report - ' . $selectedDate->format('F j, Y')]);
            fputcsv($file, []);

            // Statistics
            fputcsv($file, ['Statistics:']);
            fputcsv($file, ['Total Events', $overview['statistics']['total_calendar_events'] ?? 0]);
            fputcsv($file, ['Admin Actions', $overview['statistics']['total_admin_actions'] ?? 0]);
            fputcsv($file, ['Clock Events', $overview['statistics']['total_clock_events'] ?? 0]);
            fputcsv($file, ['Maintenance Requests', $overview['statistics']['total_maintenance_requests'] ?? 0]);
            fputcsv($file, []);

            // Lease Expirations
            if (!empty($groupedEvents['lease_expirations'])) {
                fputcsv($file, ['Lease Expirations:']);
                fputcsv($file, ['Time', 'Title', 'Description']);
                foreach ($groupedEvents['lease_expirations'] as $event) {
                    fputcsv($file, [
                        $event->start_time ?? 'All Day',
                        $event->title,
                        $event->description
                    ]);
                }
                fputcsv($file, []);
            }

            // Maintenance Requests
            if (!empty($groupedEvents['maintenance_requests'])) {
                fputcsv($file, ['Maintenance Requests:']);
                fputcsv($file, ['Time', 'Title', 'Description']);
                foreach ($groupedEvents['maintenance_requests'] as $event) {
                    fputcsv($file, [
                        $event->start_time ?? 'All Day',
                        $event->title,
                        $event->description
                    ]);
                }
                fputcsv($file, []);
            }

            // Clock Events
            if (!empty($groupedEvents['clock_events'])) {
                fputcsv($file, ['Clock Events:']);
                fputcsv($file, ['Time', 'Title', 'Description']);
                foreach ($groupedEvents['clock_events'] as $event) {
                    fputcsv($file, [
                        $event->start_time ?? 'All Day',
                        $event->title,
                        $event->description
                    ]);
                }
                fputcsv($file, []);
            }

            // Admin Actions
            if (!empty($groupedEvents['admin_actions'])) {
                fputcsv($file, ['Admin Actions:']);
                fputcsv($file, ['Time', 'Title', 'Description']);
                foreach ($groupedEvents['admin_actions'] as $event) {
                    fputcsv($file, [
                        $event->start_time ?? 'All Day',
                        $event->title,
                        $event->description
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get events for specific date (AJAX)
     */
    public function getDailyEvents(Request $request, string $date): JsonResponse
    {
        $selectedDate = Carbon::parse($date);
        $groupedEvents = $this->calendarService->getDailyEventsGrouped($selectedDate);

        $formattedEvents = [];
        foreach ($groupedEvents as $type => $events) {
            $formattedEvents[$type] = $events->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_time' => $event->start_time,
                    'event_type' => $event->event_type,
                    'color_code' => $event->color_code ?? $this->calendarService->getEventTypeColor($event->event_type),
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $formattedEvents,
            'statistics' => [
                'total_events' => collect($groupedEvents)->flatten()->count(),
                'by_type' => collect($groupedEvents)->map->count()
            ]
        ]);
    }

    public function weekView(Request $request, ?string $date = null): View
    {
        $selectedDate = $date ? Carbon::parse($date) : Carbon::today();

        return view('calendar.week-view', compact('selectedDate'));
    }

    /**
     * Month view (month route)
     */
    public function monthView(Request $request): View
    {
        return $this->index($request);
    }

    /**
     * Get filters for calendar (filters route)
     */
    public function getFilters(Request $request): JsonResponse
    {
        return response()->json([
            'event_types' => [
                'expiration' => 'Lease Expirations',
                'maintenance_request' => 'Maintenance Requests',
                'clock_event' => 'Clock Events',
                'admin_action' => 'Admin Actions',
                'reminder' => 'Reminders'
            ]
        ]);
    }
}
