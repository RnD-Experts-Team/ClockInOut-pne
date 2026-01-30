<?php

namespace Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Store;
use App\Models\User;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'invoice_card_id',
        'store_id',
        'user_id',
        'period_start',
        'period_end',
        'labor_hours',
        'labor_cost',
        'materials_cost',
        'equipment_cost',
        'total_miles',
        'total_distance_miles',
        'driving_time_hours',
        'driving_time_payment',
        'mileage_cost',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'status',
        'image_path',
        'pdf_path',
        'sent_at',
        'sent_to_email',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'labor_hours' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'materials_cost' => 'decimal:2',
        'equipment_cost' => 'decimal:2',
        'total_miles' => 'decimal:2',
        'total_distance_miles' => 'decimal:2',
        'driving_time_hours' => 'decimal:2',
        'driving_time_payment' => 'decimal:2',
        'mileage_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the invoice card
     */
    public function invoiceCard(): BelongsTo
    {
        return $this->belongsTo(InvoiceCard::class);
    }

    /**
     * Get the store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the technician/user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if invoice has been sent
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if invoice is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get formatted invoice number
     */
    public function getFormattedNumberAttribute(): string
    {
        return $this->invoice_number;
    }

    /**
     * Get period display
     */
    public function getPeriodDisplayAttribute(): string
    {
        return $this->period_start->format('M d') . ' - ' . $this->period_end->format('M d, Y');
    }

    /**
     * Get days in period
     */
    public function getDaysInPeriodAttribute(): int
    {
        return $this->period_start->diffInDays($this->period_end) + 1;
    }
}
