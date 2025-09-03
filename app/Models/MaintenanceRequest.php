<?php
// app/Models/MaintenanceRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'store_id',
        'description_of_issue',
        'urgency_level_id',
        'equipment_with_issue',
        'basic_troubleshoot_done',
        'request_date',
        'date_submitted',
        'entry_number',
        'status',
        'costs',
        'how_we_fixed_it',
        'requester_id',
        'reviewed_by_manager_id',
        'webhook_id',
        'not_in_cognito',
        'assigned_to',
        'due_date',
        'assignment_source',
        'current_task_assignment_id',
    ];

    protected $casts = [
        'basic_troubleshoot_done' => 'boolean',
        'costs' => 'decimal:2',
        'due_date' => 'datetime',
        'request_date' => 'datetime',
        'date_submitted' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function currentTaskAssignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'current_task_assignment_id');
    }
    public function getEffectiveAssignedUserAttribute()
    {
        if ($this->assignment_source === 'task_assignment' && $this->currentTaskAssignment) {
            return $this->currentTaskAssignment->assignedUser;
        }

        return $this->assignedTo;
    }
    public function getEffectiveDueDateAttribute()
    {
        if ($this->assignment_source === 'task_assignment' && $this->currentTaskAssignment) {
            return $this->currentTaskAssignment->due_date;
        }

        return $this->due_date;
    }
    public function getAssignmentDetailsAttribute()
    {
        return [
            'user' => $this->effective_assigned_user,
            'due_date' => $this->effective_due_date,
            'source' => $this->assignment_source,
            'task_assignment_id' => $this->current_task_assignment_id,
        ];
    }

    public function assignDirectly($userId, $dueDate = null)
    {
        Log::debug('assignDirectly method called', [
            'maintenance_request_id' => $this->id,
            'current_assigned_to' => $this->assigned_to,
            'current_due_date' => $this->due_date,
            'current_assignment_source' => $this->assignment_source,
            'current_task_assignment_id' => $this->current_task_assignment_id,
            'new_user_id' => $userId,
            'new_due_date' => $dueDate,
        ]);

        // Use individual assignment + save() instead of update()
        $this->assigned_to = $userId;
        $this->due_date = $dueDate;
        $this->assignment_source = 'direct';
        $this->current_task_assignment_id = null;

        $result = $this->save();

        Log::debug('assignDirectly save result', [
            'save_result' => $result,
            'after_assigned_to' => $this->assigned_to,
            'after_due_date' => $this->due_date,
            'after_assignment_source' => $this->assignment_source,
            'after_task_assignment_id' => $this->current_task_assignment_id,
        ]);

        return $result;
    }

    public function assignThroughTask(TaskAssignment $taskAssignment)
    {
        $this->update([
            'assignment_source' => 'task_assignment',
            'current_task_assignment_id' => $taskAssignment->id,
            // Keep direct assignment fields for backup/history
        ]);
    }
//    public function getIsAssignedAttribute()
//    {
//        return $this->effective_assigned_user !== null;
//    }
    public function latestTaskAssignment()
    {
        return $this->hasOne(TaskAssignment::class, 'maintenance_request_id')
            ->latestOfMany('assigned_at');
    }
    public function getLatestTaskAssignmentAttribute()
    {
        return $this->taskAssignments->first(); // Since we ordered by assigned_at desc
    }

    // ✅ Get assigned user from latest task assignment
    public function getTaskAssignedToAttribute()
    {
        return $this->latestTaskAssignment?->assignedUser;
    }

    // ✅ Get due date from latest task assignment
    public function getTaskDueDateAttribute()
    {
        return $this->latestTaskAssignment?->due_date;
    }
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function urgencyLevel(): BelongsTo
    {
        return $this->belongsTo(UrgencyLevel::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class);
    }

    public function reviewedByManager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'reviewed_by_manager_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }

    // ADD THIS MISSING RELATIONSHIP
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'maintenance_request_id');
    }

    // ADD THESE ADDITIONAL RELATIONSHIPS FOR SCHEDULE INTEGRATION
    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'maintenance_request_id');
    }

    public function scheduleShifts(): HasMany
    {
        return $this->hasMany(ScheduleShift::class, 'task_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', 'on_hold');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeByUrgency($query, $urgencyId)
    {
        return $query->where('urgency_level_id', $urgencyId);
    }

    // ADD THESE SCOPES FOR SCHEDULE INTEGRATION
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to')
            ->whereNotIn('status', ['done', 'canceled']);
    }

    public function scopeAvailableForAssignment($query)
    {
        return $query->whereNotIn('status', ['done', 'canceled'])
            ->where(function($q) {
                $q->whereNull('assigned_to')
                    ->orWhereDoesntHave('assignments', function($subQuery) {
                        $subQuery->whereIn('status', ['pending', 'in_progress']);
                    });
            });
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFormattedCostsAttribute()
    {
        return $this->costs ? '$' . number_format($this->costs, 2) : null;
    }

    // ADD THESE ACCESSORS FOR SCHEDULE INTEGRATION
    public function getIsAssignedAttribute()
    {
        return !is_null($this->assigned_to) || $this->assignments()->whereIn('status', ['pending', 'in_progress'])->exists();
    }

    public function getCanBeAssignedAttribute()
    {
        return !in_array($this->status, ['done', 'canceled']) && !$this->is_assigned;
    }

    // Relationships remain the same...
    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceAttachment::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(MaintenanceLink::class);
    }

    // Updated helper methods
    public function canMoveToStatus(string $newStatus): bool
    {
        $allowedTransitions = [
            'on_hold' => ['in_progress', 'done', 'canceled'],
            'in_progress' => ['on_hold', 'done', 'canceled'],
            'done' => ['on_hold', 'done', 'canceled'],
            'canceled' => ['on_hold', 'done', 'canceled']
        ];

        return in_array($newStatus, $allowedTransitions[$this->status] ?? []);
    }

    public function updateStatus(string $newStatus, ?float $costs = null, ?string $howWeFixedIt = null, ?int $userId = null): bool
    {
        if ($newStatus === 'done' && (empty($costs) || empty($howWeFixedIt))) {
            throw new \InvalidArgumentException('Costs and how we fixed it are required when marking as done.');
        }

        $oldStatus = $this->status;

        $this->status = $newStatus;
        if ($newStatus === 'done') {
            $this->costs = $costs;
            $this->how_we_fixed_it = $howWeFixedIt;
        }

        $saved = $this->save();

        if ($saved) {
            // Record status change in history
            StatusHistory::create([
                'maintenance_request_id' => $this->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by_user_id' => $userId,
                'notes' => $newStatus === 'done' ? "Costs: {$costs}, Fixed: {$howWeFixedIt}" : null,
                'changed_at' => now()
            ]);
        }

        return $saved;
    }
}
