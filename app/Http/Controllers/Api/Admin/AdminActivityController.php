<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Services\Api\Admin\AdminActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AdminActivityController extends Controller
{
    protected AdminActivityService $service;

    public function __construct(AdminActivityService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {

            $data = $this->service->getActivities($request);
            $activities = $data['activities'];

            return response()->json([
                'success' => true,
                'data' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ],
                'statistics' => $data['statistics'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date']
            ]);

        } catch (\Exception $e) {

            Log::error('Admin Activity Logs Index Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs'
            ], 500);
        }
    }

    public function show(AdminActivityLog $activity): JsonResponse
    {
        try {

            $data = $this->service->getActivityDetails($activity);

            return response()->json([
                'success' => true,
                'activity' => $data['activity'],
                'changes' => $data['changes']
            ]);

        } catch (\Exception $e) {

            Log::error('Admin Activity Log Show Error', [
                'message' => $e->getMessage(),
                'activity_id' => $activity->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity details'
            ], 500);
        }
    }
    public function getDailyActivity(Request $request, string $date): \Illuminate\Http\JsonResponse
    {
        try {

            $summary = $this->service->getDailyActivity($date);

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {

            Log::error('Daily Activity Error', [
                'message' => $e->getMessage(),
                'date' => $date
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch daily activities'
            ], 500);
        }
    }
    public function getStats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $data = $this->service->getStats($request);

            return response()->json([
                'success' => true,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'data' => $data['stats']
            ]);

        } catch (\Exception $e) {

            Log::error('Activity Stats Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity statistics'
            ], 500);
        }
    }   
    public function filter(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $activities = $this->service->filter($request);

            return response()->json([
                'success' => true,
                'data' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ]
            ]);

        } catch (\Exception $e) {

            Log::error('Admin Activity Filter Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to filter activities'
            ], 500);
        }
    }
}