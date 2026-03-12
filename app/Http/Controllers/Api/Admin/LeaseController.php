<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Api\Admin\LeaseStoreRequest;
use Illuminate\Http\Request;
use App\Services\Api\Admin\LeaseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\LeaseUpdateRequest;
use App\Http\Requests\LeaseImportRequest;
use App\Models\Lease;
use Illuminate\Http\JsonResponse;

class LeaseController extends Controller
{
    public function __construct(
        private LeaseService $leaseService
    ) {}

    public function getPortfolioStats(Request $request): JsonResponse
    {
        try {

            $stats = $this->leaseService->getPortfolioStats($request);

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch portfolio statistics.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function import(LeaseImportRequest $request): JsonResponse
    {
        $result = $this->leaseService->import(
            $request->file('import_file')
        );

        if (!$result['success']) {

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => $result['errors'] ?? []
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message']
        ], 200);
    }
    public function downloadTemplate(): JsonResponse
    {
        try {

            $template = $this->leaseService->downloadTemplate();

            return response()->json([
                'success' => true,
                'message' => 'Lease import template generated successfully.',
                'data' => [
                    'filename' => $template['filename'],
                    'rows' => $template['data']
                ]
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function landlordContact(): JsonResponse
    {
        try {

            $data = $this->leaseService->landlordContact();

            return response()->json([
                'success' => true,
                'message' => 'Landlord contacts fetched successfully.',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch landlord contacts.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
     public function costBreakdown(): JsonResponse
    {
        try {
            $data = $this->leaseService->costBreakdown();

            return response()->json([
                'success' => true,
                'message' => 'Cost breakdown fetched successfully.',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cost breakdown.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function leaseTracker(): JsonResponse
    {
        try {
            $data = $this->leaseService->leaseTracker();

            return response()->json([
                'success' => true,
                'message' => 'Lease tracker fetched successfully.',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lease tracker.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try {

            $data = $this->leaseService->index($request);

            return response()->json([
                'success' => true,
                'message' => 'Leases fetched successfully.',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leases.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function store(LeaseStoreRequest $request): JsonResponse
    {
        try {

            $lease = $this->leaseService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Lease created successfully.' . ($lease->renewal_date ? ' Renewal reminders have been set.' : ''),
                'data' => $lease
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create lease.',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    public function show(Lease $lease): JsonResponse
    {
        try {
            $data = $this->leaseService->show($lease);

            return response()->json([
                'success' => true,
                'message' => 'Lease fetched successfully.',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lease.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(LeaseUpdateRequest $request, Lease $lease)
    {
        try {

            $result = $this->leaseService->update(
                $request->validated(),
                $lease
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['lease']
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update lease.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Lease $lease)
    {
        try {

            $this->leaseService->destroy($lease);

            return response()->json([
                'success' => true,
                'message' => 'Lease and related reminders deleted successfully.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lease.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function export(Request $request)
    {
        try {

            $file = $this->leaseService->export($request);

            return response()->json([
                'success' => true,
                'filename' => 'leases_with_renewals_' . now()->format('Y-m-d_H-i-s') . '.csv',
                'file' => $file
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}