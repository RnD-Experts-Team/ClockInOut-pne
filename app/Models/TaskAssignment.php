<?php
// app/Models/TaskAssignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'assigned_user_id',
        'schedule_shift_id',
        'assignment_notes',
        'priority',
        'assigned_at',
        'due_date',
        'status',
        'assigned_by'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'datetime'
    ];
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // ✅ Relationship to maintenance request
    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }




    public function scheduleShift(): BelongsTo
    {
        return $this->belongsTo(ScheduleShift::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getPriorityLabelAttribute()
    {
        return ucfirst($this->priority);
    }

    public function getStatusLabelAttribute()
    {
        return str_replace('_', ' ', ucfirst($this->status));
    }
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'schedule_shift_id');
    }

}
