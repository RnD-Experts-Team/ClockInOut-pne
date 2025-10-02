<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CellValue extends Model
{
    use HasFactory;
    
    protected $fillable = ['row_id', 'column_id', 'value'];
    
    public function row()
    {
        return $this->belongsTo(Row::class);
    }
    
    public function column()
    {
        return $this->belongsTo(Column::class);
    }
}