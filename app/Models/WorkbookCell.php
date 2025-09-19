<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkbookCell extends Model
{
    protected $table = 'workbook_cells';

    protected $fillable = [
        'row_id','column_id',
        'value_text','value_number','value_date','value_bool','value_json'
    ];

    protected $casts = [
        'value_json' => 'array',
        'value_date' => 'datetime',
        'value_bool' => 'boolean',
    ];

    public function row()
    {
        return $this->belongsTo(WorkbookRow::class, 'row_id');
    }

    public function column()
    {
        return $this->belongsTo(WorkbookColumn::class, 'column_id');
    }

    public function resetValues(): void
    {
        $this->value_text = null;
        $this->value_number = null;
        $this->value_date = null;
        $this->value_bool = null;
        $this->value_json = null;
    }
}
