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
        'image_in',
        'image_out',
        'is_clocked_in',
        'using_car',
        'bought_something',
        'purchase_cost',
        'purchase_receipt',
        'gas_payment',     // Add this new field
        'total_salary'     // Add this new field
    ];
    
    protected $casts = [
        'using_car'        => 'boolean',
        'is_clocked_in'    => 'boolean',
        'bought_something' => 'boolean',
        'purchase_cost'    => 'decimal:2',
        'gas_payment'      => 'decimal:2',  // Add this new field
        'total_salary'     => 'decimal:2',  // Add this new field
        'clock_in'         => 'datetime',
        'clock_out'        => 'datetime',
    ];

    /**
     * Get the user that owns the clocking.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
