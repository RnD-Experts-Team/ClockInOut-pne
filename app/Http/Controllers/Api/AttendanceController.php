<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    //for user: fetch attendance data
    public function index(Request $request)
    {
        try {
            $data = $this->attendanceService->getAttendance($request);

            return response()->json([
                'success' => true,
                'message' => 'Attendance fetched successfully.',
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Attendance API error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching attendance.',
            ], 500);
        }
    }
}