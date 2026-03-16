<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\RenewExpirationRequest;
use App\Http\Requests\Api\Admin\UpdateExpirationRequest;
use App\Http\Requests\Api\Admin\StoreExpirationRequest;
use App\Http\Requests\Api\Admin\UpdateExpirationWarningSettingsRequest;
use App\Models\ExpirationTracking;
use App\Services\Api\Admin\ExpirationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ExpirationController extends Controller
{
    protected ExpirationService $service;

    public function __construct(ExpirationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {

            $data = $this->service->getExpirations($request);
            $expirations = $data['expirations'];

            return response()->json([
                'success' => true,
                'data' => $expirations->items(),
                'pagination' => [
                    'current_page' => $expirations->currentPage(),
                    'last_page' => $expirations->lastPage(),
                    'per_page' => $expirations->perPage(),
                    'total' => $expirations->total(),
                ],
                'statistics' => [
                    'expiring_soon' => $data['expiring_soon'],
                    'expired' => $data['expired']
                ],
                'filters' => [
                    'expiration_types' => $data['expiration_types'],
                    'selected_type' => $data['filter_type'],
                    'selected_status' => $data['filter_status']
                ]
            ]);

        } catch (\Exception $e) {

            Log::error('Expiration Index Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expirations'
            ], 500);
        }
    }
    public function store(StoreExpirationRequest $request):JsonResponse
    {
        try {

            $result = $this->service->storeExpiration(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expiration']
            ], 201);

        } catch (\Exception $e) {

            Log::error('Expiration Store Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create expiration tracking'
            ], 500);
        }
    }
    public function show(ExpirationTracking $expiration): JsonResponse
    {
        try {

            $data = $this->service->getExpiration($expiration);

            return response()->json([
                'success' => true,
                'data' => $data['expiration'],
                'days_until_expiration' => $data['days_until_expiration']
            ]);

        } catch (\Exception $e) {

            Log::error('Expiration Show Error', [
                'message' => $e->getMessage(),
                'expiration_id' => $expiration->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expiration'
            ], 500);
        }
    }


    public function update(UpdateExpirationRequest $request,ExpirationTracking $expiration):JsonResponse
    {
        try {

            $result = $this->service->updateExpiration(
                $request->validated(),
                $expiration
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['expiration']
            ]);

        } catch (\Exception $e) {

            \Log::error('Expiration Update Error', [
                'message' => $e->getMessage(),
                'expiration_id' => $expiration->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update expiration'
            ], 500);
        }
    }
    public function destroy(ExpirationTracking $expiration):JsonResponse
    {
        try {

            $result = $this->service->deleteExpiration($expiration);

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {

            Log::error('Expiration Delete Error', [
                'message' => $e->getMessage(),
                'expiration_id' => $expiration->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expiration'
            ], 500);
        }
    }


    public function renew(RenewExpirationRequest $request,ExpirationTracking $expiration): JsonResponse
    {
        try {

            $result = $this->service->renewExpiration(
                $request->validated(),
                $expiration
            );

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {

            Log::error('Expiration Renew Error', [
                'message' => $e->getMessage(),
                'expiration_id' => $expiration->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to renew expiration'
            ], 500);
        }
    }
    public function getExpiringItems(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);

        $data = $this->service->getExpiringItems($days);

        return response()->json($data);
    }
    public function updateWarningSettings(UpdateExpirationWarningSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->updateWarningSettings(
            $validated['expiration_ids'],
            $validated['warning_days']
        );

        return response()->json($result);
    }
}