<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_all_day',
        'color_code',
        'related_model_type',
        'related_model_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_all_day' => 'boolean',

    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function relatedModel(): MorphTo
    {
        return $this->morphTo('related_model');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(CalendarReminder::class);
    }

    // Scopes
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_date', Carbon::today());
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('start_date', [
            Carbon::today(),
            Carbon::today()->addDays($days)
        ]);
    }

    // Methods
    public function isExpired(): bool
    {
        $endDate = $this->end_date ?? $this->start_date;
        return Carbon::parse($endDate)->isPast();
    }

    public function getColorCode(): string
    {
        if ($this->color_code) {
            return $this->color_code;
        }

        return match($this->event_type) {
            'maintenance_request' => '#dc3545', // red
            'admin_action' => '#28a745',        // green
            'clock_event' => '#17a2b8',         // blue
            'reminder' => '#ffc107',            // yellow
            'expiration' => '#fd7e14',          // orange
            default => '#6c757d',               // gray
        };
    }

// In your CalendarEvent model
    /**
     * Format event for calendar display
     */
    public function formatForCalendar(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->is_all_day ?
                $this->start_date :
                $this->start_date . 'T' . ($this->start_time ?? '00:00:00'),
            'end' => $this->end_date ?
                ($this->is_all_day ? $this->end_date : $this->end_date . 'T' . ($this->end_time ?? '23:59:59')) :
                null,
            'allDay' => $this->is_all_day ?? true,
            'color' => $this->color_code ?? '#667eea',
            'textColor' => '#ffffff',
            'description' => $this->description ?? '',
            'eventType' => $this->event_type ?? 'custom',
            'className' => 'event-' . ($this->event_type ?? 'custom'),
            'extendedProps' => [
                'description' => $this->description ?? '',
                'event_type' => $this->event_type ?? 'custom',
                'related_model_type' => $this->related_model_type,
                'related_model_id' => $this->related_model_id,
            ]
        ];
    }

    public function getDurationInMinutes(): int
    {
        if ($this->is_all_day) {
            return 24 * 60; // 1440 minutes
        }

        if ($this->start_time && $this->end_time) {
            return Carbon::parse($this->end_time)->diffInMinutes(Carbon::parse($this->start_time));
        }

        return 60; // default 1 hour
    }



}
