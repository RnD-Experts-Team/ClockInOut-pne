<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_global_store_manager',
        'hourly_pay',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_global_store_manager' => 'boolean',
    ];
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'assigned_user_id');
    }

    // ✅ Alternative: If you want maintenance requests directly through task assignments

    public function clockings()
{
    return $this->hasMany(Clocking::class, 'user_id');
}
    public function assignedMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }
    public function shifts()
    {
        return $this->hasMany(ScheduleShift::class);
    }
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class, 'changed_by_user_id');
    }

    /**
     * Get the stores this user manages (for store_manager role).
     */
    public function managedStores()
    {
        return $this->belongsToMany(Store::class, 'store_manager', 'user_id', 'store_id')
                    ->withTimestamps()
                    ->withPivot('assigned_by', 'assigned_at');
    }


}
