<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\GetClockEventsRequest;
use App\Http\Requests\Api\Admin\GetWorkHoursSummaryRequest;
use App\Http\Requests\Api\Admin\StoreClockEventRequest;
use App\Http\Requests\Api\Admin\UpdateClockEventRequest;
use App\Models\DailyClockEvent;
use App\Services\Api\Admin\ClockEventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class ClockEventController extends Controller
{
    protected $service;

    public function __construct(ClockEventService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getClockEventsData($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Clock events fetched successfully',
            'data' => $data
        ]);
    }
    public function store(StoreClockEventRequest $request): JsonResponse
    {
        $result = $this->service->store($request->validated());

        return response()->json($result['data'], $result['status']);
    }
    public function show(DailyClockEvent $clockEvent): JsonResponse
    {
        $result = $this->service->show($clockEvent);

        return response()->json($result);
    }
    public function update(UpdateClockEventRequest $request, DailyClockEvent $clockEvent): JsonResponse
    {
        try {
            $result = $this->service->update($clockEvent, $request->validated());

            return response()->json($result, 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update clock event.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy(DailyClockEvent $clockEvent): JsonResponse
    {
        try {
            $result = $this->service->destroy($clockEvent);

            return response()->json($result, 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete clock event.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getEvents(GetClockEventsRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getEvents($request->validated());

            return response()->json($result, 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch events.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getWorkHoursSummary(GetWorkHoursSummaryRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getWorkHoursSummary($request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch work hours summary.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
    
