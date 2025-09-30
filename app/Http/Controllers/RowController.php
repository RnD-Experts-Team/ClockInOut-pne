<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Workbook;
use App\Models\Row;
use App\Models\CellValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RowController extends Controller
{
     /**
     * Show "Add Row" page.
     */
    public function create(Folder $folder, Workbook $workbook)
    {
        // You can prefill defaults here if you want later
        return view('workbooks.rows.create', [
            'folder'   => $folder,
            'workbook' => $workbook,
        ]);
    }
/**
     * Show "Edit Row" page.
     */
    public function edit(Folder $folder, Workbook $workbook, Row $row)
    {
        // Eager-load values into an array keyed by column_id for convenience
        $row->load('cellValues');
        $cellMap = $row->cellValues->pluck('value', 'column_id');

        return view('workbooks.rows.edit', [
            'folder'    => $folder,
            'workbook'  => $workbook,
            'row'       => $row,
            'cellMap'   => $cellMap, // used by view to prefill inputs
        ]);
    }
    public function store(Request $request, Folder $folder, Workbook $workbook)
    {
        $validated = $request->validate([
            'cells'   => 'required|array',
            'cells.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($workbook, $validated) {
            $row = $workbook->rows()->create();

            foreach ($validated['cells'] as $columnId => $value) {
                if ($value !== null && $value !== '') {
                    CellValue::create([
                        'row_id'    => $row->id,
                        'column_id' => $columnId,
                        'value'     => $value,
                    ]);
                }
            }
        });

        return redirect()
            ->route('workbooks.show', [$folder, $workbook])
            ->with('success', 'Row added successfully!');
    }

    public function update(Request $request, Folder $folder, Workbook $workbook, Row $row)
    {
        $validated = $request->validate([
            'cells'   => 'required|array',
            'cells.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($row, $validated) {
            $row->cellValues()->delete();

            foreach ($validated['cells'] as $columnId => $value) {
                if ($value !== null && $value !== '') {
                    CellValue::create([
                        'row_id'    => $row->id,
                        'column_id' => $columnId,
                        'value'     => $value,
                    ]);
                }
            }
        });

        return redirect()
            ->route('workbooks.show', [$folder, $workbook])
            ->with('success', 'Row updated successfully!');
    }

    public function destroy(Folder $folder, Workbook $workbook, Row $row)
    {
        $row->delete();

        return redirect()
            ->route('workbooks.show', [$folder, $workbook])
            ->with('success', 'Row deleted successfully!');
    }
}
