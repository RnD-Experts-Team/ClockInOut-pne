<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'store_id',
        'type',
        'serial_number',
        'model',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Global equipment has no store (store_id = null).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('store_id');
    }

    /**
     * Equipment belonging to a specific store OR global.
     */
    public function scopeForStore($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->where('store_id', $storeId)->orWhereNull('store_id');
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return the "Others" global catch-all record, or null if not seeded yet.
     */
    public static function getOthers(): ?self
    {
        return static::whereNull('store_id')
            ->where('name', 'Others')
            ->first();
    }

    /**
     * Try to match an equipment name (case-insensitive) and return the Equipment id.
     * Falls back to the "Others" record if no match found.
     */
    public static function matchByName(string $name): ?int
    {
        $match = static::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();

        if ($match) {
            return $match->id;
        }

        $others = static::getOthers();

        return $others?->id;
    }
}
