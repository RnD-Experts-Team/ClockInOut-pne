<?php

namespace Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceCardMaterial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_card_id',
        'maintenance_request_id',
        'item_name',
        'cost',
        'receipt_photos',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'cost' => 'decimal:2',
        'receipt_photos' => 'array',
    ];

    /**
     * Relationships
     */
    public function invoiceCard(): BelongsTo
    {
        return $this->belongsTo(InvoiceCard::class);
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MaintenanceRequest::class);
    }
}
