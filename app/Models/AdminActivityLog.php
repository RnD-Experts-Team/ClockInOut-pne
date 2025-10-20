<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'action_type',
        'model_type',
        'model_id',
        'field_name',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
        'performed_at',
        'description',
    ];

    protected $casts = [
        'old_value' => 'json',
        'new_value' => 'json',
        'performed_at' => 'datetime',
    ];

    // Relationships
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    // Scopes
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_user_id', $adminId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('performed_at', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action_type', $action);
    }

    public function scopeByModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    // Methods
    public function getHumanReadableDescription(): string
    {
        $admin = $this->adminUser;
        $adminName = $admin ? $admin->name : 'Unknown Admin';

        return match($this->action_type) {
            'create' => "{$adminName} created a new {$this->getModelName()}",
            'update' => "{$adminName} updated {$this->getModelName()} (ID: {$this->model_id})",
            'delete' => "{$adminName} deleted {$this->getModelName()} (ID: {$this->model_id})",
            'view' => "{$adminName} viewed {$this->getModelName()} (ID: {$this->model_id})",
            default => $this->description,
        };
    }

    public function getModelChanges(): array
    {
        $changes = [];

        if ($this->old_value && $this->new_value) {
            $oldValues = is_array($this->old_value) ? $this->old_value : json_decode($this->old_value, true);
            $newValues = is_array($this->new_value) ? $this->new_value : json_decode($this->new_value, true);

            foreach ($newValues as $key => $newValue) {
                $oldValue = $oldValues[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }

        return $changes;
    }

    private function getModelName(): string
    {
        $modelParts = explode('\\', $this->model_type);
        return end($modelParts);
    }

    public function getColorCodeAttribute(): string
    {
        return match($this->action_type) {
            'create' => '#28a745', // green
            'update' => '#ffc107', // yellow
            'delete' => '#dc3545', // red
            'view' => '#17a2b8',   // blue
            default => '#6c757d',  // gray
        };
    }
}
