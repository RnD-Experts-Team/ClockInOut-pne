<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentLease extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'store_number',
        'apartment_address',
        'rent',
        'utilities',
        'number_of_AT',
        'has_car',
        'is_family',
        'expiration_date',
        'drive_time',
        'notes',
        'lease_holder',
        'created_by',
    ];

    // Add proper casting for your fields
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeExpiringSoon($query, $months = 3)
    {
        return $query->where('expiration_date', '<=', now()->addMonths($months));
    }

    public function scopeForFamilies($query)
    {
        return $query->where('is_family', 'yes');
    }

    public function scopeWithCar($query)
    {
        return $query->where('has_car', true);
    }

    // Accessors
    public function getTotalMonthlyCostAttribute()
    {
        return ($this->rent ?? 0) + ($this->utilities ?? 0);
    }

    public function getFormattedTotalMonthlyCostAttribute()
    {
        return '$' . number_format($this->total_monthly_cost, 2);
    }


    public function getIsFamilyBooleanAttribute()
    {
        return $this->is_family === 'yes';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    protected $casts = [
        'expiration_date' => 'date',
        'rent' => 'decimal:2',
        'utilities' => 'decimal:2',
        'has_car' => 'integer',
        'number_of_AT' => 'integer',
        'store_number' => 'integer',
    ];

    // Add these to appends if you want them always included in JSON
    protected $appends = ['total_rent', 'expiration_warning'];

    /**
     * Handle expiration_date with 'Z' suffix or other string formats
     * This fixes the "Call to a member function format() on string" error
     */
    public function getExpirationDateAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If it's already a Carbon instance, return it
        if ($value instanceof Carbon) {
            return $value;
        }

        // Handle string dates with 'Z' suffix or other formats
        try {
            // Remove 'Z' suffix if present and parse the date
            $cleanValue = str_replace('Z', '', $value);
            return Carbon::parse($cleanValue);
        } catch (\Exception $e) {
            // If parsing fails, return null
            return null;
        }
    }

    /**
     * Calculate total rent (rent + utilities)
     */
    public function getTotalRentAttribute()
    {
        $rent = $this->rent ?? 0;
        $utilities = $this->utilities ?? 0;
        return $rent + $utilities;
    }

    /**
     * Get expiration warning - shows if lease expires within a month
     */
    /**
     * Get expiration warning text for display
     */
    public function getExpirationWarningTextAttribute()
    {
        if (!$this->expiration_date) {
            return null;
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            $now = Carbon::now();
            $daysUntilExpiration = $now->diffInDays($expirationDate, false);

            if ($expirationDate->isPast()) {
                $daysAgo = abs($daysUntilExpiration);
                return "Expired {$daysAgo} day" . ($daysAgo > 1 ? 's' : '') . " ago";
            } elseif ($daysUntilExpiration <= 30) {
                return "Expires in {$daysUntilExpiration} day" . ($daysUntilExpiration > 1 ? 's' : '');
            }

            return null; // No warning needed
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get expiration warning CSS class
     */
    public function getExpirationWarningClassAttribute()
    {
        if (!$this->expiration_date) {
            return '';
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            $now = Carbon::now();
            $daysUntilExpiration = $now->diffInDays($expirationDate, false);

            if ($expirationDate->isPast()) {
                return 'bg-red-100 text-red-800';
            } elseif ($daysUntilExpiration <= 7) {
                return 'bg-red-100 text-red-800';
            } elseif ($daysUntilExpiration <= 30) {
                return 'bg-yellow-100 text-yellow-800';
            }

            return 'bg-green-100 text-green-800';
        } catch (\Exception $e) {
            return 'bg-gray-100 text-gray-800';
        }
    }

    /**
     * Get sort value for expiration warning
     */
    public function getExpirationWarningSortValueAttribute()
    {
        if (!$this->expiration_date) {
            return 999; // Put at end
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            return Carbon::now()->diffInDays($expirationDate, false);
        } catch (\Exception $e) {
            return 999;
        }
    }


    /**
     * Accessor for formatted family status
     */
    public function getIsFamilyFormattedAttribute()
    {
        return $this->is_family ? ucfirst(strtolower($this->is_family)) : 'No';
    }

    /**
     * Scope for active leases (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('expiration_date', '>', now())
            ->orWhereNull('expiration_date');
    }

    /**
     * Scope for leases expiring within a month
     */
    public function scopeExpiringWithinMonth($query)
    {
        return $query->where('expiration_date', '>=', now())
            ->where('expiration_date', '<=', now()->addMonth());
    }

    /**
     * Scope for leases with cars
     */
    public function scopeWithCars($query)
    {
        return $query->where('has_car', '>', 0);
    }

    /**
     * Scope for family apartments
     */
    public function scopeFamilies($query)
    {
        return $query->whereIn('is_family', ['Yes', 'yes', 'YES']);
    }

    /**
     * Calculate days until expiration
     */
    public function getDaysUntilExpirationAttribute()
    {
        if (!$this->expiration_date) {
            return null;
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            return $expirationDate->diffInDays(now(), false);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if lease is expired
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->expiration_date) {
            return false;
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            return $expirationDate->isPast();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get lease holders as array
     */
    public function getLeaseHoldersArrayAttribute()
    {
        if (!$this->lease_holder) {
            return [];
        }

        return array_map('trim', explode(',', $this->lease_holder));
    }

    /**
     * Get AT per rent ratio
     */
    public function getAtPerRentRatioAttribute()
    {
        if ($this->number_of_AT == 0 || $this->total_rent == 0) {
            return 0;
        }

        return round($this->total_rent / $this->number_of_AT, 2);
    }

    /**
     * Get formatted drive time
     */
    public function getFormattedDriveTimeAttribute()
    {
        return $this->drive_time ?? 'Not specified';
    }

    /**
     * Get months until expiration
     */
    public function getMonthsUntilExpirationAttribute()
    {
        if (!$this->expiration_date) {
            return null;
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            $months = Carbon::now()->diffInMonths($expirationDate, false);
            return $months >= 0 ? $months : 0;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mutator to clean date when saving
     */
    public function setExpirationDateAttribute($value)
    {
        if ($value) {
            try {
                // Remove 'Z' suffix if present and clean the date
                $cleanValue = str_replace('Z', '', $value);
                $this->attributes['expiration_date'] = Carbon::parse($cleanValue)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->attributes['expiration_date'] = null;
            }
        } else {
            $this->attributes['expiration_date'] = null;
        }
    }

    /**
     * Additional helper method to get formatted expiration date safely
     */
    public function getFormattedExpirationDateAttribute()
    {
        if (!$this->expiration_date) {
            return 'No expiration date';
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            return $expirationDate->format('M d, Y');
        } catch (\Exception $e) {
            return 'Invalid date';
        }
    }

    /**
     * Get expiration status with proper formatting
     */
    public function getExpirationStatusAttribute()
    {
        if (!$this->expiration_date) {
            return [
                'status' => 'no_date',
                'message' => 'No expiration date',
                'class' => 'text-gray-400'
            ];
        }

        try {
            $expirationDate = $this->expiration_date instanceof Carbon
                ? $this->expiration_date
                : Carbon::parse($this->expiration_date);

            $daysUntilExpiration = $expirationDate->diffInDays(now(), false);
            $isExpired = $expirationDate->isPast();

            if ($isExpired) {
                if ($daysUntilExpiration <= 30) {
                    return [
                        'status' => 'expired_recent',
                        'message' => 'Expired ' . abs($daysUntilExpiration) . ' day' . (abs($daysUntilExpiration) != 1 ? 's' : '') . ' ago',
                        'class' => 'bg-red-100 text-red-800'
                    ];
                } else {
                    return [
                        'status' => 'expired_old',
                        'message' => 'Expired',
                        'class' => 'bg-gray-100 text-gray-800'
                    ];
                }
            } else {
                if ($daysUntilExpiration <= 30) {
                    return [
                        'status' => 'expiring_soon',
                        'message' => 'Expires in ' . $daysUntilExpiration . ' day' . ($daysUntilExpiration != 1 ? 's' : ''),
                        'class' => 'bg-yellow-100 text-yellow-800'
                    ];
                } elseif ($daysUntilExpiration <= 90) {
                    return [
                        'status' => 'expiring_later',
                        'message' => 'Expires ' . $expirationDate->format('M d, Y'),
                        'class' => 'bg-blue-100 text-blue-800'
                    ];
                } else {
                    return [
                        'status' => 'active',
                        'message' => $expirationDate->format('M d, Y'),
                        'class' => 'text-gray-400'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'status' => 'invalid',
                'message' => 'Invalid date format',
                'class' => 'text-red-400'
            ];
        }
    }
}
