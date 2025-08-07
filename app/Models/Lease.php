<?php
// app/Models/Lease.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_number',
        'name',
        'store_address',
        'aws',
        'base_rent',
        'percent_increase_per_year',
        'cam',
        'insurance',
        're_taxes',
        'others',
        'security_deposit',
        'franchise_agreement_expiration_date',
        'renewal_options',
        'initial_lease_expiration_date',
        'sqf',
        'hvac',
        'landlord_responsibility',
        'landlord_name',
        'landlord_email',
        'landlord_phone',
        'landlord_address',
        'comments'
    ];

    protected $casts = [
        'aws' => 'decimal:2',
        'base_rent' => 'decimal:2',
        'percent_increase_per_year' => 'decimal:2',
        'cam' => 'decimal:2',
        'insurance' => 'decimal:2',
        're_taxes' => 'decimal:2',
        'others' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'franchise_agreement_expiration_date' => 'date',
        'initial_lease_expiration_date' => 'date',
        'sqf' => 'integer',
        'hvac' => 'boolean'
    ];

    // Calculate total monthly rent
    protected function totalRent(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = 0;
                $total += $this->base_rent ?? 0;
                $total += $this->cam ?? 0;
                $total += $this->insurance ?? 0;
                $total += $this->re_taxes ?? 0;
                $total += $this->others ?? 0;
                return $total;
            }
        );
    }

    // Parse renewal options and return term details
    protected function parsedRenewalOptions(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->renewal_options) {
                    return null;
                }
                
                $parts = explode(',', $this->renewal_options);
                if (count($parts) === 2) {
                    return [
                        'terms' => (int) trim($parts[0]),
                        'years_per_term' => (int) trim($parts[1])
                    ];
                }
                
                return null;
            }
        );
    }

    // Calculate all term expiration dates
    protected function termExpirationDates(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->initial_lease_expiration_date || !$this->parsed_renewal_options) {
                    return collect([]);
                }

                $dates = collect([]);
                $currentDate = $this->initial_lease_expiration_date->copy();
                $renewalOptions = $this->parsed_renewal_options;
                
                // Add initial lease expiration
                $dates->push([
                    'term' => 'Initial Term',
                    'expiration_date' => $currentDate->copy(),
                    'is_initial' => true
                ]);

                // Add renewal terms
                for ($i = 1; $i <= $renewalOptions['terms']; $i++) {
                    $currentDate->addYears($renewalOptions['years_per_term']);
                    $dates->push([
                        'term' => "Renewal Term {$i}",
                        'expiration_date' => $currentDate->copy(),
                        'is_initial' => false
                    ]);
                }

                return $dates;
            }
        );
    }

    // Get current term information
    protected function currentTermInfo(): Attribute
    {
        return Attribute::make(
            get: function () {
                $now = Carbon::now();
                $termDates = $this->term_expiration_dates;
                
                foreach ($termDates as $index => $term) {
                    if ($now->lte($term['expiration_date'])) {
                        return [
                            'term_name' => $term['term'],
                            'expiration_date' => $term['expiration_date'],
                            'time_left' => $this->formatTimeDifference($now, $term['expiration_date'])
                        ];
                    }
                }
                
                return null;
            }
        );
    }

    // Get last term expiration date
    protected function lastTermExpirationDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                $termDates = $this->term_expiration_dates;
                if ($termDates->isEmpty()) {
                    return null;
                }
                
                return $termDates->last()['expiration_date'];
            }
        );
    }

    // Time left until last term ends
    protected function timeUntilLastTermEnds(): Attribute
    {
        return Attribute::make(
            get: function () {
                $lastDate = $this->last_term_expiration_date;
                if (!$lastDate) {
                    return null;
                }
                
                return $this->formatTimeDifference(Carbon::now(), $lastDate);
            }
        );
    }

    // Lease to sales ratio calculation
    protected function leaseToSalesRatio(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->aws || $this->aws == 0) {
                    return null;
                }
                
                $annualSales = $this->aws * 4; // AWS * 4 quarters
                $annualRent = $this->total_rent * 12; // Monthly rent * 12 months
                
                return $annualRent / $annualSales;
            }
        );
    }

    // Time left until franchise agreement expires
    protected function timeUntilFranchiseExpires(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->franchise_agreement_expiration_date) {
                    return null;
                }
                
                return $this->formatTimeDifference(Carbon::now(), $this->franchise_agreement_expiration_date);
            }
        );
    }

    // Helper method to format time difference
    private function formatTimeDifference(Carbon $start, Carbon $end): array
    {
        if ($start->gt($end)) {
            return ['expired' => true, 'years' => 0, 'months' => 0, 'days' => 0];
        }

        $diff = $start->diff($end);
        
        return [
            'expired' => false,
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'formatted' => $this->formatTimeString($diff->y, $diff->m, $diff->d)
        ];
    }

    // Format time string
    private function formatTimeString(int $years, int $months, int $days): string
    {
        $parts = [];
        
        if ($years > 0) {
            $parts[] = $years . ' year' . ($years != 1 ? 's' : '');
        }
        if ($months > 0) {
            $parts[] = $months . ' month' . ($months != 1 ? 's' : '');
        }
        if ($days > 0) {
            $parts[] = $days . ' day' . ($days != 1 ? 's' : '');
        }
        
        if (empty($parts)) {
            return 'Expired';
        }
        
        return implode(', ', $parts);
    }

    // Static method to get overall statistics
    public static function getOverallStatistics(): array
    {
        $leases = self::all();
        
        $totals = [
            'aws' => $leases->sum('aws'),
            'total_rent' => $leases->sum(fn($lease) => $lease->total_rent),
            'base_rent' => $leases->sum('base_rent'),
            'percent_increase_per_year' => $leases->sum('percent_increase_per_year'),
            'insurance' => $leases->sum('insurance'),
            'cam' => $leases->sum('cam'),
            're_taxes' => $leases->sum('re_taxes'),
            'others' => $leases->sum('others'),
            'security_deposit' => $leases->sum('security_deposit')
        ];

        $leasesWithData = $leases->filter(fn($lease) => $lease->aws > 0);
        $leasesWithRent = $leases->filter(fn($lease) => $lease->total_rent > 0);
        $leasesWithRatio = $leases->filter(fn($lease) => $lease->lease_to_sales_ratio !== null);

        $averages = [
            'aws' => $leasesWithData->count() > 0 ? $leasesWithData->avg('aws') : 0,
            'total_rent' => $leasesWithRent->count() > 0 ? $leasesWithRent->avg(fn($lease) => $lease->total_rent) : 0,
            'lease_to_sales_ratio' => $leasesWithRatio->count() > 0 ? $leasesWithRatio->avg(fn($lease) => $lease->lease_to_sales_ratio) : 0
        ];

        // Calculate total lease to sales ratio
        $totalLeaseToSalesRatio = $totals['aws'] > 0 ? ($totals['total_rent'] * 12) / ($totals['aws'] * 4) : 0;

        return [
            'totals' => array_merge($totals, ['lease_to_sales_ratio' => $totalLeaseToSalesRatio]),
            'averages' => $averages,
            'count' => $leases->count()
        ];
    }

    // Existing scopes and methods...
    public function scopeExpiringFranchiseSoon($query)
    {
        return $query->whereNotNull('franchise_agreement_expiration_date')
                    ->where('franchise_agreement_expiration_date', '<=', now()->addMonths(6));
    }

    public function scopeExpiringLeaseSoon($query)
    {
        return $query->whereNotNull('initial_lease_expiration_date')
                    ->where('initial_lease_expiration_date', '<=', now()->addMonths(6));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('store_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('store_address', 'like', "%{$search}%")
                    ->orWhere('landlord_name', 'like', "%{$search}%");
    }
    public static function getScopedStatistics(array $storeNumbers = []): array
{
    $query = self::query();
    
    // If specific stores are provided, filter by them
    if (!empty($storeNumbers)) {
        $query->whereIn('store_number', $storeNumbers);
    }
    
    $leases = $query->get();
    
    $totals = [
        'aws' => $leases->sum('aws'),
        'total_rent' => $leases->sum(fn($lease) => $lease->total_rent),
        'base_rent' => $leases->sum('base_rent'),
        'percent_increase_per_year' => $leases->sum('percent_increase_per_year'),
        'insurance' => $leases->sum('insurance'),
        'cam' => $leases->sum('cam'),
        're_taxes' => $leases->sum('re_taxes'),
        'others' => $leases->sum('others'),
        'security_deposit' => $leases->sum('security_deposit')
    ];

    $leasesWithData = $leases->filter(fn($lease) => $lease->aws > 0);
    $leasesWithRent = $leases->filter(fn($lease) => $lease->total_rent > 0);
    $leasesWithRatio = $leases->filter(fn($lease) => $lease->lease_to_sales_ratio !== null);

    $averages = [
        'aws' => $leasesWithData->count() > 0 ? $leasesWithData->avg('aws') : 0,
        'total_rent' => $leasesWithRent->count() > 0 ? $leasesWithRent->avg(fn($lease) => $lease->total_rent) : 0,
        'lease_to_sales_ratio' => $leasesWithRatio->count() > 0 ? $leasesWithRatio->avg(fn($lease) => $lease->lease_to_sales_ratio) : 0
    ];

    // Calculate total lease to sales ratio
    $totalLeaseToSalesRatio = $totals['aws'] > 0 ? ($totals['total_rent'] * 12) / ($totals['aws'] * 4) : 0;

    return [
        'totals' => array_merge($totals, ['lease_to_sales_ratio' => $totalLeaseToSalesRatio]),
        'averages' => $averages,
        'count' => $leases->count(),
        'selected_stores' => $storeNumbers
    ];
}

// Add method to get all available store numbers for dropdown
public static function getAllStoreNumbers(): array
{
    return self::whereNotNull('store_number')
               ->distinct()
               ->orderBy('store_number')
               ->pluck('store_number')
               ->toArray();
}
}
