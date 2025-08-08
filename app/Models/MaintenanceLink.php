<?php
// app/Models/MaintenanceLink.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'link_type',
        'download_url',
        'description'
    ];

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
