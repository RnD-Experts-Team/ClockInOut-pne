<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\DeleteRoleRequest;
use App\Http\Requests\Api\Admin\DeleteShiftTypeRequest;
use App\Http\Requests\Api\Admin\StoreScheduleRequest;
use App\Http\Requests\Api\Admin\UpdateScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Services\Api\Admin\ScheduleService;
use Illuminate\Support\Facades\Log;
class ScheduleController extends Controller
{
    private $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        try {

            $schedules = $this->scheduleService->getSchedules($request);

            return response()->json([
                'success' => true,
                'data' => $schedules->items(),
                'pagination' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'per_page' => $schedules->perPage(),
                    'total' => $schedules->total()
                ]
            ]);

        } catch (\Exception $e) {

            Log::error('Schedule Index Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Schedule $schedule)
    {
        try {

            $data = $this->scheduleService->getScheduleDetails($schedule);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            Log::error('Schedule Show Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch schedule details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(StoreScheduleRequest $request)
    {
        try {

            $result = $this->scheduleService->createSchedule($request);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['schedule']
            ], 201);

        } catch (\Exception $e) {

            Log::error('Schedule Store Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function destroy(Schedule $schedule)
    {
        try {

            $this->scheduleService->deleteSchedule($schedule);

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully.'
            ]);

        } catch (\Exception $e) {

            Log::error('Schedule Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule'
            ], 500);
        }
    }
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        try {

            $message = $this->scheduleService->updateSchedule($request, $schedule);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function deleteShiftType(DeleteShiftTypeRequest $request)
    {
        Log::info('Delete Shift Type Request', $request->all());

        try {

            $this->scheduleService->deleteShiftType($request->shift_type);

            return response()->json([
                'success' => true,
                'message' => 'Shift type removed successfully'
            ]);

        } catch (\Exception $e) {

            Log::error('Delete Shift Type Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteRole(DeleteRoleRequest $request)
    {
        Log::info('Delete Role Request', $request->all());

        try {

            $this->scheduleService->deleteRole($request->role);

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully'
            ]);

        } catch (\Exception $e) {

            Log::error('Delete Role Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function publish(Schedule $schedule)
    {
        try {

            $schedule = $this->scheduleService->publish($schedule);

            return response()->json([
                'success' => true,
                'message' => 'Schedule published successfully!',
                'data' => $schedule
            ]);

        } catch (\Exception $e) {

            Log::error('Publish Schedule Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function activate(Schedule $schedule)
    {
        try {

            $schedule = $this->scheduleService->activate($schedule);

            return response()->json([
                'success' => true,
                'message' => 'Schedule activated successfully!',
                'data' => $schedule
            ]);

        } catch (\Exception $e) {

            Log::error('Activate Schedule Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
   
}