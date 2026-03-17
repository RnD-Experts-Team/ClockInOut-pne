<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreUpdateRowRequest;
use App\Models\Folder;
use App\Models\Workbook;
use App\Models\Row;
use App\Services\Api\Admin\RowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class RowController extends Controller
{
    public function __construct(
        private RowService $service
    ) {}

    public function store(
        StoreUpdateRowRequest $request,
        Folder $folder,
        Workbook $workbook
    ): JsonResponse {
        try {
            $result = $this->service->store($workbook, $request->validated());

            return response()->json($result, 201);

        } catch (Throwable $e) {

            Log::error('Store Row Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add row.',
            ], 500);
        }
    }

    public function update(
        StoreUpdateRowRequest $request,
        Folder $folder,
        Workbook $workbook,
        Row $row
    ): JsonResponse {
        try {
            $result = $this->service->update($row, $request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Update Row Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update row.',
            ], 500);
        }
    }

    public function destroy(
        Folder $folder,
        Workbook $workbook,
        Row $row
    ): JsonResponse {
        try {
            $result = $this->service->destroy($row);

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Delete Row Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete row.',
            ], 500);
        }
    }
}