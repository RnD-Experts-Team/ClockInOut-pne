<?php
// app/Models/WebhookNotification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'type',
        'message',
        'read_at',
        'is_broadcast'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_broadcast' => 'boolean'
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }


    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function webhookNotifications()
    {
        return $this->hasMany(WebhookNotification::class);
    }

    // Add this relationship to store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Add this relationship to urgency level
    public function urgencyLevel()
    {
        return $this->belongsTo(UrgencyLevel::class);
    }
}
