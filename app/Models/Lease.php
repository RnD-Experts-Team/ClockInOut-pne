<?php
// app/Models/Lease.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
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
        'comments',
        'current_term',
        'renewal_date',
        'renewal_reminder_sent',
        'renewal_notes',
        'renewal_status',
        'renewal_created_by',
        'renewal_reminder_sent_at',
        'renewal_completed_at',
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
        'current_term' => 'integer',
        'hvac' => 'boolean',
        'renewal_date' => 'date',
        'renewal_reminder_sent' => 'boolean',
        'renewal_reminder_sent_at' => 'datetime',
        'renewal_completed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Scopes
    public function scopeExpiringFranchise($query, $months = 6)
    {
        return $query->where('franchise_agreement_expiration_date', '<=', now()->addMonths($months));
    }

    public function scopeExpiringLease($query, $months = 6)
    {
        return $query->where('initial_lease_expiration_date', '<=', now()->addMonths($months));
    }

    // Accessors
    public function getTotalMonthlyCostAttribute()
    {
        return ($this->base_rent ?? 0) +
            ($this->cam ?? 0) +
            ($this->insurance ?? 0) +
            ($this->re_taxes ?? 0) +
            ($this->others ?? 0);
    }

    public function getFormattedTotalMonthlyCostAttribute()
    {
        return '$' . number_format($this->total_monthly_cost, 2);
    }

    public function getDaysUntilFranchiseExpirationAttribute()
    {
        return $this->franchise_agreement_expiration_date
            ? now()->diffInDays($this->franchise_agreement_expiration_date, false)
            : null;
    }

    public function getDaysUntilLeaseExpirationAttribute()
    {
        return $this->initial_lease_expiration_date
            ? now()->diffInDays($this->initial_lease_expiration_date, false)
            : null;
    }
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
                $termDates = $this->term_expiration_dates;

                // If manual current_term is set, use it
                if ($this->current_term && $this->current_term > 0) {
                    $termIndex = $this->current_term - 1; // Convert to 0-based index

                    if (isset($termDates[$termIndex])) {
                        $term = $termDates[$termIndex];
                        $now = Carbon::now();

                        return [
                            'term_name' => $term['term'],
                            'term_number' => $this->current_term,
                            'expiration_date' => $term['expiration_date'],
                            'time_left' => $this->formatTimeDifference($now, $term['expiration_date']),
                            'is_manual' => true // Flag to indicate this was manually set
                        ];
                    }
                }

                // Fall back to automatic date-based calculation
                $now = Carbon::now();
                foreach ($termDates as $index => $term) {
                    if ($now->lte($term['expiration_date'])) {
                        return [
                            'term_name' => $term['term'],
                            'term_number' => $index + 1,
                            'expiration_date' => $term['expiration_date'],
                            'time_left' => $this->formatTimeDifference($now, $term['expiration_date']),
                            'is_manual' => false // Flag to indicate this was automatically calculated
                        ];
                    }
                }

                return null;
            }
        );
    }
    public function getAvailableTermsAttribute(): array
    {
        $termDates = $this->term_expiration_dates;
        $options = [];

        foreach ($termDates as $index => $term) {
            $options[] = [
                'value' => $index + 1,
                'label' => $term['term'],
                'expiration_date' => $term['expiration_date']->format('Y-m-d')
            ];
        }

        return $options;
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
                $annualSales = $this->aws * 4; // AWS * 4 quarters = annual sales
                $annualRent = $this->total_rent ;

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
        $totalLeaseToSalesRatio = $totals['aws'] > 0 ? ($totals['total_rent'] ) / ($totals['aws'] * 4) : 0;

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
    $query = self::with('store');

    if (!empty($storeNumbers) && count($storeNumbers) > 0) {
        $storeNumbers = array_map('intval', array_filter($storeNumbers));


        // Changed from store_number to store_id
        $query->whereIn('store_id', $storeNumbers);
    }

    $leases = $query->get();

    // Debug the final result
    Log::info('Final Query SQL:', [$query->toSql()]);
    Log::info('Leases Found Count:', [$leases->count()]);

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
    $leasesWithBaseRent = $leases->filter(fn($lease) => $lease->base_rent > 0);
    $leasesWithPercentIncrease = $leases->filter(fn($lease) => $lease->percent_increase_per_year > 0);
    $leasesWithInsurance = $leases->filter(fn($lease) => $lease->insurance > 0);
    $leasesWithCAM = $leases->filter(fn($lease) => $lease->cam > 0);
    $leasesWithRETaxes = $leases->filter(fn($lease) => $lease->re_taxes > 0);
    $leasesWithOthers = $leases->filter(fn($lease) => $lease->others > 0);
    $leasesWithSecurityDeposit = $leases->filter(fn($lease) => $lease->security_deposit > 0);

    $averages = [
        'aws' => $leasesWithData->count() > 0 ? $leasesWithData->avg('aws') : 0,
        'total_rent' => $leasesWithRent->count() > 0 ? $leasesWithRent->avg(fn($lease) => $lease->total_rent) : 0,
        'lease_to_sales_ratio' => $leasesWithRatio->count() > 0 ? $leasesWithRatio->avg(fn($lease) => $lease->lease_to_sales_ratio) : 0,
        'base_rent' => $leasesWithBaseRent->count() > 0 ? $leasesWithBaseRent->avg('base_rent') : 0,
        'percent_increase_per_year' => $leasesWithPercentIncrease->count() > 0 ? $leasesWithPercentIncrease->avg('percent_increase_per_year') : 0,
        'insurance' => $leasesWithInsurance->count() > 0 ? $leasesWithInsurance->avg('insurance') : 0,
        'cam' => $leasesWithCAM->count() > 0 ? $leasesWithCAM->avg('cam') : 0,
        're_taxes' => $leasesWithRETaxes->count() > 0 ? $leasesWithRETaxes->avg('re_taxes') : 0,
        'others' => $leasesWithOthers->count() > 0 ? $leasesWithOthers->avg('others') : 0,
        'security_deposit' => $leasesWithSecurityDeposit->count() > 0 ? $leasesWithSecurityDeposit->avg('security_deposit') : 0
    ];

    // Calculate total lease to sales ratio
    $totalLeaseToSalesRatio = $totals['aws'] > 0 ? ($totals['total_rent']) / ($totals['aws'] * 4) : 0;

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
// ADD THESE NEW RELATIONSHIPS (add after existing relationships):
    /**
     * Who created the renewal reminder
     */
    public function renewalCreatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renewal_created_by');
    }

// ADD THESE NEW SCOPES (add after existing scopes):
    /**
     * Scope for renewal reminders that haven't been sent
     */
    public function scopePendingRenewalReminders($query)
    {
        return $query->whereNotNull('renewal_date')
            ->where('renewal_reminder_sent', false)
            ->where('renewal_status', 'pending');
    }

    /**
     * Scope for renewals coming due soon
     */
    public function scopeRenewalsDueSoon($query, $days = 30)
    {
        return $query->whereNotNull('renewal_date')
            ->where('renewal_date', '<=', now()->addDays($days))
            ->where('renewal_status', 'pending');
    }

    /**
     * Scope for overdue renewals
     */
    public function scopeOverdueRenewals($query)
    {
        return $query->whereNotNull('renewal_date')
            ->where('renewal_date', '<', now())
            ->where('renewal_status', 'pending');
    }

// ADD THESE NEW ACCESSOR METHODS (add after existing accessors):
    /**
     * Get formatted renewal date
     */
    public function getFormattedRenewalDateAttribute()
    {
        if (!$this->renewal_date) {
            return 'No renewal date set';
        }

        try {
            return $this->renewal_date->format('M d, Y');
        } catch (\Exception $e) {
            return 'Invalid renewal date';
        }
    }

    /**
     * Get days until renewal
     */
    public function getDaysUntilRenewalAttribute()
    {
        if (!$this->renewal_date) {
            return null;
        }

        return now()->diffInDays($this->renewal_date, false);
    }

    /**
     * Check if renewal is overdue
     */
    public function getIsRenewalOverdueAttribute()
    {
        if (!$this->renewal_date) {
            return false;
        }

        return $this->renewal_date->isPast();
    }

    /**
     * Get renewal status with CSS classes
     */
    public function getRenewalStatusInfoAttribute()
    {
        if (!$this->renewal_date) {
            return [
                'status' => 'no_date',
                'message' => 'No renewal date',
                'class' => 'text-gray-400'
            ];
        }

        $daysUntilRenewal = $this->days_until_renewal;

        if ($this->renewal_status === 'completed') {
            return [
                'status' => 'completed',
                'message' => 'Renewal completed',
                'class' => 'bg-green-100 text-green-800'
            ];
        }

        if ($this->renewal_status === 'declined') {
            return [
                'status' => 'declined',
                'message' => 'Renewal declined',
                'class' => 'bg-red-100 text-red-800'
            ];
        }

        if ($daysUntilRenewal < 0) {
            return [
                'status' => 'overdue',
                'message' => 'Renewal overdue (' . abs($daysUntilRenewal) . ' days)',
                'class' => 'bg-red-100 text-red-800'
            ];
        } elseif ($daysUntilRenewal <= 7) {
            return [
                'status' => 'urgent',
                'message' => 'Renewal due in ' . $daysUntilRenewal . ' days',
                'class' => 'bg-yellow-100 text-yellow-800'
            ];
        } elseif ($daysUntilRenewal <= 30) {
            return [
                'status' => 'upcoming',
                'message' => 'Renewal due in ' . $daysUntilRenewal . ' days',
                'class' => 'bg-blue-100 text-blue-800'
            ];
        }

        return [
            'status' => 'scheduled',
            'message' => 'Renewal scheduled for ' . $this->formatted_renewal_date,
            'class' => 'text-gray-600'
        ];
    }

    /**
     * Create calendar event for renewal
     */
    public function createRenewalCalendarEvent()
    {
        if (!$this->renewal_date) {
            return null;
        }

        return \App\Models\CalendarEvent::create([
            'title' => 'Lease Renewal - Store ' . $this->store_number,
            'description' => 'Renewal for lease at ' . $this->store_address,
            'event_type' => 'lease_renewal',
            'start_date' => $this->renewal_date,
            'start_time' => '09:00:00',
            'is_all_day' => false,
            'color_code' => '#dc3545',
            'related_model_type' => self::class,
            'related_model_id' => $this->id,
            'created_by' => $this->renewal_created_by ?? auth()->id(),
        ]);
    }

    /**
     * Mark renewal reminder as sent
     */
    public function markRenewalReminderSent()
    {
        $this->update([
            'renewal_reminder_sent' => true,
            'renewal_reminder_sent_at' => now(),
        ]);
    }

    /**
     * Complete renewal process
     */
    public function completeRenewal(?string $notes = null)
    {
        $this->update([
            'renewal_status' => 'completed',
            'renewal_completed_at' => now(),
            'renewal_notes' => $notes ? ($this->renewal_notes ? $this->renewal_notes . "\n\n" . $notes : $notes) : $this->renewal_notes,
        ]);
    }





}
