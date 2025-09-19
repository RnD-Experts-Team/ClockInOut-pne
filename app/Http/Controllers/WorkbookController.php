<?php

namespace App\Http\Controllers;

use App\Models\WorkbookCell;
use App\Models\WorkbookColumn;
use App\Models\WorkbookRow;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Store;
class WorkbookController extends Controller
{
    public function saveRow(Request $request, WorkbookRow $row)
{
    // Expect: cells is an associative array keyed by column_id or slug
    $data = $request->validate([
        'cells' => ['required','array'],
        'store_id' => ['nullable','integer','exists:stores,id'],
    ]);
     if (array_key_exists('store_id', $data)) {
            $row->store_id = $data['store_id'];
            $row->save();
        }

    // Load columns (by id and by slug)
    $columns = WorkbookColumn::all();
    $byId   = $columns->keyBy('id');
    $bySlug = $columns->keyBy('slug');

    // Normalize incoming map to column_id => value
    $incoming = [];
    foreach ($data['cells'] as $key => $value) {
        $col = is_numeric($key) ? $byId->get((int)$key) : $bySlug->get($key);
        if ($col) {
            $incoming[$col->id] = $value;
        }
    }

    // Helper to coerce value per type
    $coerce = function ($value, WorkbookColumn $col) {
        if ($value === '' || $value === null) {
            return null;
        }
        return match ($col->type) {
            'number' => is_numeric($value) ? (float)$value : null,
            'date'   => $value ? Carbon::parse($value) : null,
            'bool'   => ($value === '1' || $value === 1 || $value === true) ? true :
                        (($value === '0' || $value === 0 || $value === false) ? false : null),
            'json'   => is_array($value) ? $value : (function($v){
                            if ($v === '' || $v === null) return null;
                            try { return json_decode($v, true, 512, JSON_THROW_ON_ERROR); }
                            catch (\Throwable) { return '__INVALID_JSON__'; }
                        })($value),
            default  => (string)$value,
        };
    };

    // Validate (required + unique)
    $errors = [];
    foreach ($incoming as $colId => $raw) {
        $col = $byId[$colId];

        $val = $coerce($raw, $col);
        if ($col->type === 'json' && $val === '__INVALID_JSON__') {
            $errors["cells.$colId"] = 'Invalid JSON';
            continue;
        }

        // Required?
        if ($col->required && ($val === null || $val === '')) {
            $errors["cells.$colId"] = 'This column is required';
        }

        // Unique?
        if ($col->is_unique && $val !== null) {
            $q = WorkbookCell::where('column_id', $col->id)->where('row_id', '!=', $row->id);
            match ($col->type) {
                'number' => $q->where('value_number', (float)$val),
                'date'   => $q->where('value_date', Carbon::parse($raw)),
                'bool'   => $q->where('value_bool', (bool)$val),
                'json'   => $q->where('value_json', json_encode($val)),
                default  => $q->where('value_text', (string)$val),
            };
            if ($q->exists()) {
                $errors["cells.$colId"] = 'Value must be unique in this column';
            }
        }
    }

    if (!empty($errors)) {
        return back()->withErrors($errors)->withInput();
    }

    // Persist all cells atomically
    DB::transaction(function () use ($incoming, $byId, $row, $coerce) {
        foreach ($incoming as $colId => $raw) {
            /** @var WorkbookColumn $col */
            $col = $byId[$colId];
            $val = $coerce($raw, $col); // already validated above

            $cell = WorkbookCell::firstOrNew([
                'row_id' => $row->id,
                'column_id' => $col->id,
            ]);
            $cell->resetValues();

            switch ($col->type) {
                case 'number': $cell->value_number = $val; break;
                case 'date':   $cell->value_date   = $val; break; // Carbon accepted
                case 'bool':   $cell->value_bool   = $val; break;
                case 'json':   $cell->value_json   = $val; break;
                default:       $cell->value_text   = $val; break;
            }
            $cell->save();
        }
    });

    return redirect()->route('workbook.index')->with('status', 'Row saved.');
}
    /**
     * Render the workbook page with grid data.
     */
    public function index()
    {
        $columns = WorkbookColumn::orderBy('position')->get();

        $rows = WorkbookRow::with([
            'cells' => fn ($q) => $q->with('column'),
            'store', // ⬅ eager-load store
        ])->orderBy('position')->get();

        $rowsTransformed = $rows->map(function ($row) use ($columns) {
            $cellMap = [];
            foreach ($columns as $col) {
                $cell = $row->cells->firstWhere('column_id', $col->id);
                $cellMap[$col->slug] = $cell
                    ? static::extractTypedValue($col->type, $cell)
                    : null;
            }
            return [
                'id'       => $row->id,
                'position' => $row->position,
                'store'    => $row->store ? [
                    'id'           => $row->store->id,
                    'store_number' => $row->store->store_number,
                    'name'         => $row->store->name,
                    'is_active'    => $row->store->is_active,
                ] : null,
                'cells'    => $cellMap,
            ];
        });

        // ⬇ pass stores for dropdowns (active first; tweak as needed)
        $stores = Store::orderByDesc('is_active')
            ->orderBy('store_number')
            ->get(['id','store_number','name','is_active']);

        return view('workbook.index', [
            'columns' => $columns,
            'rows'    => $rowsTransformed,
            'stores'  => $stores,
        ]);
    }

    // ----- Columns -----

    public function storeColumn(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','regex:/^[a-z0-9_]+$/'],
            'type' => ['required', Rule::in(['string','number','date','bool','json'])],
            'position' => ['nullable','integer','min:0'],
            'required' => ['sometimes','boolean'],
            'is_unique' => ['sometimes','boolean'],
            'options' => ['nullable','array'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name'], '_');

        if (WorkbookColumn::where('slug', $data['slug'])->exists()) {
            return back()->withErrors(['slug' => 'Slug already exists'])->withInput();
        }

        WorkbookColumn::create($data);

        return redirect()->route('workbook.index')->with('status', 'Column created.');
    }

    public function updateColumn(Request $request, WorkbookColumn $column)
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'slug' => ['sometimes','string','max:255','regex:/^[a-z0-9_]+$/', Rule::unique('workbook_columns','slug')->ignore($column->id)],
            'type' => ['sometimes', Rule::in(['string','number','date','bool','json'])],
            'position' => ['sometimes','integer','min:0'],
            'required' => ['sometimes','boolean'],
            'is_unique' => ['sometimes','boolean'],
            'options' => ['nullable','array'],
        ]);

        $column->update($data);

        return redirect()->route('workbook.index')->with('status', 'Column updated.');
    }

    public function destroyColumn(WorkbookColumn $column)
    {
        $column->delete();
        return redirect()->route('workbook.index')->with('status', 'Column deleted.');
    }

    // ----- Rows -----

    public function storeRow(Request $request)
    {
        $data = $request->validate([
            'position' => ['nullable','integer','min:0'],
            'store_id' => ['nullable','integer','exists:stores,id'], // ⬅ accept at creation
        ]);

        WorkbookRow::create([
            'position' => $data['position'] ?? 0,
            'store_id' => $data['store_id'] ?? null,
        ]);

        return redirect()->route('workbook.index')->with('status', 'Row added.');
    }

    public function updateRow(Request $request, WorkbookRow $row)
    {
        $data = $request->validate([
            'position' => ['sometimes','integer','min:0'],
            'store_id' => ['sometimes','nullable','integer','exists:stores,id'], // ⬅ allow updating
        ]);

        $row->update($data);

        return redirect()->route('workbook.index')->with('status', 'Row updated.');
    }

    public function destroyRow(WorkbookRow $row)
    {
        $row->delete();
        return redirect()->route('workbook.index')->with('status', 'Row deleted.');
    }

    // ----- Cells -----

    /**
     * Upsert a single cell (via form submit).
     */
    public function upsertCell(Request $request)
    {
        $data = $request->validate([
            'row_id' => ['required','integer','exists:workbook_rows,id'],
            'column_id' => ['required','integer','exists:workbook_columns,id'],
            'value' => ['nullable'],
        ]);

        $row = WorkbookRow::findOrFail($data['row_id']);
        $column = WorkbookColumn::findOrFail($data['column_id']);

        $this->validateValueByType($request, $column);

        if ($column->is_unique && !is_null($data['value'])) {
            $exists = WorkbookCell::where('column_id', $column->id)
                ->when($column->type === 'string', fn($q) => $q->where('value_text', $data['value']))
                ->when($column->type === 'number', fn($q) => $q->where('value_number', (float)$data['value']))
                ->when($column->type === 'date',   fn($q) => $q->where('value_date', $data['value']))
                ->when($column->type === 'bool',   fn($q) => $q->where('value_bool', (bool)$data['value']))
                ->when($column->type === 'json',   fn($q) => $q->where('value_json', json_encode($data['value'])))
                ->where('row_id', '!=', $row->id)
                ->exists();
            if ($exists) {
                return back()->withErrors(['value' => 'Value must be unique for this column'])->withInput();
            }
        }

        $cell = WorkbookCell::firstOrNew([
            'row_id' => $row->id,
            'column_id' => $column->id,
        ]);
        $cell->resetValues();

        switch ($column->type) {
            case 'number': $cell->value_number = is_null($data['value']) ? null : (float)$data['value']; break;
            case 'date':   $cell->value_date   = $data['value']; break;
            case 'bool':   $cell->value_bool   = is_null($data['value']) ? null : (bool)$data['value']; break;
            case 'json':   $cell->value_json   = $data['value']; break;
            default:       $cell->value_text   = is_null($data['value']) ? null : (string)$data['value']; break;
        }

        if ($column->required && is_null($this->extractTypedValue($column->type, $cell))) {
            return back()->withErrors(['value' => 'This column is required'])->withInput();
        }

        $cell->save();

        return redirect()->route('workbook.index')->with('status', 'Cell saved.');
    }

    public function destroyCell(Request $request)
    {
        $data = $request->validate([
            'row_id' => ['required','integer','exists:workbook_rows,id'],
            'column_id' => ['required','integer','exists:workbook_columns,id'],
        ]);

        WorkbookCell::where('row_id', $data['row_id'])
            ->where('column_id', $data['column_id'])
            ->delete();

        return redirect()->route('workbook.index')->with('status', 'Cell cleared.');
    }

    // ----- Helpers -----

    public static function extractTypedValue(string $type, WorkbookCell $cell)
    {
        return match ($type) {
            'number' => $cell->value_number,
            'date'   => optional($cell->value_date)?->toISOString(),
            'bool'   => $cell->value_bool,
            'json'   => $cell->value_json,
            default  => $cell->value_text,
        };
    }

    protected function validateValueByType(Request $request, WorkbookColumn $column): void
    {
        $rule = match ($column->type) {
            'number' => ['nullable','numeric'],
            'date'   => ['nullable','date'],
            'bool'   => ['nullable','boolean'],
            'json'   => ['nullable','array'],
            default  => ['nullable','string'],
        };
        $request->validate(['value' => $rule]);

        if ($column->required && $request->missing('value')) {
            abort(back()->withErrors(['value' => 'This column is required']));
        }
    }
}
