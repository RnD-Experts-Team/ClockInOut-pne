<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ExpirationTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'expiration_date',
        'expiration_type',
        'warning_days',
        'status',
        'last_reminder_sent',
        'notes',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'last_reminder_sent' => 'datetime',
    ];

    // Relationships
    public function trackableModel(): MorphTo
    {
        return $this->morphTo('model');
    }

    // Scopes
    public function scopeExpiringSoon($query, $days = null)
    {
        $warningDays = $days ?? 30;
        $warningDate = Carbon::today()->addDays($warningDays);

        return $query->where('expiration_date', '<=', $warningDate)
            ->where('expiration_date', '>=', Carbon::today())
            ->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', Carbon::today())
            ->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('expiration_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('last_reminder_sent')
                    ->orWhere('last_reminder_sent', '<', Carbon::now()->subDays(7));
            });
    }

    // Methods
    public function daysUntilExpiration(): int
    {
        return Carbon::today()->diffInDays($this->expiration_date, false);
    }

    public function isExpired(): bool
    {
        return $this->expiration_date->isPast();
    }

    public function shouldWarn(): bool
    {
        $daysUntil = $this->daysUntilExpiration();
        return $daysUntil <= $this->warning_days && $daysUntil >= 0;
    }

    public function createReminderEvent(): CalendarEvent
    {
        return CalendarEvent::create([
            'title' => $this->getReminderTitle(),
            'description' => $this->getReminderDescription(),
            'event_type' => 'expiration',
            'start_date' => $this->expiration_date,
            'is_all_day' => true,
            'color_code' => $this->getExpirationColorCode(),
            'related_model_type' => get_class($this),
            'related_model_id' => $this->id,
            'created_by' => auth()->id() ?? 1, // fallback to system user
        ]);
    }

    public function markReminderSent(): void
    {
        $this->update(['last_reminder_sent' => Carbon::now()]);
    }

    public function renew(Carbon $newExpirationDate, string $notes = null): void
    {
        $this->update([
            'expiration_date' => $newExpirationDate,
            'status' => 'renewed',
            'notes' => $notes,
            'last_reminder_sent' => null,
        ]);
    }

    public function extend(int $days, string $notes = null): void
    {
        $newDate = $this->expiration_date->addDays($days);
        $this->update([
            'expiration_date' => $newDate,
            'status' => 'extended',
            'notes' => $notes,
        ]);
    }

    private function getReminderTitle(): string
    {
        $modelName = class_basename($this->model_type);
        return match($this->expiration_type) {
            'lease_end' => "Lease Expiring Soon",
            'officer_term' => "Officer Term Ending",
            'department_closure' => "Department Closure",
            'contract_end' => "Contract Expiring",
            'license_expiry' => "License Expiring",
            default => "{$modelName} Expiring",
        };
    }

    private function getReminderDescription(): string
    {
        $daysUntil = $this->daysUntilExpiration();
        $modelName = class_basename($this->model_type);

        if ($daysUntil > 0) {
            return "{$modelName} (ID: {$this->model_id}) expires in {$daysUntil} days.";
        } elseif ($daysUntil === 0) {
            return "{$modelName} (ID: {$this->model_id}) expires today!";
        } else {
            return "{$modelName} (ID: {$this->model_id}) expired " . abs($daysUntil) . " days ago.";
        }
    }

    private function getExpirationColorCode(): string
    {
        $daysUntil = $this->daysUntilExpiration();

        if ($daysUntil < 0) {
            return '#dc3545'; // red - expired
        } elseif ($daysUntil <= 7) {
            return '#fd7e14'; // orange - critical
        } elseif ($daysUntil <= 30) {
            return '#ffc107'; // yellow - warning
        } else {
            return '#28a745'; // green - ok
        }
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'badge-success',
            'expired' => 'badge-danger',
            'renewed' => 'badge-info',
            'extended' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
