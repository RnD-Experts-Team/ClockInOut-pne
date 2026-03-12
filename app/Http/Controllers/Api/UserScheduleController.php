<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UserScheduleService;

class UserScheduleController extends Controller
{
    public function __construct(
        private UserScheduleService $scheduleService
    ) {}

    public function index(Request $request)
    {
        try {

            $data = $this->scheduleService->index($request);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to load schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}