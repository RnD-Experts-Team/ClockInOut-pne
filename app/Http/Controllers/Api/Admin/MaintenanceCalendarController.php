<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\GetMaintenanceEventsRequest;
use App\Http\Requests\Api\Admin\GetMaintenanceStatisticsRequest;
use App\Http\Requests\Api\Admin\MaintenanceIndexRequest;
use App\Http\Requests\Api\Admin\RescheduleMaintenanceRequest;
use App\Http\Requests\Api\Admin\ScheduleMaintenanceRequest;
use App\Models\CalendarEvent;
use App\Services\Api\Admin\MaintenanceCalendarService;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Log;

class MaintenanceCalendarController extends Controller
{
    public function __construct(
        private MaintenanceCalendarService $service
    ) {}

    public function index(MaintenanceIndexRequest $request): JsonResponse
    {
        try {
            $result = $this->service->index($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('Maintenance API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch maintenance data.',
                'data' => [
                    'current_date' => now()->toDateString(),
                    'maintenance_events' => [],
                    'upcoming_maintenance' => [],
                    'overdue_maintenance' => [],
                    'statistics' => [
                        'total_this_month' => 0,
                        'upcoming_week' => 0,
                        'overdue' => 0,
                        'completed_this_month' => 0,
                    ]
                ]
            ], 500);
        }
    }
    public function getMaintenanceEvents(GetMaintenanceEventsRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getMaintenanceEvents($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('Maintenance Events API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch maintenance events.',
                'events' => []
            ], 500);
        }
    }
    public function getStatistics(GetMaintenanceStatisticsRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getStatistics($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('Maintenance Statistics API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch maintenance statistics.',
                'statistics' => []
            ], 500);
        }
    }
    public function scheduleMaintenance(ScheduleMaintenanceRequest $request): JsonResponse
    {
        try {
            $result = $this->service->scheduleMaintenance($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            Log::error('Schedule Maintenance Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule maintenance.',
            ], 500);
        }
    }
    public function reschedule(RescheduleMaintenanceRequest $request,CalendarEvent $event): JsonResponse {
        try {
            $result = $this->service->reschedule($event, $request->validated());

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'event' => $result['event'] ?? null,
            ], $result['status']);

        } catch (Throwable $e) {
            Log::error('Reschedule Maintenance Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reschedule maintenance.',
            ], 500);
        }
    }
}