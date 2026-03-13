<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Api\Admin\ScorecardService;
use Illuminate\Support\Facades\Log;

class ScorecardController extends Controller
{
    protected ScorecardService $scorecardService;

    public function __construct(ScorecardService $scorecardService)
    {
        $this->scorecardService = $scorecardService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $scorecards = $this->scorecardService->getScorecards($request);
            $allUsers = $this->scorecardService->getAllUsers();

            return response()->json([
                'success' => true,
                'message' => 'Scorecards fetched successfully',
                'data' => [
                    'scorecards' => $scorecards,
                    'all_users' => $allUsers,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching scorecards', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch scorecards',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => [
                    'scorecards' => [],
                    'all_users' => [],
                ],
            ], 500);
        }
    }

    public function export(Request $request): JsonResponse
    {
        try {
            $scorecards = $this->scorecardService->getScorecards($request);

            return response()->json([
                'success' => true,
                'message' => 'Scorecards export data fetched successfully',
                'data' => [
                    'scorecards' => $scorecards,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error exporting scorecards', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export scorecards',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => [
                    'scorecards' => [],
                ],
            ], 500);
        }
    }
}