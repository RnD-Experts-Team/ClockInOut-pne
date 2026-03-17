<?php

namespace App\Services\Api\Admin;

use App\Models\CalendarEvent;
use App\Models\User;
use Carbon\Carbon;

class MaintenanceCalendarService
{
      public function __construct(
        private CalendarService $calendarService
    ) {}
    public function index(array $validated): array
    {
        $currentDate = isset($validated['date'])
            ? Carbon::parse($validated['date'])
            : Carbon::today();

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // maintenance events
        $maintenanceEvents = CalendarEvent::whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->where('event_type', 'maintenance_request')
            ->with('creator')
            ->orderBy('start_date')
            ->get();

        // upcoming
        $upcomingMaintenance = CalendarEvent::where('start_date', '>=', Carbon::today())
            ->where('start_date', '<=', Carbon::today()->addDays(7))
            ->where('event_type', 'maintenance_request')
            ->with('creator')
            ->orderBy('start_date')
            ->get();

        // overdue
        $overdueMaintenance = CalendarEvent::where('start_date', '<', Carbon::today())
            ->where('event_type', 'maintenance_request')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('creator')
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get();

        // statistics
        $statistics = [
            'total_this_month' => $maintenanceEvents->count(),
            'upcoming_week' => $upcomingMaintenance->count(),
            'overdue' => $overdueMaintenance->count(),
            'completed_this_month' => $maintenanceEvents->where('status', 'completed')->count(),
        ];

        return [
            'success' => true,
            'data' => [
                'current_date' => $currentDate->toDateString(),
                'maintenance_events' => $maintenanceEvents,
                'upcoming_maintenance' => $upcomingMaintenance,
                'overdue_maintenance' => $overdueMaintenance,
                'statistics' => $statistics,
            ]
        ];
    }
    public function getMaintenanceEvents(array $validated): array
    {
        $start = Carbon::parse($validated['start']);
        $end = Carbon::parse($validated['end']);

        $filters = [
            'event_type' => 'maintenance_request',
            'priority' => $validated['priority'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $events = $this->calendarService->getFormattedEvents($start, $end, $filters);

        $events = collect($events)->map(function ($event) {

            if (($event['extendedProps']['event_type'] ?? null) === 'maintenance_request') {

                $event['classNames'] = ['maintenance-event'];

                if (isset($event['extendedProps']['priority'])) {
                    $color = $this->getPriorityColor($event['extendedProps']['priority']);
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
    public function getStatistics(array $validated): array
    {
        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : Carbon::today()->startOfMonth();

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])
            : Carbon::today()->endOfMonth();

        $maintenanceEvents = CalendarEvent::byDateRange($startDate, $endDate)
            ->byType('maintenance_request')
            ->get();

        $statistics = [
            'total_scheduled' => $maintenanceEvents->count(),

            'completed' => $maintenanceEvents
                ->where('end_date', '<=', Carbon::today())
                ->count(),

            'upcoming' => $maintenanceEvents
                ->where('start_date', '>', Carbon::today())
                ->count(),

            'overdue' => $maintenanceEvents
                ->where('start_date', '<', Carbon::today())
                ->whereNull('end_date')
                ->count(),

            'by_priority' => $maintenanceEvents
                ->groupBy(function ($event) {
                    return $this->extractPriorityFromEvent($event);
                })
                ->map->count(),

            'by_week' => $maintenanceEvents
                ->groupBy(function ($event) {
                    return $event->start_date->format('W');
                })
                ->map->count(),
        ];

        return [
            'success' => true,
            'statistics' => $statistics
        ];
    }

    private function extractPriorityFromEvent(CalendarEvent $event): string
    {
        if (preg_match('/Priority:\s*(\w+)/i', $event->description, $matches)) {
            return strtolower($matches[1]);
        }

        return 'normal';
    }
    

    private function getPriorityColor(string $priority): string
    {
        return match($priority) {
            'urgent' => '#dc3545',
            'high' => '#fd7e14',
            'normal' => '#ffc107',
            'low' => '#28a745',
            default => '#6c757d',
        };
    }
    public function scheduleMaintenance(array $validated): array
    {
        $calendarEvent = CalendarEvent::create([
            'title' => 'Maintenance: ' . $validated['title'],
            'description' => $this->buildMaintenanceDescription($validated),
            'event_type' => 'maintenance_request',
            'start_date' => $validated['scheduled_date'],
            'start_time' => $validated['scheduled_time'] ?? '09:00',
            'end_time' => $this->calculateEndTime(
                $validated['scheduled_time'] ?? '09:00',
                $validated['estimated_duration'] ?? 60
            ),
            'is_all_day' => false,
            'color_code' => $this->getPriorityColor($validated['priority']),
            'related_model_type' => $validated['maintenance_request_id']
                ? 'App\Models\MaintenanceRequest'
                : null,
            'related_model_id' => $validated['maintenance_request_id'],
            'created_by' => auth()->id(),
        ]);

        // update maintenance request
        if (!empty($validated['maintenance_request_id'])) {

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

        return [
            'success' => true,
            'message' => 'Maintenance scheduled successfully',
            'event' => $calendarEvent->formatForCalendar()
        ];
    }
 
    private function buildMaintenanceDescription(array $data): string
    {
        $description = $data['description'] ?? '';
        $description .= "\n\nPriority: " . ucfirst($data['priority']);

        if (!empty($data['estimated_duration'])) {
            $description .= "\nEstimated Duration: " . $data['estimated_duration'] . " minutes";
        }

        if (!empty($data['assigned_user_id'])) {
            $user = User::find($data['assigned_user_id']);
            if ($user) {
                $description .= "\nAssigned to: " . $user->name;
            }
        }

        if (!empty($data['asset_id'])) {
            $description .= "\nAsset ID: " . $data['asset_id'];
        }

        return trim($description);
    }

    private function calculateEndTime(string $startTime, int $durationMinutes): string
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = $start->addMinutes($durationMinutes);

        return $end->format('H:i');
    }
    public function reschedule(CalendarEvent $event, array $validated): array
    {
         if ($event->event_type !== 'maintenance_request') {
            return [
                'success' => false,
                'message' => 'Only maintenance events can be rescheduled',
                'status' => 422
            ];
        }

        $oldDate = $event->start_date->format('Y-m-d');
        $oldTime = $event->start_time;

        $event->update([
            'start_date' => $validated['new_date'],
            'start_time' => $validated['new_time'] ?? $event->start_time,
            'description' => $event->description . "\n\nRescheduled from {$oldDate} " .
                ($oldTime ? $oldTime : '') .
                (!empty($validated['reason']) ? "\nReason: " . $validated['reason'] : ''),
        ]);

        // update related maintenance request
        if (
            $event->related_model_type === 'App\Models\MaintenanceRequest' &&
            $event->related_model_id
        ) {
            if (class_exists('App\Models\MaintenanceRequest')) {

                $maintenanceRequest = \App\Models\MaintenanceRequest::find($event->related_model_id);

                if ($maintenanceRequest) {
                    $maintenanceRequest->update([
                        'scheduled_start_date' => $validated['new_date'],
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Maintenance rescheduled successfully',
            'event' => $event->formatForCalendar(),
            'status' => 200
        ];
    }

   
}