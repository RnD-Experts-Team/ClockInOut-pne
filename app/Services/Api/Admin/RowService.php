<?php

namespace App\Services\Api\Admin;

use App\Models\Workbook;
use App\Models\Row;
use App\Models\CellValue;
use Illuminate\Support\Facades\DB;

class RowService
{
    public function store(Workbook $workbook, array $validated): array
    {
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

        return [
            'success' => true,
            'message' => 'Row added successfully!',
        ];
    }

    public function update(Row $row, array $validated): array
    {
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

        return [
            'success' => true,
            'message' => 'Row updated successfully!',
        ];
    }

    public function destroy(Row $row): array
    {
        $row->delete();

        return [
            'success' => true,
            'message' => 'Row deleted successfully!',
        ];
    }
}