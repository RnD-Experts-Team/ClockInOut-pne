<?php

namespace App\Services\Api\Admin;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderService
{
    public function index(Request $request): array
    {
        $query = Folder::query()->withCount('workbooks');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'created_at', 'workbooks_count'])) {
            if ($sortBy === 'workbooks_count') {
                $query->orderBy('workbooks_count', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

         $folders = $query->latest()->get();

        return [
            'success' => true,
            'data' => $folders
        ];
    }
    public function store(array $validated): array
    {
        $folder = Folder::create($validated);

        return [
            'success' => true,
            'message' => 'Folder created successfully!',
            'data' => $folder,
        ];
    }

    public function update(Folder $folder, array $validated): array
    {
        $folder->update($validated);

        return [
            'success' => true,
            'message' => 'Folder updated successfully!',
            'data' => $folder,
        ];
    }
     public function destroy(Folder $folder): array
    {
        $folder->delete();

        return [
            'success' => true,
            'message' => 'Folder deleted successfully!',
        ];
    }

    public function show(Request $request, Folder $folder): array
    {
        $query = $folder->workbooks()->with('columns');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $workbooks = $query->get();

        return [
            'success' => true,
            'data' => [
                'folder' => $folder,
                'workbooks' => $workbooks,
            ]
        ];
    }
}