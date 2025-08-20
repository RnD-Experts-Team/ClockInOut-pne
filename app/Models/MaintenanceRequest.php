<?php
// app/Models/MaintenanceRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'basic_troubleshoot_done' => 'boolean',
        'request_date' => 'date',
        'date_submitted' => 'datetime',
        'costs' => 'decimal:2'
    ];

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

    // Accessors
    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFormattedCostsAttribute()
    {
        return $this->costs ? '$' . number_format($this->costs, 2) : null;
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
            'done' => [], // Cannot change from done
            'canceled' => [] // Cannot change from canceled
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
