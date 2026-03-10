<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClockInRequest;
use App\Http\Requests\Api\ClockOutRequest;
use App\Services\Api\ClockingService;
use Illuminate\Http\Request;

class ClockingController extends Controller
{
    protected $clockingService;

    public function __construct(ClockingService $clockingService)
    {
        $this->clockingService = $clockingService;
    }
    //for user:
    public function index()
    {
        $data = $this->clockingService->getClockingData();

        return response()->json([
            'status' => true,
            'message' => 'Clocking data fetched successfully',
            'data' => $data
        ]);
    }
      public function clockIn(ClockInRequest $request)
    {
        $clocking = $this->clockingService->clockIn($request);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح',
            'data' => $clocking
        ], 201);
    }



    public function clockOut(ClockOutRequest $request)
    {
        $result = $this->clockingService->clockOut($request);

        if (isset($result['error'])) {

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الإنصراف بنجاح',
            'data' => $result
        ]);
    }
     
}