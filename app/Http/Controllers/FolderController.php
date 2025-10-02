<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index(Request $request)
    {
        $query = Folder::query()->withCount('workbooks');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'created_at', 'workbooks_count'])) {
            if ($sortBy === 'workbooks_count') {
                $query->orderBy('workbooks_count', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $folders = $query->latest()->get();

        return view('workbooks.folders.index', compact('folders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Folder::create($validated);

        return redirect()->route('workbooks.folders.index')->with('success', 'Folder created successfully!');
    }

    public function update(Request $request, Folder $folder)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $folder->update($validated);

        return redirect()->route('workbooks.folders.index')->with('success', 'Folder updated successfully!');
    }

    public function destroy(Folder $folder)
    {
        $folder->delete();

        return redirect()->route('workbooks.folders.index')->with('success', 'Folder deleted successfully!');
    }

    public function show(Request $request, Folder $folder)
    {
        $query = $folder->workbooks()->with('columns');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $workbooks = $query->get();

        return view('workbooks.folders.show', compact('folder', 'workbooks'));
    }
}
