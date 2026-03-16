<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ApartmentLeaseStoreRequest;
use App\Http\Requests\Api\Admin\ApartmentLeaseUpdateRequest;
use App\Models\ApartmentLease;
use App\Services\Api\Admin\ApartmentLeaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApartmentLeaseController extends Controller
{
    protected ApartmentLeaseService $apartmentLeaseService;

    public function __construct(ApartmentLeaseService $apartmentLeaseService)
    {
        $this->apartmentLeaseService = $apartmentLeaseService;
    }

    public function list(): JsonResponse
    {
        $leases = $this->apartmentLeaseService->list();

        return response()->json([
            'status' => true,
            'message' => 'Apartment leases fetched successfully',
            'data' => $leases,
        ], 200);
    }
    public function index(Request $request): JsonResponse
    {
        $data = $this->apartmentLeaseService->index($request);

        return response()->json([
            'status' => true,
            'message' => 'Apartment leases fetched successfully',
            'data' => $data
        ]);
    }
     public function show(ApartmentLease $apartmentLease): JsonResponse
    {
        $lease = $this->apartmentLeaseService->show($apartmentLease);

        return response()->json([
            'status' => true,
            'message' => 'Apartment lease fetched successfully',
            'data' => $lease,
        ], 200);
    }
     public function store(ApartmentLeaseStoreRequest $request): JsonResponse
    {

        try {

            $lease = $this->apartmentLeaseService->store($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Apartment lease created successfully',
                'data' => $lease
            ], 201);

        }

        catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to create apartment lease',
                'error' => $e->getMessage()
            ], 500);

        }

    }
    public function update(ApartmentLeaseUpdateRequest $request, ApartmentLease $apartmentLease): JsonResponse
    {

        try {

            $lease = $this->apartmentLeaseService->update(
                $apartmentLease,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Apartment lease updated successfully',
                'data' => $lease
            ]);

        }

        catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to update apartment lease',
                'error' => $e->getMessage()
            ],500);

        }

    }
    public function destroy(ApartmentLease $apartmentLease): JsonResponse
    {
        try {

            $this->apartmentLeaseService->destroy($apartmentLease);

            return response()->json([
                'status' => true,
                'message' => 'Apartment lease deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete apartment lease',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function export(Request $request)
    {
        try {

            $data = $this->apartmentLeaseService->export($request);

            return response()->json([
                'success' => true,
                'count' => $data->count(),
                'data' => $data
            ]);

        } catch (\Exception $e) {

            Log::error('Apartment Lease Export Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export apartment leases',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}