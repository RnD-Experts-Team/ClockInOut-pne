<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workbook extends Model
{
    use HasFactory;
    
    protected $fillable = ['folder_id', 'name', 'description'];
    
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
    
    public function columns()
    {
        return $this->hasMany(Column::class)->orderBy('order');
    }
    
    public function rows()
    {
        return $this->hasMany(Row::class);
    }
}