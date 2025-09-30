<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    use HasFactory;
    
    protected $fillable = ['workbook_id', 'name', 'order'];
    
    public function workbook()
    {
        return $this->belongsTo(Workbook::class);
    }
    
    public function cellValues()
    {
        return $this->hasMany(CellValue::class);
    }
}
