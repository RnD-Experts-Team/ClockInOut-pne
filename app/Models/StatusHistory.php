<?php
// app/Models/StatusHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'notes',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime'
    ];

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by_user_id');
    }
}
