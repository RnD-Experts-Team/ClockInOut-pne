<?php

namespace App\Models\Native;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NativeRequestAttachment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'native_request_attachments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'native_request_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete physical file when model is deleted
        static::deleting(function ($attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }

    /**
     * Get the native request that owns this attachment.
     */
    public function nativeRequest(): BelongsTo
    {
        return $this->belongsTo(NativeRequest::class, 'native_request_id');
    }

    /**
     * Get the public URL for this attachment.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
