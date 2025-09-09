<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'shift_type',
        'role',
        'color',
        'assignment_notes'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ✅ Fix the getDurationHoursAttribute method with proper error handling
    public function getDurationHoursAttribute()
    {
        try {
            // Validate that both start_time and end_time exist
            if (empty($this->start_time) || empty($this->end_time)) {
                return 0;
            }

            // Parse times using Carbon with explicit format
            $startTime = Carbon::createFromFormat('H:i:s', $this->start_time);
            $endTime = Carbon::createFromFormat('H:i:s', $this->end_time);

            // Handle cases where end time might be next day
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }

            return $endTime->diffInHours($startTime, true);

        } catch (\Exception $e) {
            // Log the error and return default value
            Log::warning("Failed to calculate duration for shift {$this->id}: " . $e->getMessage());
            return 0;
        }
    }

    // ✅ Add relationships
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'schedule_shift_id');
    }
}
