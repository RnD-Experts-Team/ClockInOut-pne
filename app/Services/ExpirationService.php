<?php

namespace App\Services;

use App\Models\ExpirationTracking;
use App\Models\CalendarReminder;
use App\Models\CalendarEvent;
use App\Models\ApartmentLease;
use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ExpirationService
{
    protected ReminderService $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Track new expiration
     */
    public function trackExpiration(array $data): ExpirationTracking
    {
        $expiration = ExpirationTracking::create($data);

        // Create calendar event for expiration
        $expiration->createReminderEvent();

        // Create reminder notifications based on warning days
        $this->createExpirationReminders($expiration);

        return $expiration;
    }

    /**
     * Check all expirations and create reminders
     */
    public function checkExpirations(): array
    {
        $results = [
            'warnings_created' => 0,
            'expired_found' => 0,
            'reminders_sent' => 0
        ];

        // Get items expiring soon that need warnings
        $expiringSoon = ExpirationTracking::expiringSoon()
            ->needsReminder()
            ->with('trackableModel')
            ->get();

        foreach ($expiringSoon as $expiration) {
            $this->createExpirationReminders($expiration);
            $expiration->markReminderSent();
            $results['warnings_created']++;
        }

        // Get expired items
        $expired = ExpirationTracking::expired()->get();
        $results['expired_found'] = $expired->count();

        // Process expired items
        foreach ($expired as $expiration) {
            $this->handleExpiredItem($expiration);
        }

        return $results;
    }

    /**
     * Create expiration reminder notifications
     */
    protected function createExpirationReminders(ExpirationTracking $expiration): void
    {
        $warningDays = [30, 14, 7, 3, 1]; // Days before expiration to warn

        foreach ($warningDays as $days) {
            $reminderDate = $expiration->expiration_date->copy()->subDays($days);

            // Only create reminder if it's in the future
            if ($reminderDate->isFuture()) {
                $this->reminderService->createReminder([
                    'admin_user_id' => auth()->id() ?? 1,
                    'title' => $this->getExpirationReminderTitle($expiration, $days),
                    'description' => $this->getExpirationReminderDescription($expiration, $days),
                    'reminder_date' => $reminderDate,
                    'reminder_time' => '09:00',
                    'reminder_type' => 'expiration_alert',
                    'is_recurring' => false,
                    'status' => 'pending',
                    'related_model_type' => get_class($expiration),
                    'related_model_id' => $expiration->id,
                    'notification_methods' => ['browser'],
                ]);
            }
        }
    }

    /**
     * Handle expired item
     */
    protected function handleExpiredItem(ExpirationTracking $expiration): void
    {
        // Create urgent reminder for expired item
        $this->reminderService->createReminder([
            'admin_user_id' => auth()->id() ?? 1,
            'title' => 'EXPIRED: ' . $this->getExpirationTypeLabel($expiration->expiration_type),
            'description' => $this->getExpiredItemDescription($expiration),
            'reminder_date' => Carbon::today(),
            'reminder_time' => Carbon::now()->format('H:i'),
            'reminder_type' => 'expiration_alert',
            'is_recurring' => false,
            'status' => 'pending',
            'related_model_type' => get_class($expiration),
            'related_model_id' => $expiration->id,
            'notification_methods' => ['browser'],
        ]);
    }

    /**
     * Get items expiring within specified days
     */
    public function getExpiringItems(int $days = 30): Collection
    {
        return ExpirationTracking::expiringSoon($days)
            ->with('trackableModel')
            ->orderBy('expiration_date', 'asc')
            ->get();
    }

    /**
     * Get expired items
     */
    public function getExpiredItems(): Collection
    {
        return ExpirationTracking::expired()
            ->with('trackableModel')
            ->orderBy('expiration_date', 'desc')
            ->get();
    }

    /**
     * Renew expiration
     */
    public function renewExpiration(ExpirationTracking $expiration, Carbon $newExpirationDate, ?string $notes = null): void
    {
        $expiration->renew($newExpirationDate, $notes);

        // Create new reminders for renewed expiration
        $this->createExpirationReminders($expiration->fresh());
    }

    /**
     * Extend expiration
     */
    public function extendExpiration(ExpirationTracking $expiration, int $days, ?string $notes = null): void
    {
        $expiration->extend($days, $notes);

        // Create new reminders for extended expiration
        $this->createExpirationReminders($expiration->fresh());
    }

    /**
     * Get expiration statistics
     */
    public function getExpirationStatistics(): array
    {
        return [
            'total_tracked' => ExpirationTracking::count(),
            'expiring_soon_30' => ExpirationTracking::expiringSoon(30)->count(),
            'expiring_soon_7' => ExpirationTracking::expiringSoon(7)->count(),
            'expired' => ExpirationTracking::expired()->count(),
            'by_type' => ExpirationTracking::selectRaw('expiration_type, COUNT(*) as count')
                ->groupBy('expiration_type')
                ->pluck('count', 'expiration_type'),
            'by_status' => ExpirationTracking::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];
    }

    /**
     * Auto-track lease expirations
     */
    public function trackLeaseExpirations(): int
    {
        $count = 0;

        // Track apartment leases
        if (class_exists('App\Models\ApartmentLease')) {
            $leases = ApartmentLease::whereNotNull('expiration_date')
                ->whereDoesntHave('expirationTracking')
                ->get();

            foreach ($leases as $lease) {
                $this->trackExpiration([
                    'model_type' => get_class($lease),
                    'model_id' => $lease->id,
                    'expiration_date' => $lease->expiration_date,
                    'expiration_type' => 'lease_end',
                    'warning_days' => 30,
                    'status' => 'active',
                    'notes' => 'Auto-tracked lease expiration',
                ]);
                $count++;
            }
        }

        // Track regular leases (franchise agreements)
        if (class_exists('App\Models\Lease')) {
            $franchiseLeases = Lease::whereNotNull('franchise_agreement_expiration_date')
                ->whereDoesntHave('expirationTracking')
                ->get();

            foreach ($franchiseLeases as $lease) {
                $this->trackExpiration([
                    'model_type' => get_class($lease),
                    'model_id' => $lease->id,
                    'expiration_date' => $lease->franchise_agreement_expiration_date,
                    'expiration_type' => 'franchise_agreement',
                    'warning_days' => 30,
                    'status' => 'active',
                    'notes' => 'Auto-tracked franchise agreement expiration',
                ]);
                $count++;
            }

            // Track initial lease expirations
            $initialLeases = Lease::whereNotNull('initial_lease_expiration_date')
                ->whereDoesntHave('expirationTracking')
                ->get();

            foreach ($initialLeases as $lease) {
                $this->trackExpiration([
                    'model_type' => get_class($lease),
                    'model_id' => $lease->id,
                    'expiration_date' => $lease->initial_lease_expiration_date,
                    'expiration_type' => 'initial_lease',
                    'warning_days' => 30,
                    'status' => 'active',
                    'notes' => 'Auto-tracked initial lease expiration',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get expiration reminder title
     */
    protected function getExpirationReminderTitle(ExpirationTracking $expiration, int $days): string
    {
        $typeLabel = $this->getExpirationTypeLabel($expiration->expiration_type);

        if ($days === 1) {
            return $typeLabel . ' expires TOMORROW!';
        } elseif ($days <= 7) {
            return $typeLabel . ' expires in ' . $days . ' days';
        } else {
            return $typeLabel . ' expires in ' . $days . ' days';
        }
    }

    /**
     * Get expiration reminder description
     */
    protected function getExpirationReminderDescription(ExpirationTracking $expiration, int $days): string
    {
        $modelName = class_basename($expiration->model_type);
        $typeLabel = $this->getExpirationTypeLabel($expiration->expiration_type);

        $description = $typeLabel . ' for ' . $modelName . ' (ID: ' . $expiration->model_id . ') ';
        $description .= 'expires on ' . $expiration->expiration_date->format('M j, Y') . '. ';

        if ($days === 1) {
            $description .= '⚠️ URGENT: ACTION REQUIRED!';
        } elseif ($days <= 7) {
            $description .= 'Please take action soon.';
        }

        if ($expiration->notes) {
            $description .= "\n\n" . $expiration->notes;
        }

        return $description;
    }

    /**
     * Get expired item description
     */
    protected function getExpiredItemDescription(ExpirationTracking $expiration): string
    {
        $modelName = class_basename($expiration->model_type);
        $typeLabel = $this->getExpirationTypeLabel($expiration->expiration_type);
        $daysOverdue = abs($expiration->daysUntilExpiration());

        return $typeLabel . ' for ' . $modelName . ' (ID: ' . $expiration->model_id . ') ' .
            'expired ' . $daysOverdue . ' days ago on ' . $expiration->expiration_date->format('M j, Y') . '. ' .
            '🚨 Immediate action required!';
    }

    /**
     * Get expiration type label
     */
    protected function getExpirationTypeLabel(string $expirationType): string
    {
        return match($expirationType) {
            'lease_end' => 'Lease Agreement',
            'franchise_agreement' => 'Franchise Agreement',
            'initial_lease' => 'Initial Lease',
            'officer_term' => 'Officer Term',
            'department_closure' => 'Department Closure',
            'contract_end' => 'Contract',
            'license_expiry' => 'License',
            default => ucfirst(str_replace('_', ' ', $expirationType)),
        };
    }

    /**
     * Bulk update warning days
     */
    public function bulkUpdateWarningDays(array $expirationIds, int $warningDays): int
    {
        return ExpirationTracking::whereIn('id', $expirationIds)
            ->update(['warning_days' => $warningDays]);
    }

    /**
     * Get items needing attention (expiring soon + expired)
     */
    public function getItemsNeedingAttention(): array
    {
        $expiringSoon = $this->getExpiringItems(30);
        $expired = $this->getExpiredItems();

        return [
            'expiring_soon' => $expiringSoon->map(function ($item) {
                return [
                    'id' => $item->id,
                    'model_type' => class_basename($item->model_type),
                    'model_id' => $item->model_id,
                    'expiration_date' => $item->expiration_date->format('Y-m-d'),
                    'days_until_expiration' => $item->daysUntilExpiration(),
                    'expiration_type' => $item->expiration_type,
                    'status' => $item->status,
                    'urgency' => $item->daysUntilExpiration() <= 7 ? 'high' : ($item->daysUntilExpiration() <= 14 ? 'medium' : 'low'),
                ];
            }),
            'expired' => $expired->map(function ($item) {
                return [
                    'id' => $item->id,
                    'model_type' => class_basename($item->model_type),
                    'model_id' => $item->model_id,
                    'expiration_date' => $item->expiration_date->format('Y-m-d'),
                    'days_since_expiration' => abs($item->daysUntilExpiration()),
                    'expiration_type' => $item->expiration_type,
                    'status' => $item->status,
                    'urgency' => 'critical',
                ];
            }),
            'summary' => [
                'total_expiring_soon' => $expiringSoon->count(),
                'total_expired' => $expired->count(),
                'by_type' => $expiringSoon->concat($expired)->groupBy('expiration_type')->map->count(),
            ]
        ];
    }
}
