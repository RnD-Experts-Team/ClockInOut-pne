<?php
// app/Models/Store.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_number',
        'name',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function apartmentLeases(): HasMany
    {
        return $this->hasMany(ApartmentLease::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStoreNumber($query, $storeNumber)
    {
        return $query->where('store_number', $storeNumber);
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->name ?: $this->store_number;
    }

    /**
     * Get the users (store managers) assigned to this store.
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'store_manager', 'store_id', 'user_id')
                    ->withTimestamps()
                    ->withPivot('assigned_by', 'assigned_at');
    }
}
