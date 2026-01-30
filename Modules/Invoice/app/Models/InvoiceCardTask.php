<?php

namespace Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class InvoiceCardTask extends Model
{
    protected $table = 'invoice_card_maintenance_requests';

    protected $fillable = [
        'invoice_card_id',
        'maintenance_request_id',
        'task_status',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function invoiceCard(): BelongsTo
    {
        return $this->belongsTo(InvoiceCard::class, 'invoice_card_id');
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MaintenanceRequest::class, 'maintenance_request_id');
    }

    public function isCompleted(): bool
    {
        return $this->task_status === 'completed';
    }

    public function markComplete(): bool
    {
        $this->task_status = 'completed';
        $this->completed_at = now();
        return $this->save();
    }
}
