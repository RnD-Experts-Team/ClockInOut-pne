<?php

namespace Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Store;

class InvoiceRecipient extends Model
{
    protected $fillable = [
        'store_id',
        'email',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get default recipient for a store
     */
    public static function getDefaultForStore(int $storeId)
    {
        return self::where('store_id', $storeId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get all recipients for a store
     */
    public static function getForStore(int $storeId)
    {
        return self::where('store_id', $storeId)->get();
    }

    /**
     * Set this recipient as default for the store
     */
    public function setAsDefault(): void
    {
        // Remove default from all others for this store
        self::where('store_id', $this->store_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }
}
