<?php

namespace App\Services\Api\Admin;

use App\Models\Folder;
use App\Models\Workbook;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WorkbookService
{
   public function store(Folder $folder, array $validated): void
    {
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
    }
    public function destroy(Workbook $workbook): array
    {
        $workbook->delete();

        return [
            'success' => true,
            'message' => 'Workbook deleted successfully!',
        ];
    }

    public function show(Request $request, Folder $folder, Workbook $workbook): array
    {
        $workbook->load(['folder', 'columns']);

        $query = $workbook->rows()->with('cellValues');

        // filters
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

        // search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('cellValues', function ($cellQuery) use ($search) {
                    $cellQuery->where('value', 'like', "%{$search}%");
                });
            });
        }

        // sorting
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
                'id' => $row->id,
                'created_at' => $row->created_at->diffForHumans(),
                'updated_at' => $row->updated_at->diffForHumans(),
                'cells' => $workbook->columns->mapWithKeys(function ($column) use ($row) {
                    return [$column->id => $row->getCellValue($column->id)];
                })->toArray(),
            ];
        })->toArray();

        return [
            'success' => true,
            'data' => [
                'folder' => $folder,
                'workbook' => $workbook,
                'rows' => $rows,
                'filters' => $filters,
                'rowsData' => $rowsData,
            ]
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
}