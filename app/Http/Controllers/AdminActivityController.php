<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Models\AdminActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Admin activity overview page
     */
    public function index(Request $request): View
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->subDays(7);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today();

        $activities = AdminActivityLog::byDateRange($startDate, $endDate)
            ->with('adminUser')
            ->orderBy('performed_at', 'desc')
            ->paginate(50);

        $statistics = [
            'total_activities' => AdminActivityLog::byDateRange($startDate, $endDate)->count(),
            'unique_admins' => AdminActivityLog::byDateRange($startDate, $endDate)->distinct('admin_user_id')->count(),
            'activities_by_type' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->pluck('count', 'action_type'),
            'activities_by_day' => AdminActivityLog::byDateRange($startDate, $endDate)
                ->selectRaw('DATE(performed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date'),
        ];

        return view('admin-activity.index', compact('activities', 'statistics', 'startDate', 'endDate'));
    }

    /**
     * Get daily admin activity (AJAX)
     */
    public function getDailyActivity(Request $request, string $date): JsonResponse
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

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Show detailed activity log
     */
    public function show(AdminActivityLog $activity): View
    {
        $activity->load('adminUser');
        $changes = $activity->getModelChanges();

        return view('admin-activity.show', compact('activity', 'changes'));
    }

    /**
     * Get activity statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today();

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

        return response()->json($stats);
    }

    /**
     * Filter activities by criteria
     */
    public function filter(Request $request): JsonResponse
    {
        $query = AdminActivityLog::with('adminUser')->orderBy('performed_at', 'desc');

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
            $startDate = Carbon::parse($request->start_date);
            $endDate = $request->has('end_date') && $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::today();

            $query->byDateRange($startDate, $endDate);
        }

        $activities = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
}
