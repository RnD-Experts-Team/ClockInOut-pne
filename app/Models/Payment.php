<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'date',
        'what_got_fixed',
        'company_id',
        'cost',
        'notes',
        'paid',
        'payment_method',
        'maintenance_type',
    ];

    protected $casts = [
        'date' => 'date',
        'cost' => 'decimal:2',
        'paid' => 'boolean'
    ];

    protected $appends = [
        'week',
        'month',
        'this_month',
        'within_90_days',
        'within_4_weeks',
        'this_week',
        'within_1_year',
        'year'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('paid', false);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByMaintenanceType($query, $type)
    {
        return $query->where('maintenance_type', $type);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedCostAttribute()
    {
        return '$' . number_format($this->cost, 2);
    }

    public function getStatusLabelAttribute()
    {
        return $this->paid ? 'Paid' : 'Unpaid';
    }



    // Calculated attributes
    public function getWeekAttribute()
    {
        return $this->date->weekOfYear;
    }

    public function getMonthAttribute()
    {
        return $this->date->month;
    }

    public function getYearAttribute()
    {
        return $this->date?->year ?? null;
    }


    public function getThisMonthAttribute()
    {
        return $this->date->isCurrentMonth() ? 'This Month' : 'Not this Month';
    }

    public function getWithin90DaysAttribute()
    {
        return $this->date->diffInDays(now()) <= 90 ? 'Within 90 days' : 'Not within 90 days';
    }

    public function getWithin4WeeksAttribute()
    {
        return $this->date->diffInWeeks(now()) <= 4 ? 'Within 4 weeks' : 'Not within 4 weeks';
    }

    public function getThisWeekAttribute()
    {
        return $this->date->isCurrentWeek() ? 'Current Week' : 'Not Current Week';
    }

    public function getWithin1YearAttribute()
    {
        return $this->date->diffInMonths(now()) <= 12 ? 'Within 1 Year' : 'Not Within 1 Year';
    }

    // Scopes for filtering
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }

    public function scopeWithin90Days($query)
    {
        return $query->where('date', '>=', now()->subDays(90));
    }

    public function scopeWithin4Weeks($query)
    {
        return $query->where('date', '>=', now()->subWeeks(4));
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeWithin1Year($query)
    {
        return $query->where('date', '>=', now()->subYear());
    }

}
