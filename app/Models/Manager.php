<?php
// app/Models/Manager.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name'
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function reviewedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'reviewed_by_manager_id');
    }
}
