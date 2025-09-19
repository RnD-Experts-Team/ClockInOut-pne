<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkbookColumn extends Model
{
    protected $table = 'workbook_columns';

    protected $fillable = [
        'name','slug','type','position','required','is_unique','options',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'bool',
        'is_unique' => 'bool',
    ];

    public function cells()
    {
        return $this->hasMany(WorkbookCell::class, 'column_id');
    }
}
