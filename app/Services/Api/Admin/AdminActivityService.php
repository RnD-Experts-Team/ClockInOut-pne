<?php

namespace App\Services\Api\Admin;

use App\Models\AdminActivityLog;
use Carbon\Carbon;

class AdminActivityService
{
    public function getActivities($request): array
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : Carbon::today()->subDays(7);

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : Carbon::today();

        $activities = AdminActivityLog::byDateRange($startDate, $endDate)
            ->with('adminUser')
            ->orderBy('performed_at', 'desc')
            ->paginate(50);

        $statistics = [
            'total_activities' => AdminActivityLog::byDateRange($startDate, $endDate)->count(),

            'unique_admins' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->distinct('admin_user_id')
                ->count(),

            'activities_by_type' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->pluck('count', 'action_type'),

            'activities_by_day' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->selectRaw('DATE(performed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date'),
        ];

        return [
            'activities' => $activities,
            'statistics' => $statistics,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }

    public function getActivityDetails($activity): array
    {
        $activity->load('adminUser');

        $changes = $activity->getModelChanges();

        return [
            'activity' => $activity,
            'changes' => $changes
        ];
    }
    public function getDailyActivity(string $date): array
    {
        $selectedDate = Carbon::parse($date);

        $activities = AdminActivityLog::byDate($selectedDate)
            ->with('adminUser')
            ->orderBy('performed_at', 'desc')
            ->get();

        $groupedActivities = $activities->groupBy('action_type');

        $summary = [
            'date' => $selectedDate->format('Y-m-d'),
            'total_activities' => $activities->count(),
            'activities_by_type' => $groupedActivities->map->count(),
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'admin_name' => $activity->adminUser ? $activity->adminUser->name : 'Unknown',
                    'action_type' => $activity->action_type,
                    'model_type' => class_basename($activity->model_type),
                    'model_id' => $activity->model_id,
                    'description' => $activity->getHumanReadableDescription(),
                    'performed_at' => $activity->performed_at->format('H:i:s'),
                    'color_code' => $activity->color_code,
                ];
            })
        ];

        return $summary;
    }
    public function getStats($request): array
    {
        $startDate = $request->get('start_date')
            ? \Carbon\Carbon::parse($request->get('start_date'))
            : \Carbon\Carbon::today()->subDays(30);

        $endDate = $request->get('end_date')
            ? \Carbon\Carbon::parse($request->get('end_date'))
            : \Carbon\Carbon::today();

        $stats = [
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

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => $stats
        ];
    }
    public function filter($request)
    {
        $query = \App\Models\AdminActivityLog::with('adminUser')
            ->orderBy('performed_at', 'desc');

        if ($request->has('admin_id') && $request->admin_id) {
            $query->byAdmin($request->admin_id);
        }

        if ($request->has('action_type') && $request->action_type) {
            $query->byAction($request->action_type);
        }

        if ($request->has('model_type') && $request->model_type) {
            $query->byModel($request->model_type);
        }

        if ($request->has('start_date') && $request->start_date) {

            $startDate = \Carbon\Carbon::parse($request->start_date);

            $endDate = $request->has('end_date') && $request->end_date
                ? \Carbon\Carbon::parse($request->end_date)
                : \Carbon\Carbon::today();

            $query->byDateRange($startDate, $endDate);
        }

        return $query->paginate(50);
    }
}