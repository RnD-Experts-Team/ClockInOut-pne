<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateGasRateRequest;
use App\Http\Requests\Api\ClockInRequest;
use App\Http\Requests\Api\ClockOutRequest;
use App\Services\Api\ClockingService;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Admin\UpdateClockingRequest;

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
  

    public function updateGasRate(UpdateGasRateRequest $request)
    {
        try {

            $this->clockingService->updateGasRate($request->gas_payment_rate);

            return response()->json([
                'status' => true,
                'message' => 'Gas payments rate updated successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }


    public function destroy($id)
    {
        try {

            $this->clockingService->deleteClocking($id);

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
    public function ClockingTable(Request $request)
    {
        try {

            $data = $this->clockingService->clockingTable($request);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }

    public function updateClocking(UpdateClockingRequest $request)
    {
        try {

            $clocking = $this->clockingService->updateClocking($request);

            return response()->json([
                'status' => true,
                'message' => 'Clocking record updated successfully.',
                'data' => $clocking
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
     
}