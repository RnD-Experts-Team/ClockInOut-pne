<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DailyClockEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'event_timestamp',
        'location',
        'ip_address',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'event_timestamp' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('event_timestamp', $date);
    }

    public function scopeByEventType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('event_timestamp', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('event_timestamp', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeClockIns($query)
    {
        return $query->where('event_type', 'clock_in');
    }

    public function scopeClockOuts($query)
    {
        return $query->where('event_type', 'clock_out');
    }

    // Methods
    public function getFormattedTime(): string
    {
        return $this->event_timestamp->format('g:i A');
    }

    public function getFormattedDate(): string
    {
        return $this->event_timestamp->format('M j, Y');
    }

    public function getFormattedDateTime(): string
    {
        return $this->event_timestamp->format('M j, Y g:i A');
    }

    public function isClockIn(): bool
    {
        return $this->event_type === 'clock_in';
    }

    public function isClockOut(): bool
    {
        return $this->event_type === 'clock_out';
    }

    public function isBreak(): bool
    {
        return in_array($this->event_type, ['break_start', 'break_end']);
    }

    public function getEventTypeLabel(): string
    {
        return match($this->event_type) {
            'clock_in' => 'Clock In',
            'clock_out' => 'Clock Out',
            'break_start' => 'Break Start',
            'break_end' => 'Break End',
            default => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }

    public function getEventTypeIcon(): string
    {
        return match($this->event_type) {
            'clock_in' => 'fas fa-sign-in-alt text-success',
            'clock_out' => 'fas fa-sign-out-alt text-danger',
            'break_start' => 'fas fa-pause-circle text-warning',
            'break_end' => 'fas fa-play-circle text-info',
            default => 'fas fa-clock text-secondary',
        };
    }

    public function getEventTypeColor(): string
    {
        return match($this->event_type) {
            'clock_in' => '#28a745',   // green
            'clock_out' => '#dc3545',  // red
            'break_start' => '#ffc107', // yellow
            'break_end' => '#17a2b8',   // blue
            default => '#6c757d',       // gray
        };
    }

    public function hasLocation(): bool
    {
        return $this->latitude && $this->longitude;
    }

    public function getLocationString(): string
    {
        if ($this->hasLocation()) {
            return "{$this->latitude}, {$this->longitude}";
        }
        return $this->location ?? 'Unknown';
    }

    public function createCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::create([
            'title' => $this->user->name . ' - ' . $this->getEventTypeLabel(),
            'description' => "User: {$this->user->name}\nEvent: {$this->getEventTypeLabel()}\nLocation: {$this->getLocationString()}",
            'event_type' => 'clock_event',
            'start_date' => $this->event_timestamp->toDateString(),
            'start_time' => $this->event_timestamp->format('H:i:s'),
            'is_all_day' => false,
            'color_code' => $this->getEventTypeColor(),
            'related_model_type' => get_class($this),
            'related_model_id' => $this->id,
            'created_by' => $this->user_id,
        ]);
    }

    // Get work hours for a specific user on a specific date
    public static function calculateWorkHours($userId, $date): float
    {
        $events = self::byUser($userId)
            ->byDate($date)
            ->orderBy('event_timestamp')
            ->get();

        $totalHours = 0;
        $clockIn = null;

        foreach ($events as $event) {
            if ($event->event_type === 'clock_in') {
                $clockIn = $event->event_timestamp;
            } elseif ($event->event_type === 'clock_out' && $clockIn) {
                $totalHours += $clockIn->diffInHours($event->event_timestamp, true);
                $clockIn = null;
            }
        }

        return round($totalHours, 2);
    }
}
