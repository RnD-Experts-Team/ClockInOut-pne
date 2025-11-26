<?php

namespace App\Models\Native;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NativeRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'native_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'store_id',
        'requester_id',
        'external_requester',
        'is_from_cognito',
        'equipment_with_issue',
        'description_of_issue',
        'urgency_level_id',
        'basic_troubleshoot_done',
        'request_date',
        'status',
        'assigned_to',
        'costs',
        'how_we_fixed_it',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'request_date' => 'date',
        'basic_troubleshoot_done' => 'boolean',
        'is_from_cognito' => 'boolean',
        'costs' => 'decimal:2',
    ];

    /**
     * Allowed status values.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';
    const STATUS_CANCELED = 'canceled';
    const STATUS_RECEIVED = 'received';

    /**
     * Get the store that owns this request.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user (store manager) who submitted this request.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the urgency level for this request.
     */
    public function urgencyLevel(): BelongsTo
    {
        return $this->belongsTo(NativeUrgencyLevel::class, 'urgency_level_id');
    }

    /**
     * Get the user (technician) assigned to this request.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all attachments for this request.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(NativeRequestAttachment::class, 'native_request_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to filter by stores (multiple).
     */
    public function scopeForStores($query, array $storeIds)
    {
        return $query->whereIn('store_id', $storeIds);
    }

    // ============================================================================
    // COGNITOFORMS INTEGRATION - Display Helpers
    // ============================================================================

    /**
     * Get the display name for the requester.
     *
     * Returns the external requester name for CognitoForms submissions,
     * otherwise returns the store manager's name.
     *
     * @return string
     */
    public function getDisplayRequesterNameAttribute(): string
    {
        if ($this->is_from_cognito && $this->external_requester) {
            return $this->external_requester;
        }

        return $this->requester->name;
    }

    /**
     * Get the requester type label for display.
     *
     * @return string
     */
    public function getRequesterTypeAttribute(): string
    {
        return $this->is_from_cognito ? 'via CognitoForms' : 'Store Manager';
    }

    /**
     * Check if this request is from CognitoForms.
     *
     * @return bool
     */
    public function getIsCognitoRequestAttribute(): bool
    {
        return (bool) $this->is_from_cognito;
    }
}
