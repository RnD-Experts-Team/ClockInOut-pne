<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = ['key', 'value'];
    
    // Optionally, add a method to retrieve gas payment rate
    public static function getGasPaymentRate()
    {
        $config = self::where('key', 'gas_payment_rate')->first();
        return $config ? (float) $config->value : 10;  // Default to $10 if not set
    }
}
