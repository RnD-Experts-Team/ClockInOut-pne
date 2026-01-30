<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clocking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'miles_in',
        'miles_out',
        'total_session_miles',
        'image_in',
        'image_out',
        'is_clocked_in',
        'using_car',
        'gas_payment',
        'total_salary',
        // Purchase fields
        'bought_something',
        'purchase_cost',
        'purchase_receipt',
    ];

    protected $casts = [
        'using_car'           => 'boolean',
        'is_clocked_in'       => 'boolean',
        'gas_payment'         => 'decimal:2',
        'total_salary'        => 'decimal:2',
        'clock_in'            => 'datetime',
        'clock_out'           => 'datetime',
        'bought_something'    => 'boolean',
        'purchase_cost'       => 'decimal:2',
    ];

    /**
     * Get the user that owns the clocking.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the invoice cards for this clocking session.
     */
    public function invoiceCards()
    {
        return $this->hasMany(\Modules\Invoice\Models\InvoiceCard::class);
    }

    /**
     * Payments linked to this clocking (purchases via clocking system)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'clocking_id');
    }
}
