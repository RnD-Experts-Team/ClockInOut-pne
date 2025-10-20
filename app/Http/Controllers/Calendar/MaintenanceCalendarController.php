<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MaintenanceCalendarController extends Controller
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Maintenance calendar view
     */
    public function index(Request $request): View
    {
        try {
            $currentDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();

            // Get maintenance-related events for the current month
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();

            // Replace scope methods with basic queries
            $maintenanceEvents = CalendarEvent::whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->where('event_type', 'maintenance_request')
                ->with('creator')
                ->orderBy('start_date')
                ->get();

            // Get upcoming maintenance (next 7 days)
            $upcomingMaintenance = CalendarEvent::where('start_date', '>=', Carbon::today())
                ->where('start_date', '<=', Carbon::today()->addDays(7))
                ->where('event_type', 'maintenance_request')
                ->with('creator')
                ->orderBy('start_date')
                ->get();

            // Get overdue maintenance
            $overdueMaintenance = CalendarEvent::where('start_date', '<', Carbon::today())
                ->where('event_type', 'maintenance_request')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with('creator')
                ->orderBy('start_date', 'desc')
                ->limit(5)
                ->get();

            // Get maintenance statistics
            $statistics = [
                'total_this_month' => $maintenanceEvents->count(),
                'upcoming_week' => $upcomingMaintenance->count(),
                'overdue' => $overdueMaintenance->count(),
                'completed_this_month' => $maintenanceEvents->where('status', 'completed')->count(),
            ];

            return view('calendar.maintenance.index', compact(
                'currentDate',
                'maintenanceEvents',
                'upcomingMaintenance',
                'overdueMaintenance',
                'statistics'
            ));

        } catch (\Exception $e) {
            \Log::error('Maintenance Calendar Error: ' . $e->getMessage());

            // Return with empty data to prevent errors
            return view('calendar.maintenance.index', [
                'currentDate' => Carbon::today(),
                'maintenanceEvents' => collect(),
                'upcomingMaintenance' => collect(),
                'overdueMaintenance' => collect(),
                'statistics' => [
                    'total_this_month' => 0,
                    'upcoming_week' => 0,
                    'overdue' => 0,
                    'completed_this_month' => 0,
                ]
            ]);
        }
    }
    /**
     * Get maintenance events for calendar (AJAX)
     */
    public function getMaintenanceEvents(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));

        $filters = [
            'event_type' => 'maintenance_request',
            'priority' => $request->get('priority'),
            'status' => $request->get('status'),
        ];

        $events = $this->calendarService->getFormattedEvents($start, $end, $filters);

        // Add maintenance-specific data
        $events = collect($events)->map(function ($event) {
            if ($event['extendedProps']['event_type'] === 'maintenance_request') {
                // Add maintenance-specific styling and data
                $event['classNames'] = ['maintenance-event'];

                // Color code by priority if available
                if (isset($event['extendedProps']['priority'])) {
                    $event['backgroundColor'] = $this->getPriorityColor($event['extendedProps']['priority']);
                    $event['borderColor'] = $event['backgroundColor'];
                }
            }
            return $event;
        })->toArray();

        return response()->json($events);
    }

    /**
     * Schedule maintenance for specific date
     */
    public function scheduleMaintenance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:15|max:480', // 15 minutes to 8 hours
            'priority' => 'required|in:low,normal,high,urgent',
            'asset_id' => 'nullable|integer',
            'assigned_user_id' => 'nullable|exists:users,id',
            'maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
        ]);

        // Create calendar event for scheduled maintenance
        $calendarEvent = CalendarEvent::create([
            'title' => 'Maintenance: ' . $validated['title'],
            'description' => $this->buildMaintenanceDescription($validated),
            'event_type' => 'maintenance_request',
            'start_date' => $validated['scheduled_date'],
            'start_time' => $validated['scheduled_time'] ?? '09:00',
            'end_time' => $this->calculateEndTime($validated['scheduled_time'] ?? '09:00', $validated['estimated_duration'] ?? 60),
            'is_all_day' => false,
            'color_code' => $this->getPriorityColor($validated['priority']),
            'related_model_type' => $validated['maintenance_request_id'] ? 'App\Models\MaintenanceRequest' : null,
            'related_model_id' => $validated['maintenance_request_id'],
            'created_by' => auth()->id(),
        ]);

        // Update maintenance request if provided
        if ($validated['maintenance_request_id']) {
            // Assuming you have MaintenanceRequest model
            if (class_exists('App\Models\MaintenanceRequest')) {
                $maintenanceRequest = \App\Models\MaintenanceRequest::find($validated['maintenance_request_id']);
                if ($maintenanceRequest) {
                    $maintenanceRequest->update([
                        'scheduled_start_date' => $validated['scheduled_date'],
                        'estimated_duration' => $validated['estimated_duration'] ?? 60,
                        'assigned_user_id' => $validated['assigned_user_id'],
                        'status' => 'scheduled',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance scheduled successfully',
            'event' => $calendarEvent->formatForCalendar()
        ]);
    }

    /**
     * Reschedule maintenance to different date
     */
    public function reschedule(Request $request, CalendarEvent $event): JsonResponse
    {
        if ($event->event_type !== 'maintenance_request') {
            return response()->json([
                'success' => false,
                'message' => 'Only maintenance events can be rescheduled'
            ], 422);
        }

        $validated = $request->validate([
            'new_date' => 'required|date|after_or_equal:today',
            'new_time' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldDate = $event->start_date->format('Y-m-d');
        $oldTime = $event->start_time;

        $event->update([
            'start_date' => $validated['new_date'],
            'start_time' => $validated['new_time'] ?? $event->start_time,
            'description' => $event->description . "\n\nRescheduled from {$oldDate} " . ($oldTime ? $oldTime : '') .
                ($validated['reason'] ? "\nReason: " . $validated['reason'] : ''),
        ]);

        // Update related maintenance request if exists
        if ($event->related_model_type === 'App\Models\MaintenanceRequest' && $event->related_model_id) {
            if (class_exists('App\Models\MaintenanceRequest')) {
                $maintenanceRequest = \App\Models\MaintenanceRequest::find($event->related_model_id);
                if ($maintenanceRequest) {
                    $maintenanceRequest->update([
                        'scheduled_start_date' => $validated['new_date'],
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance rescheduled successfully',
            'event' => $event->formatForCalendar()
        ]);
    }

    /**
     * Get maintenance statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today()->endOfMonth();

        $maintenanceEvents = CalendarEvent::byDateRange($startDate, $endDate)
            ->byType('maintenance_request')
            ->get();

        $statistics = [
            'total_scheduled' => $maintenanceEvents->count(),
            'completed' => $maintenanceEvents->where('end_date', '<=', Carbon::today())->count(),
            'upcoming' => $maintenanceEvents->where('start_date', '>', Carbon::today())->count(),
            'overdue' => $maintenanceEvents->where('start_date', '<', Carbon::today())
                ->whereNull('end_date')->count(),
            'by_priority' => $maintenanceEvents->groupBy(function($event) {
                // Extract priority from description or use default
                return $this->extractPriorityFromEvent($event);
            })->map->count(),
            'by_week' => $maintenanceEvents->groupBy(function($event) {
                return $event->start_date->format('W');
            })->map->count(),
        ];

        return response()->json($statistics);
    }

    /**
     * Build maintenance description
     */
    private function buildMaintenanceDescription(array $data): string
    {
        $description = $data['description'] ?? '';
        $description .= "\n\nPriority: " . ucfirst($data['priority']);

        if ($data['estimated_duration']) {
            $description .= "\nEstimated Duration: " . $data['estimated_duration'] . " minutes";
        }

        if ($data['assigned_user_id']) {
            $user = \App\Models\User::find($data['assigned_user_id']);
            if ($user) {
                $description .= "\nAssigned to: " . $user->name;
            }
        }

        if ($data['asset_id']) {
            $description .= "\nAsset ID: " . $data['asset_id'];
        }

        return trim($description);
    }

    /**
     * Get priority color
     */
    private function getPriorityColor(string $priority): string
    {
        return match($priority) {
            'urgent' => '#dc3545',  // red
            'high' => '#fd7e14',    // orange
            'normal' => '#ffc107',  // yellow
            'low' => '#28a745',     // green
            default => '#6c757d',   // gray
        };
    }

    /**
     * Calculate end time based on start time and duration
     */
    private function calculateEndTime(string $startTime, int $durationMinutes): string
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = $start->addMinutes($durationMinutes);
        return $end->format('H:i');
    }

    /**
     * Extract priority from event description
     */
    private function extractPriorityFromEvent(CalendarEvent $event): string
    {
        if (preg_match('/Priority:\s*(\w+)/i', $event->description, $matches)) {
            return strtolower($matches[1]);
        }
        return 'normal';
    }


}
