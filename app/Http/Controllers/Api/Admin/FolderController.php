<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ShowFolderRequest;
use App\Http\Requests\Api\Admin\StoreUpdateFolderRequest;
use App\Models\Folder;
use App\Services\Api\Admin\FolderService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Log;
class FolderController extends Controller
{
    public function __construct(
        private FolderService $folderService
    ) {}

     public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->folderService->index($request);

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Folder index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch folders.',
            ], 500);
        }
    }
    public function store(StoreUpdateFolderRequest $request): JsonResponse
    {
        try {
            $result = $this->folderService->store($request->validated());

            return response()->json($result, 201);

        } catch (Throwable $e) {

            Log::error('Store Folder Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create folder.',
            ], 500);
        }
    }

    public function update(StoreUpdateFolderRequest $request, Folder $folder): JsonResponse
    {
        try {
            $result = $this->folderService->update($folder, $request->validated());

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Update Folder Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update folder.',
            ], 500);
        }
    }
     public function destroy(Folder $folder): JsonResponse
    {
        try {
            $result = $this->folderService->destroy($folder);

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Delete Folder Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete folder.',
            ], 500);
        }
    }

    public function show(ShowFolderRequest $request, Folder $folder): JsonResponse
    {
        try {
            $result = $this->folderService->show($request, $folder);

            return response()->json($result, 200);

        } catch (Throwable $e) {

            Log::error('Show Folder Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch folder data.',
            ], 500);
        }
    }
}