<?php

namespace App\Models\Native;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NativeUrgencyLevel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'native_urgency_levels';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'level',
        'color',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'level' => 'integer',
    ];

    /**
     * Get all native requests with this urgency level.
     */
    public function nativeRequests(): HasMany
    {
        return $this->hasMany(NativeRequest::class, 'urgency_level_id');
    }
}
