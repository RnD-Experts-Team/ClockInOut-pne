<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\DailyClockEvent;
use App\Models\MaintenanceRequest;
use App\Models\CalendarEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Log admin action with automatic event creation
     */
    public function logAdminAction(
        string $action,
        string $modelType,
        int $modelId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $fieldName = null
    ): AdminActivityLog {
        $log = AdminActivityLog::create([
            'admin_user_id' => Auth::id(),
            'action_type' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'field_name' => $fieldName,
            'old_value' => $oldValues,
            'new_value' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'performed_at' => Carbon::now(),
            'description' => $this->generateDescription($action, $modelType, $modelId, $fieldName),
        ]);

        // Create calendar event for this activity
        $this->createCalendarEventFromLog($log);

        return $log;
    }

    /**
     * Log clock in/out events
     */
    public function logClockEvent(
        int $userId,
        string $eventType,
        ?string $location = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null
    ): DailyClockEvent {
        $clockEvent = DailyClockEvent::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'event_timestamp' => Carbon::now(),
            'location' => $location,
            'ip_address' => Request::ip(),
            'notes' => $notes,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        // Create calendar event
        $clockEvent->createCalendarEvent();

        return $clockEvent;
    }

    /**
     * Get activity overview for specific date
     */
    public function getActivityByDate(Carbon $date): array
    {
        return [
            'admin_activities' => AdminActivityLog::byDate($date)
                ->with('adminUser')
                ->orderBy('performed_at', 'desc')
                ->get(),
            'clock_events' => DailyClockEvent::byDate($date)
                ->with('user')
                ->orderBy('event_timestamp', 'asc')
                ->get(),
            'maintenance_requests' => $this->getMaintenanceRequestsByDate($date),
            'calendar_events' => CalendarEvent::whereDate('start_date', $date)
                ->with('creator')
                ->orderBy('start_time', 'asc')
                ->get(),
        ];
    }

    /**
     * Get comprehensive daily overview
     */
    public function getDailyOverview(Carbon $date): array
    {
        $activities = $this->getActivityByDate($date);

        return [
            'date' => $date->format('Y-m-d'),
            'date_formatted' => $date->format('F j, Y'),
            'day_name' => $date->format('l'),
            'activities' => $activities,
            'statistics' => [
                'total_admin_actions' => count($activities['admin_activities']),
                'total_clock_events' => count($activities['clock_events']),
                'total_maintenance_requests' => count($activities['maintenance_requests']),
                'total_calendar_events' => count($activities['calendar_events']),
                'unique_active_users' => $this->getUniqueActiveUsers($date),
                'work_hours_summary' => $this->getWorkHoursSummary($date),
            ],
            'summary' => $this->generateDailySummary($activities),
        ];
    }

    /**
     * Generate activity description
     */
    private function generateDescription(string $action, string $modelType, int $modelId, ?string $fieldName): string
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        $modelName = class_basename($modelType);

        $description = match ($action) {
            'created' => "{$userName} created new {$modelName} (ID: {$modelId})",
            'updated' => "{$userName} updated {$modelName} (ID: {$modelId})",
            'deleted' => "{$userName} deleted {$modelName} (ID: {$modelId})",
            'viewed' => "{$userName} viewed {$modelName} (ID: {$modelId})",
            default => "{$userName} performed {$action} on {$modelName} (ID: {$modelId})",
        };

        if ($fieldName) {
            $description .= " - Modified field: {$fieldName}";
        }

        return $description;
    }

    /**
     * Create calendar event from activity log
     */
    private function createCalendarEventFromLog(AdminActivityLog $log): CalendarEvent
    {
        return CalendarEvent::create([
            'title' => 'Admin Action: ' . $log->action_type,
            'description' => $log->getHumanReadableDescription(),
            'event_type' => 'admin_action',
            'start_date' => $log->performed_at->toDateString(),
            'start_time' => $log->performed_at->format('H:i:s'),
            'is_all_day' => false,
            'color_code' => $log->color_code,
            'related_model_type' => get_class($log),
            'related_model_id' => $log->id,
            'created_by' => $log->admin_user_id,
        ]);
    }

    /**
     * Get maintenance requests for specific date
     */
    private function getMaintenanceRequestsByDate(Carbon $date): array
    {
        $maintenanceRequests = [];

        // Assuming you have MaintenanceRequest model from your existing code
        if (class_exists('App\Models\MaintenanceRequest')) {
            $maintenanceRequests = MaintenanceRequest::whereDate('created_at', $date)
                ->with('user', 'assignedUser')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }

        return $maintenanceRequests;
    }

    /**
     * Get unique active users for the day
     */
    private function getUniqueActiveUsers(Carbon $date): int
    {
        $adminUsers = AdminActivityLog::byDate($date)->distinct('admin_user_id')->count('admin_user_id');
        $clockUsers = DailyClockEvent::byDate($date)->distinct('user_id')->count('user_id');

        return max($adminUsers, $clockUsers);
    }

    /**
     * Get work hours summary for the day
     */
    private function getWorkHoursSummary(Carbon $date): array
    {
        $clockEvents = DailyClockEvent::byDate($date)->get()->groupBy('user_id');
        $summary = [];

        foreach ($clockEvents as $userId => $events) {
            $workHours = DailyClockEvent::calculateWorkHours($userId, $date);

            if ($workHours > 0) {
                $user = User::find($userId);
                $summary[] = [
                    'user_id' => $userId,
                    'username' => $user ? $user->name : 'Unknown User',
                    'work_hours' => $workHours,
                ];
            }
        }

        return $summary;
    }

    /**
     * Generate daily activity summary
     */
    private function generateDailySummary(array $activities): string
    {
        $adminCount = count($activities['admin_activities']);
        $clockCount = count($activities['clock_events']);
        $maintenanceCount = count($activities['maintenance_requests']);

        $summary = "Daily Activity Summary:\n";
        $summary .= "• {$adminCount} admin actions\n";
        $summary .= "• {$clockCount} clock events\n";
        $summary .= "• {$maintenanceCount} maintenance requests";

        return $summary;
    }

    /**
     * Get activity statistics for date range
     */
    public function getActivityStatistics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_activities' => AdminActivityLog::byDateRange($startDate, $endDate)->count(),
            'activities_by_admin' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->join('users', 'admin_activity_logs.admin_user_id', '=', 'users.id')
                ->selectRaw('users.name, COUNT(*) as count')
                ->groupBy('users.name')
                ->pluck('count', 'name'),
            'activities_by_model' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->selectRaw('model_type, COUNT(*) as count')
                ->groupBy('model_type')
                ->pluck('count', 'model_type')
                ->mapWithKeys(function ($count, $modelType) {
                    return [class_basename($modelType) => $count];
                }),
            'hourly_distribution' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->selectRaw('HOUR(performed_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->pluck('count', 'hour'),
        ];
    }

    /**
     * Get filtered activities
     */
    public function getFilteredActivities(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = AdminActivityLog::with('adminUser')->orderBy('performed_at', 'desc');

        if (!empty($filters['admin_id'])) {
            $query->byAdmin($filters['admin_id']);
        }

        if (!empty($filters['action_type'])) {
            $query->byAction($filters['action_type']);
        }

        if (!empty($filters['model_type'])) {
            $query->byModel($filters['model_type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date']);
            $endDate = Carbon::parse($filters['end_date']);
            $query->byDateRange($startDate, $endDate);
        }

        return $query->paginate(50);
    }

    /**
     * Export activities to array for Excel/CSV
     */
    public function exportActivities(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = AdminActivityLog::with('adminUser')
            ->byDateRange($startDate, $endDate)
            ->orderBy('performed_at', 'desc');

        if (!empty($filters['admin_id'])) {
            $query->byAdmin($filters['admin_id']);
        }

        if (!empty($filters['action_type'])) {
            $query->byAction($filters['action_type']);
        }

        if (!empty($filters['model_type'])) {
            $query->byModel($filters['model_type']);
        }

        return $query->get()->map(function ($activity) {
            return [
                'Date' => $activity->performed_at->format('Y-m-d'),
                'Time' => $activity->performed_at->format('H:i:s'),
                'Admin' => $activity->adminUser->name ?? 'Unknown',
                'Action' => ucfirst($activity->action_type),
                'Model' => class_basename($activity->model_type),
                'Model ID' => $activity->model_id,
                'Description' => $activity->getHumanReadableDescription(),
                'IP Address' => $activity->ip_address,
            ];
        })->toArray();
    }

    /**
     * Clean old activity logs
     */
    public function cleanOldLogs(int $olderThanDays = 90): int
    {
        return AdminActivityLog::where('performed_at', '<', Carbon::now()->subDays($olderThanDays))
            ->delete();
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecentActivity(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return AdminActivityLog::with('adminUser')
            ->orderBy('performed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity count by date for chart
     */
    public function getActivityCountByDate(Carbon $startDate, Carbon $endDate): array
    {
        return AdminActivityLog::byDateRange($startDate, $endDate)
            ->selectRaw('DATE(performed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }
}
