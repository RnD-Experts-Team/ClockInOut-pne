<?php

namespace Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceEmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the default template
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first() ?? self::first();
    }

    /**
     * Set this template as default
     */
    public function setAsDefault(): void
    {
        // Remove default from all others
        self::where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }
}
