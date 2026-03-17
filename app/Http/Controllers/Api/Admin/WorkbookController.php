<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreWorkbookRequest;
use App\Http\Requests\Api\Admin\UpdateFolderRequest;
use App\Models\Folder;
use App\Models\Workbook;
use App\Services\Api\Admin\WorkbookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class WorkbookController extends Controller
{
    public function __construct(
        private WorkbookService $service
    ) {}

     public function store(StoreWorkbookRequest $request, Folder $folder): JsonResponse
    {
        try {
            $this->service->store($folder, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Workbook created successfully!',
            ], 201);

        } catch (Throwable $e) {

            Log::error('Store Workbook Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create workbook.',
            ], 500);
        }
    }
    public function destroy(Folder $folder, Workbook $workbook): JsonResponse
    {
        try {
            $result = $this->service->destroy($workbook);

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Delete Workbook Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete workbook.',
            ], 500);
        }
    }

    public function show(Request $request,  Folder $folder, Workbook $workbook): JsonResponse {
        try {
            $result = $this->service->show($request, $folder, $workbook);

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Show Workbook Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch workbook.',
            ], 500);
        }
    }
    public function update(UpdateFolderRequest $request, Folder $folder): JsonResponse
    {
        try {
            $result = $this->service->update($folder, $request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Update Folder Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update folder.',
            ], 500);
        }
    }
}