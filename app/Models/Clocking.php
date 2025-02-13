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
        'image_in',  // Add this to the fillable array
        'image_out', // Add this to the fillable array
        'is_clocked_in',
    ];

    /**
     * Get the user that owns the clocking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
