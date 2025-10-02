<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    use HasFactory;
    
    protected $fillable = ['workbook_id'];
    
    public function workbook()
    {
        return $this->belongsTo(Workbook::class);
    }
    
    public function cellValues()
    {
        return $this->hasMany(CellValue::class);
    }
    
    public function getCellValue($columnId)
    {
        return $this->cellValues()->where('column_id', $columnId)->first()?->value;
    }
}