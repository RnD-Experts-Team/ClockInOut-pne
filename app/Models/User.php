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
        'hourly_pay',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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



}
