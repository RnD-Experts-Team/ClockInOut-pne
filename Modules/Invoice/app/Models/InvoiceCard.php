<?php

namespace Modules\Invoice\Models;

use App\Models\Clocking;
use App\Models\Store;
use App\Models\User;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InvoiceCard extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Invoice\Database\Factories\InvoiceCardFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'clocking_id',
        'store_id',
        'user_id',
        'start_time',
        'end_time',
        'arrival_odometer',
        'arrival_odometer_image',
        'calculated_miles',
        'driving_time_hours',
        'driving_time_payment',
        'allocated_return_miles',
        'total_miles',
        'mileage_payment',
        'labor_hours',
        'labor_cost',
        'materials_cost',
        'total_cost',
        'status',
        'notes',
        'not_done_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_time'            => 'datetime',
        'end_time'              => 'datetime',
        'arrival_odometer'      => 'decimal:2',
        'calculated_miles'      => 'decimal:2',
        'driving_time_hours'    => 'decimal:2',
        'driving_time_payment'  => 'decimal:2',
        'allocated_return_miles' => 'decimal:2',
        'total_miles'           => 'decimal:2',
        'mileage_payment'       => 'decimal:2',
        'labor_hours'           => 'decimal:2',
        'labor_cost'            => 'decimal:2',
        'materials_cost'        => 'decimal:2',
        'total_cost'            => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function clocking(): BelongsTo
    {
        return $this->belongsTo(Clocking::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(InvoiceCardMaterial::class);
    }

    public function maintenanceRequests(): BelongsToMany
    {
        return $this->belongsToMany(MaintenanceRequest::class, 'invoice_card_maintenance_requests')
            ->withPivot('status', 'notes', 'task_status', 'completed_at')
            ->withTimestamps();
    }

    /**
     * Access pivot model records directly as tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(InvoiceCardTask::class, 'invoice_card_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'id', 'invoice_card_id');
    }

    /**
     * Business Logic Methods
     */
    public function calculateLaborCost(): void
    {
        if ($this->start_time && $this->end_time && $this->user) {
            $diffInSeconds = $this->end_time->timestamp - $this->start_time->timestamp;
            $this->labor_hours = $diffInSeconds / 3600;
            
            // Include accumulated labor hours from previous sessions
            $totalLaborHours = $this->labor_hours + ($this->accumulated_labor_hours ?? 0);
            $this->labor_cost = $totalLaborHours * $this->user->hourly_pay;
            
            $this->save();
        }
    }

    public function calculateMaterialsCost(): void
    {
        $this->materials_cost = $this->materials()->sum('cost');
        $this->save();
    }

    public function calculateMileagePayment(float $mileRate): void
    {
        // Calculate total miles from calculated miles and allocated return miles
        $this->total_miles = ($this->calculated_miles ?? 0) + ($this->allocated_return_miles ?? 0);
        
        if ($this->total_miles > 0) {
            $this->mileage_payment = $this->total_miles * $mileRate;
        }
        
        $this->save();
    }

    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->labor_cost + $this->materials_cost + $this->mileage_payment;
        $this->save();
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForClocking($query, $clockingId)
    {
        return $query->where('clocking_id', $clockingId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('start_time', [$from, $to]);
    }
}
