<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Workbook;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkbookController extends Controller
{
    public function store(Request $request, Folder $folder)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'columns'     => 'required|array|min:1',
            'columns.*'   => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($folder, $validated) {
            $workbook = $folder->workbooks()->create([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['columns'] as $index => $columnName) {
                $workbook->columns()->create([
                    'name'  => $columnName,
                    'order' => $index,
                ]);
            }
        });

        return redirect()
            ->route('workbooks.folders.show', $folder)
            ->with('success', 'Workbook created successfully!');
    }

    public function show(Request $request, Folder $folder, Workbook $workbook)
    {
        $workbook->load(['folder', 'columns']);

        $query = $workbook->rows()->with('cellValues');

        // Dynamic column filters
        $filters = [];
        foreach ($workbook->columns as $column) {
            $key = 'filter_' . $column->id;
            if ($request->filled($key)) {
                $filters[$column->id] = $request->get($key);
            }
        }

        if (!empty($filters)) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters as $columnId => $value) {
                    $q->whereHas('cellValues', function ($cellQuery) use ($columnId, $value) {
                        $cellQuery->where('column_id', $columnId)
                                  ->where('value', 'like', "%{$value}%");
                    });
                }
            });
        }

        // Global search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('cellValues', function ($cellQuery) use ($search) {
                    $cellQuery->where('value', 'like', "%{$search}%");
                });
            });
        }

        // Sorting by a column
        if ($request->filled('sort_column') && $request->filled('sort_order')) {
            $sortColumn = $request->sort_column;
            $sortOrder  = $request->sort_order;

            if ($workbook->columns()->where('id', $sortColumn)->exists()) {
                $query->leftJoin('cell_values', function ($join) use ($sortColumn) {
                        $join->on('rows.id', '=', 'cell_values.row_id')
                             ->where('cell_values.column_id', '=', $sortColumn);
                    })
                    ->select('rows.*')
                    ->orderBy('cell_values.value', $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $rows = $query->get()->unique('id');

        $rowsData = $rows->map(function ($row) use ($workbook) {
    return [
        'id'         => $row->id,
        'created_at' => $row->created_at->diffForHumans(),
        'updated_at' => $row->updated_at->diffForHumans(),
        'cells'      => $workbook->columns->mapWithKeys(function ($column) use ($row) {
            return [$column->id => $row->getCellValue($column->id)];
        })->toArray(),
    ];
})->toArray();

        return view('workbooks.show', [
            'folder'  => $folder,
            'workbook'=> $workbook,
            'rows'    => $rows,
            'filters' => $filters,
            'rowsData'  => $rowsData,
        ]);
    }

    public function edit(Folder $folder, Workbook $workbook)
    {
        $workbook->load('columns');
        return view('workbooks.edit', compact('folder', 'workbook'));
    }

    public function update(Request $request, Folder $folder, Workbook $workbook)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'columns'          => 'required|array|min:1',
            'columns.*.id'     => 'nullable|exists:columns,id',
            'columns.*.name'   => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($workbook, $validated) {
            $workbook->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $existingIds = collect($validated['columns'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // delete removed columns
            $workbook->columns()->whereNotIn('id', $existingIds)->delete();

            foreach ($validated['columns'] as $index => $columnData) {
                if (isset($columnData['id'])) {
                    Column::where('id', $columnData['id'])->update([
                        'name'  => $columnData['name'],
                        'order' => $index,
                    ]);
                } else {
                    $workbook->columns()->create([
                        'name'  => $columnData['name'],
                        'order' => $index,
                    ]);
                }
            }
        });

        return redirect()
            ->route('workbooks.folders.show', $workbook->folder)
            ->with('success', 'Workbook updated successfully!');
    }

    public function destroy(Folder $folder, Workbook $workbook)
    {
        $workbook->delete();

        return redirect()
            ->route('workbooks.folders.show', $folder)
            ->with('success', 'Workbook deleted successfully!');
    }
}
