<?php
// app/Models/Schedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'shift_type',
        'week_start_date',
        'role',
        'settings'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'settings' => 'array'
    ];

    public function shifts(): HasMany
    {
        return $this->hasMany(ScheduleShift::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForWeek($query, $startDate)
    {
        $endDate = Carbon::parse($startDate)->addDays(6);
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);
    }

    // Accessors
    public function getTotalShiftsAttribute()
    {
        return $this->shifts()->count();
    }

    public function getTotalHoursAttribute()
    {
        return $this->shifts()->get()->sum(function($shift) {
            return $shift->duration_hours;
        });
    }

    public function getWeekRangeAttribute()
    {
        return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
    }

    // Methods
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'published']);
    }

    public function publish(): bool
    {
        if ($this->status === 'draft') {
            $this->update(['status' => 'published']);
            // Here you could add notification logic
            return true;
        }
        return false;
    }

    public function activate(): bool
    {
        if ($this->status === 'published') {
            $this->update(['status' => 'active']);
            return true;
        }
        return false;
    }
}
