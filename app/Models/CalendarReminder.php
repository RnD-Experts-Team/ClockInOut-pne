<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class CalendarReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_event_id',
        'admin_user_id',
        'title',
        'description',
        'reminder_date',
        'reminder_time',
        'reminder_type',
        'is_recurring',
        'recurrence_pattern',
        'status',
        'related_model_type',
        'related_model_id',
        'notification_methods',
        'last_sent_at',
        'snooze_until',
        'notified_at',
        'notification_status',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'reminder_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
        'notification_methods' => 'json',
        'last_sent_at' => 'datetime',
        'snooze_until' => 'datetime',
        'notified_at' => 'datetime',    // NEW

    ];

    // Fixed relatedModel method
    public function relatedModel(): MorphTo
    {
        return $this->morphTo('related_model', 'related_model_type', 'related_model_id');
    }

    // Add these Query Scopes that your controller needs
    public function scopeByUser($query, int $userId)
    {
        return $query->where('admin_user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        $now = Carbon::now();

        return $query->where('status', 'pending')
            ->where(function ($subQuery) use ($now) {
                $subQuery->where('reminder_date', '<', $now->format('Y-m-d'))
                    ->orWhere(function ($timeQuery) use ($now) {
                        $timeQuery->where('reminder_date', '=', $now->format('Y-m-d'))
                            ->where('reminder_time', '<', $now->format('H:i'));
                    });
            })
            ->where(function ($snoozeQuery) {
                $snoozeQuery->whereNull('snooze_until')
                    ->orWhere('snooze_until', '<', Carbon::now());
            });
    }

    public function scopeDueSoon($query, int $hours = 24)
    {
        $now = Carbon::now();
        $endTime = Carbon::now()->addHours($hours);

        return $query->where('status', 'pending')
            ->where('reminder_date', '>=', $now->format('Y-m-d'))
            ->where('reminder_date', '<=', $endTime->format('Y-m-d'))
            ->where(function ($snoozeQuery) {
                $snoozeQuery->whereNull('snooze_until')
                    ->orWhere('snooze_until', '<', Carbon::now());
            });
    }

    // Helper methods
    public function dismiss(): void
    {
        $this->update(['status' => 'dismissed']);
    }

    public function snooze(int $minutes): void
    {
        $this->update([
            'snooze_until' => Carbon::now()->addMinutes($minutes),
            'status' => 'snoozed'
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'last_sent_at' => Carbon::now()
        ]);
    }

    public function shouldSend(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->snooze_until && $this->snooze_until->isFuture()) {
            return false;
        }

        $reminderDateTime = $this->reminder_time
            ? Carbon::parse($this->reminder_date->format('Y-m-d') . ' ' . $this->reminder_time->format('H:i:s'))
            : $this->reminder_date->endOfDay();

        return $reminderDateTime->isPast();
    }

    public function getFormattedReminderDateTime(): string
    {
        if ($this->reminder_time) {
            return $this->reminder_date->format('M j, Y') . ' at ' . $this->reminder_time->format('g:i A');
        }

        return $this->reminder_date->format('M j, Y') . ' (All Day)';
    }

    // Relationships
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    // Safe method to get related model without errors
    public function getSafeRelatedModelAttribute()
    {
        try {
            if (!$this->related_model_type || !$this->related_model_id) {
                return null;
            }

            // Define safe model mappings
            $modelMap = [
                'MaintenanceRequest' => \App\Models\MaintenanceRequest::class,
                'ApartmentLease' => \App\Models\ApartmentLease::class,
                'Lease' => \App\Models\Lease::class,
            ];

            if (isset($modelMap[$this->related_model_type])) {
                $modelClass = $modelMap[$this->related_model_type];

                if (class_exists($modelClass)) {
                    return $modelClass::find($this->related_model_id);
                }
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Failed to load related model', [
                'reminder_id' => $this->id,
                'related_type' => $this->related_model_type,
                'related_id' => $this->related_model_id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    // Existing attribute methods
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $reminderDateTime = $this->reminder_time
            ? Carbon::parse($this->reminder_date->format('Y-m-d') . ' ' . $this->reminder_time->format('H:i:s'))
            : $this->reminder_date->endOfDay();

        return $reminderDateTime->isPast();
    }

    public function getTimeUntilAttribute(): string
    {
        if ($this->is_overdue) {
            return 'Overdue';
        }

        $reminderDateTime = $this->reminder_time
            ? Carbon::parse($this->reminder_date->format('Y-m-d') . ' ' . $this->reminder_time->format('H:i:s'))
            : $this->reminder_date->startOfDay();

        return $reminderDateTime->diffForHumans();
    }

    // Add scopes:
    public function scopePendingNotifications($query) {
        return $query->where('notification_status', 'pending')
            ->where('reminder_time', '<=', now());
    }

// Add methods:
    public function markAsNotified() {
        $this->update([
            'notified_at' => now(),
            'notification_status' => 'shown'
        ]);
    }

    public function isDue() {
        return $this->reminder_time <= now() &&
            $this->notification_status === 'pending';
    }

    
}
