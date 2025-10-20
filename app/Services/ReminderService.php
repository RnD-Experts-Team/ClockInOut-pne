<?php

namespace App\Services;

use App\Models\CalendarReminder;
use App\Models\CalendarEvent;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new reminder
     */
    public function createReminder(array $data): CalendarReminder
    {
        $reminder = CalendarReminder::create(array_merge($data, [
            'admin_user_id' => $data['admin_user_id'] ?? auth()->id(),
            'status' => 'pending',
        ]));

        // Create calendar event for the reminder if it doesn't exist
        if (!$reminder->calendar_event_id) {
            $calendarEvent = CalendarEvent::create([
                'title' => 'Reminder: ' . $reminder->title,
                'description' => $reminder->description,
                'event_type' => 'reminder',
                'start_date' => $reminder->reminder_date,
                'start_time' => $reminder->reminder_time,
                'is_all_day' => false,
                'color_code' => '#ffc107',
                'created_by' => auth()->id(),
            ]);

            $reminder->update(['calendar_event_id' => $calendarEvent->id]);
        }

        return $reminder;
    }

    /**
     * Send reminder notification
     */
    public function sendReminder(CalendarReminder $reminder): bool
    {
        try {
            $user = $reminder->adminUser;

            if (!$user) {
                Log::warning('Cannot send reminder - user not found', ['reminder_id' => $reminder->id]);
                return false;
            }

            // Send notifications based on configured methods
            $notificationMethods = $reminder->notification_methods ?? ['browser'];

            foreach ($notificationMethods as $method) {
                switch ($method) {
                    case 'browser':
                        $this->notificationService->sendBrowserNotification($user, $reminder->title, $reminder->description, [
                            'reminder_id' => $reminder->id,
                            'reminder_type' => $reminder->reminder_type,
                            'due_date' => $reminder->reminder_date->format('Y-m-d'),
                            'due_time' => $reminder->reminder_time,
                        ]);
                        break;

                    case 'email':
                        // TODO: Implement email notifications
                        Log::info('Email notification not implemented yet', ['reminder_id' => $reminder->id]);
                        break;

                    case 'sms':
                        // TODO: Implement SMS notifications
                        Log::info('SMS notification not implemented yet', ['reminder_id' => $reminder->id]);
                        break;
                }
            }

            $reminder->markAsSent();

            // Create recurring reminder if needed
            if ($reminder->isRecurring()) {
                $reminder->createRecurringReminder();
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send reminder', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get user's pending reminders
     */
    public function getUserReminders(int $userId, ?string $status = 'pending'): Collection
    {
        $query = CalendarReminder::byUser($userId)->with('calendarEvent', 'relatedModel');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('reminder_date', 'asc')
            ->orderBy('reminder_time', 'asc')
            ->get();
    }

    /**
     * Get due reminders
     */
    public function getDueReminders(): Collection
    {
        return CalendarReminder::where('status', 'pending')
            ->where(function ($query) {
                $now = Carbon::now();
                $query->where('reminder_date', '<', $now->format('Y-m-d'))
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->where('reminder_date', '=', $now->format('Y-m-d'))
                            ->where('reminder_time', '<=', $now->format('H:i'));
                    });
            })
            ->where(function ($query) {
                $query->whereNull('snooze_until')
                    ->orWhere('snooze_until', '<=', Carbon::now());
            })
            ->with('adminUser', 'calendarEvent')
            ->get();
    }

    /**
     * Get overdue reminders
     */
    public function getOverdueReminders(): Collection
    {
        $now = Carbon::now();

        return CalendarReminder::where('status', 'pending')
            ->where(function ($query) use ($now) {
                $query->where('reminder_date', '<', $now->format('Y-m-d'))
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->where('reminder_date', '=', $now->format('Y-m-d'))
                            ->where('reminder_time', '<', $now->format('H:i'));
                    });
            })
            ->with('adminUser', 'calendarEvent')
            ->get();
    }

    /**
     * Snooze reminder for specified minutes
     */
    public function snoozeReminder(CalendarReminder $reminder, int $minutes): void
    {
        $reminder->snooze($minutes);
    }

    /**
     * Dismiss reminder
     */
    public function dismissReminder(CalendarReminder $reminder): void
    {
        $reminder->dismiss();
    }

    /**
     * Get reminders for maintenance requests that are on hold
     */
    public function getMaintenanceHoldReminders(): Collection
    {
        return CalendarReminder::where('reminder_type', 'maintenance_followup')
            ->where('status', 'pending')
            ->whereHas('relatedModel', function ($query) {
                $query->where('status', 'on_hold');
            })
            ->with('adminUser', 'relatedModel')
            ->get();
    }

    /**
     * Create maintenance follow-up reminder
     */
    public function createMaintenanceFollowUp(int $maintenanceRequestId, Carbon $reminderDate, int $adminUserId): CalendarReminder
    {
        return $this->createReminder([
            'admin_user_id' => $adminUserId,
            'title' => 'Maintenance Follow-up Required',
            'description' => 'Follow up on maintenance request that was put on hold',
            'reminder_date' => $reminderDate,
            'reminder_time' => '09:00',
            'reminder_type' => 'maintenance_followup',
            'is_recurring' => false,
            'status' => 'pending',
            'related_model_type' => 'App\Models\MaintenanceRequest',
            'related_model_id' => $maintenanceRequestId,
            'notification_methods' => ['browser'],
        ]);
    }

    /**
     * Create lease renewal reminder
     */
    public function createLeaseRenewalReminder(int $leaseId, Carbon $reminderDate, int $adminUserId): CalendarReminder
    {
        return $this->createReminder([
            'admin_user_id' => $adminUserId,
            'title' => 'Lease Renewal Due',
            'description' => 'Lease renewal process needs to be initiated',
            'reminder_date' => $reminderDate,
            'reminder_time' => '10:00',
            'reminder_type' => 'lease_renewal',
            'is_recurring' => false,
            'status' => 'pending',
            'related_model_type' => 'App\Models\Lease',
            'related_model_id' => $leaseId,
            'notification_methods' => ['browser', 'email'],
        ]);
    }

    /**
     * Create payment due reminder
     */
    public function createPaymentDueReminder(int $paymentId, Carbon $reminderDate, int $adminUserId): CalendarReminder
    {
        return $this->createReminder([
            'admin_user_id' => $adminUserId,
            'title' => 'Payment Due',
            'description' => 'Payment is due and needs attention',
            'reminder_date' => $reminderDate,
            'reminder_time' => '08:00',
            'reminder_type' => 'payment_due',
            'is_recurring' => false,
            'status' => 'pending',
            'related_model_type' => 'App\Models\Payment',
            'related_model_id' => $paymentId,
            'notification_methods' => ['browser'],
        ]);
    }

    /**
     * Process and send all due reminders
     */
    public function processDueReminders(): array
    {
        $dueReminders = $this->getDueReminders();

        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($dueReminders as $reminder) {
            try {
                if ($this->sendReminder($reminder)) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Get reminder statistics
     */
    public function getReminderStatistics(int $userId = null): array
    {
        $query = CalendarReminder::query();

        if ($userId) {
            $query->where('admin_user_id', $userId);
        }

        $all = $query->get();

        return [
            'total' => $all->count(),
            'pending' => $all->where('status', 'pending')->count(),
            'sent' => $all->where('status', 'sent')->count(),
            'dismissed' => $all->where('status', 'dismissed')->count(),
            'snoozed' => $all->where('status', 'snoozed')->count(),
            'overdue' => $this->getOverdueReminders()->count(),
            'by_type' => $all->groupBy('reminder_type')->map->count(),
            'upcoming_7_days' => CalendarReminder::where('reminder_date', '<=', Carbon::now()->addDays(7))
                ->where('reminder_date', '>=', Carbon::now())
                ->where('status', 'pending')
                ->count(),
        ];
    }

    /**
     * Bulk action on reminders
     */
    public function bulkAction(array $reminderIds, string $action, int $userId): int
    {
        $reminders = CalendarReminder::whereIn('id', $reminderIds)
            ->where('admin_user_id', $userId)
            ->get();

        $count = 0;

        foreach ($reminders as $reminder) {
            switch ($action) {
                case 'dismiss':
                    $reminder->dismiss();
                    $count++;
                    break;
                case 'delete':
                    $reminder->delete();
                    $count++;
                    break;
                case 'mark_sent':
                    $reminder->markAsSent();
                    $count++;
                    break;
            }
        }

        return $count;
    }

    /**
     * Update reminder
     */
    public function updateReminder(CalendarReminder $reminder, array $data): CalendarReminder
    {
        $reminder->update($data);

        // Update related calendar event if exists
        if ($reminder->calendarEvent) {
            $reminder->calendarEvent->update([
                'title' => 'Reminder: ' . $reminder->title,
                'description' => $reminder->description,
                'start_date' => $reminder->reminder_date,
                'start_time' => $reminder->reminder_time,
            ]);
        }

        return $reminder;
    }

    /**
     * Clean old reminders
     */
    public function cleanOldReminders(int $olderThanDays = 90): int
    {
        return CalendarReminder::where('reminder_date', '<', Carbon::now()->subDays($olderThanDays))
            ->whereIn('status', ['sent', 'dismissed'])
            ->delete();
    }

    /**
     * Get upcoming reminders for dashboard
     */
    public function getUpcomingReminders(int $userId, int $limit = 5): Collection
    {
        return CalendarReminder::byUser($userId)
            ->where('status', 'pending')
            ->where('reminder_date', '>=', Carbon::today())
            ->orderBy('reminder_date')
            ->orderBy('reminder_time')
            ->limit($limit)
            ->get();
    }
}
