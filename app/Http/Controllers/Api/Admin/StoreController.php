<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\Api\Admin\StoreService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreStoreRequest;
use App\Http\Requests\Api\Admin\StoreUpdateRequest;

class StoreController extends Controller
{

    public function __construct(
        private StoreService $storeService
    ) {}


    public function index()
    {
        try {

            $stores = $this->storeService->index();

            return response()->json([
                'success' => true,
                'data' => $stores
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stores',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(StoreStoreRequest $request)
    {
        try {

            $store = $this->storeService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Store created successfully',
                'data' => $store
            ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create store',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Store $store)
    {
        try {

            $store = $this->storeService->show($store);

            return response()->json([
                'success' => true,
                'data' => $store
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch store',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(StoreUpdateRequest $request, Store $store)
    {
        try {

            $store = $this->storeService->update(
                $store,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Store updated successfully',
                'data' => $store
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update store',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Store $store)
    {
        try {

            $this->storeService->destroy($store);

            return response()->json([
                'success' => true,
                'message' => 'Store deleted successfully'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    public function toggleStatus(Store $store)
    {
        try {

            $store = $this->storeService->toggleStatus($store);

            $status = $store->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Store {$status} successfully",
                'data' => $store
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle store status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}