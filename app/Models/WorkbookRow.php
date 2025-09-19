<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkbookRow extends Model
{
    protected $table = 'workbook_rows';

    protected $fillable = ['position','store_id']; // ⬅ add store_id

    public function cells()
    {
        return $this->hasMany(WorkbookCell::class, 'row_id');
    }
        public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
