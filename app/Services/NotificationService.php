<?php

namespace App\Services;

use App\Models\ApartmentLease;
use App\Models\Lease;
use App\Models\User;
use App\Models\CalendarReminder;
use App\Notifications\ReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Send browser notification to user
     */
    public function sendBrowserNotification(User $user, string $title, string $message, array $data = []): void
    {
        // Store notification in database for browser display
        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\\Notifications\\BrowserNotification',
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'data' => [
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ],
            'created_at' => now(),
            'read_at' => null,
        ]);

        Log::info('Browser notification sent', [
            'user_id' => $user->id,
            'title' => $title,
            'message' => substr($message, 0, 100) . '...'
        ]);
    }

    /**
     * Send email notification
     */
    public function sendEmailNotification(User $user, string $subject, string $message, array $data = []): bool
    {
        try {
            // TODO: Implement email sending logic
            // This could use Laravel's Mail facade or a service like SendGrid

            Log::info('Email notification queued', [
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $subject
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send SMS notification
     */
    public function sendSMSNotification(User $user, string $message): bool
    {
        try {
            // TODO: Implement SMS sending logic
            // This could use Twilio, AWS SNS, or another SMS service

            Log::info('SMS notification queued', [
                'user_id' => $user->id,
                'phone' => $user->phone ?? 'No phone number',
                'message' => substr($message, 0, 50) . '...'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send reminder notification
     */
    public function sendReminderNotification(User $user, CalendarReminder $reminder): void
    {
        $user->notify(new ReminderNotification($reminder));
    }

    /**
     * Send maintenance request notification
     */
    public function sendMaintenanceNotification(User $user, string $type, array $data): void
    {
        $titles = [
            'new_request' => 'New Maintenance Request',
            'status_updated' => 'Maintenance Request Updated',
            'assigned' => 'Maintenance Request Assigned',
            'completed' => 'Maintenance Request Completed',
        ];

        $title = $titles[$type] ?? 'Maintenance Notification';

        $this->sendBrowserNotification($user, $title, $data['message'] ?? '', $data);
    }

    /**
     * Send lease expiration notification
     */
    public function sendLeaseExpirationNotification(User $user, array $leaseData, int $daysUntilExpiration): void
    {
        $title = $daysUntilExpiration <= 0 ? 'Lease Expired!' : "Lease Expires in {$daysUntilExpiration} Days";

        $message = "Store {$leaseData['store_number']} lease ";
        $message .= $daysUntilExpiration <= 0 ? 'has expired' : "expires in {$daysUntilExpiration} days";
        $message .= " on {$leaseData['expiration_date']}.";

        if ($daysUntilExpiration <= 0) {
            $message .= " Immediate action required!";
        } elseif ($daysUntilExpiration <= 7) {
            $message .= " Please take action soon.";
        }

        $this->sendBrowserNotification($user, $title, $message, [
            'type' => 'lease_expiration',
            'lease_id' => $leaseData['id'],
            'store_number' => $leaseData['store_number'],
            'days_until_expiration' => $daysUntilExpiration,
            'urgency' => $daysUntilExpiration <= 0 ? 'critical' : ($daysUntilExpiration <= 7 ? 'high' : 'medium')
        ]);
    }

    /**
     * Send bulk notifications to multiple users
     */
    public function sendBulkNotification(Collection $users, string $title, string $message, array $data = []): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($users as $user) {
            try {
                $this->sendBrowserNotification($user, $title, $message, $data);
                $results['sent']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];

                Log::error('Failed to send bulk notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get user's unread notifications
     */
    public function getUnreadNotifications(User $user, int $limit = 10): Collection
    {
        return $user->unreadNotifications()->limit($limit)->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->unreadNotifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Clear old notifications
     */
    public function clearOldNotifications(User $user, int $olderThanDays = 30): int
    {
        return $user->notifications()
            ->where('created_at', '<', now()->subDays($olderThanDays))
            ->delete();
    }

    /**
     * Get notification statistics for user
     */
    public function getNotificationStats(User $user): array
    {
        $notifications = $user->notifications;

        return [
            'total' => $notifications->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $notifications->whereNotNull('read_at')->count(),
            'today' => $notifications->where('created_at', '>=', now()->startOfDay())->count(),
            'this_week' => $notifications->where('created_at', '>=', now()->startOfWeek())->count(),
            'by_type' => $notifications->groupBy('type')->map->count(),
        ];
    }

    /**
     * Send system alert to all admin users
     */
    public function sendSystemAlert(string $title, string $message, string $level = 'info'): array
    {
        $adminUsers = User::where('role', 'admin')->get();

        return $this->sendBulkNotification($adminUsers, $title, $message, [
            'type' => 'system_alert',
            'level' => $level,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Send daily summary notifications
     */
    public function sendDailySummary(User $user, array $summaryData): void
    {
        $message = "Daily Summary:\n";
        $message .= "• {$summaryData['maintenance_requests']} maintenance requests\n";
        $message .= "• {$summaryData['clock_events']} clock events\n";
        $message .= "• {$summaryData['reminders_due']} reminders due\n";
        $message .= "• {$summaryData['expirations_soon']} items expiring soon";

        $this->sendBrowserNotification($user, 'Daily Summary', $message, [
            'type' => 'daily_summary',
            'data' => $summaryData,
            'date' => now()->toDateString()
        ]);
    }

    /**
     * Test notification system
     */
    public function sendTestNotification(User $user): array
    {
        $results = [];

        // Test browser notification
        try {
            $this->sendBrowserNotification($user, 'Test Notification', 'This is a test browser notification', [
                'test' => true,
                'timestamp' => now()->toISOString()
            ]);
            $results['browser'] = 'success';
        } catch (\Exception $e) {
            $results['browser'] = 'failed: ' . $e->getMessage();
        }

        // Test email notification (if implemented)
        try {
            $emailResult = $this->sendEmailNotification($user, 'Test Email', 'This is a test email notification');
            $results['email'] = $emailResult ? 'success' : 'failed';
        } catch (\Exception $e) {
            $results['email'] = 'failed: ' . $e->getMessage();
        }

        // Test SMS notification (if implemented)
        try {
            $smsResult = $this->sendSMSNotification($user, 'This is a test SMS notification');
            $results['sms'] = $smsResult ? 'success' : 'failed';
        } catch (\Exception $e) {
            $results['sms'] = 'failed: ' . $e->getMessage();
        }

        return $results;
    }

    public function checkTodayExpirations(): array
    {
        $today = Carbon::today();
        $notifications = [];

        try {
            // Check apartment lease expirations TODAY
            $apartmentLeases = ApartmentLease::whereDate('expiration_date', $today)->get();
            foreach ($apartmentLeases as $lease) {
                $notifications[] = [
                    'type' => 'apartment_lease_expiration',
                    'title' => '🚨 URGENT: Apartment Lease Expires TODAY',
                    'message' => "Store #{$lease->store_number} apartment lease expires today at {$lease->apartment_address}",
                    'urgency' => 'critical',
                    'data' => [
                        'lease_id' => $lease->id,
                        'store_number' => $lease->store_number,
                        'address' => $lease->apartment_address,
                        'lease_holder' => $lease->lease_holder,
                        'url' => route('admin.apartment-leases.show', $lease->id)
                    ]
                ];
            }

            // Check apartment lease renewals TODAY
            $apartmentRenewals = ApartmentLease::whereDate('renewal_date', $today)
                ->where('renewal_status', '!=', 'completed')
                ->get();
            foreach ($apartmentRenewals as $lease) {
                $notifications[] = [
                    'type' => 'apartment_lease_renewal',
                    'title' => '🔔 Apartment Lease Renewal Due TODAY',
                    'message' => "Store #{$lease->store_number} apartment lease renewal is due today",
                    'urgency' => 'high',
                    'data' => [
                        'lease_id' => $lease->id,
                        'store_number' => $lease->store_number,
                        'renewal_status' => $lease->renewal_status,
                        'url' => route('admin.apartment-leases.show', $lease->id)
                    ]
                ];
            }

            // Check regular lease expirations TODAY
            if (class_exists(Lease::class)) {
                $regularLeases = Lease::where(function($query) use ($today) {
                    $query->whereDate('franchise_agreement_expiration_date', $today)
                        ->orWhereDate('initial_lease_expiration_date', $today);
                })->get();

                foreach ($regularLeases as $lease) {
                    if ($lease->franchise_agreement_expiration_date &&
                        $lease->franchise_agreement_expiration_date->isToday()) {
                        $notifications[] = [
                            'type' => 'franchise_expiration',
                            'title' => '🚨 URGENT: Franchise Agreement Expires TODAY',
                            'message' => "Franchise agreement for store expires today",
                            'urgency' => 'critical',
                            'data' => [
                                'lease_id' => $lease->id,
                                'url' => route('admin.leases.show', $lease->id)
                            ]
                        ];
                    }

                    if ($lease->initial_lease_expiration_date &&
                        $lease->initial_lease_expiration_date->isToday()) {
                        $notifications[] = [
                            'type' => 'lease_expiration',
                            'title' => '🚨 URGENT: Lease Expires TODAY',
                            'message' => "Initial lease for store expires today",
                            'urgency' => 'critical',
                            'data' => [
                                'lease_id' => $lease->id,
                                'url' => route('admin.leases.show', $lease->id)
                            ]
                        ];
                    }
                }
            }

            // Check calendar reminders TODAY
            $reminders = CalendarReminder::whereDate('reminder_date', $today)
                ->where('status', 'pending')
                ->get();
            foreach ($reminders as $reminder) {
                $notifications[] = [
                    'type' => 'reminder',
                    'title' => '🔔 Reminder Due',
                    'message' => $reminder->title ?? 'You have a reminder due today',
                    'urgency' => $reminder->priority ?? 'normal',
                    'data' => [
                        'reminder_id' => $reminder->id,
                        'description' => $reminder->description,
                        'url' => route('admin.calendar.dashboard')
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error checking today expirations: ' . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * Check for OVERDUE items and return notifications
     */
    public function checkOverdueItems(): array
    {
        $today = Carbon::today();
        $notifications = [];

        try {
            // Check overdue apartment lease expirations
            $overdueApartmentLeases = ApartmentLease::where('expiration_date', '<', $today)->get();
            foreach ($overdueApartmentLeases as $lease) {
                $daysOverdue = $today->diffInDays($lease->expiration_date);
                $notifications[] = [
                    'type' => 'apartment_lease_overdue',
                    'title' => "🚨 OVERDUE: Apartment Lease ({$daysOverdue} days)",
                    'message' => "Store #{$lease->store_number} apartment lease is {$daysOverdue} days overdue",
                    'urgency' => 'overdue',
                    'data' => [
                        'lease_id' => $lease->id,
                        'days_overdue' => $daysOverdue,
                        'store_number' => $lease->store_number,
                        'url' => route('admin.apartment-leases.show', $lease->id)
                    ]
                ];
            }

            // Check overdue apartment lease renewals
            $overdueRenewals = ApartmentLease::where('renewal_date', '<', $today)
                ->where('renewal_status', '!=', 'completed')
                ->get();
            foreach ($overdueRenewals as $lease) {
                $daysOverdue = $today->diffInDays($lease->renewal_date);
                $notifications[] = [
                    'type' => 'apartment_renewal_overdue',
                    'title' => "⏰ OVERDUE: Apartment Renewal ({$daysOverdue} days)",
                    'message' => "Store #{$lease->store_number} apartment lease renewal is {$daysOverdue} days overdue",
                    'urgency' => 'overdue',
                    'data' => [
                        'lease_id' => $lease->id,
                        'days_overdue' => $daysOverdue,
                        'renewal_status' => $lease->renewal_status,
                        'url' => route('admin.apartment-leases.show', $lease->id)
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error checking overdue items: ' . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * Get all current notifications for browser display (MAIN METHOD FOR 10-SECOND CHECKING)
     */
    public function getCurrentNotifications(): array
    {
        $todayNotifications = $this->checkTodayExpirations();
        $overdueNotifications = $this->checkOverdueItems();

        $allNotifications = array_merge($todayNotifications, $overdueNotifications);

        // Sort by urgency priority
        usort($allNotifications, function($a, $b) {
            $urgencyOrder = ['overdue' => 1, 'critical' => 2, 'high' => 3, 'normal' => 4, 'info' => 5];
            return ($urgencyOrder[$a['urgency']] ?? 4) <=> ($urgencyOrder[$b['urgency']] ?? 4);
        });

        return [
            'notifications' => $allNotifications,
            'count' => count($allNotifications),
            'critical_count' => count(array_filter($allNotifications, fn($n) => in_array($n['urgency'], ['overdue', 'critical']))),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Format notifications for browser display
     */
    public function formatNotificationsForDisplay(array $notifications): array
    {
        return array_map(function($notification) {
            return [
                'id' => 'notif_' . uniqid(),
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $notification['type'],
                'urgency' => $notification['urgency'],
                'icon' => $this->getNotificationIcon($notification['type']),
                'color' => $this->getNotificationColor($notification['urgency']),
                'url' => $notification['data']['url'] ?? null,
                'timestamp' => now()->format('H:i'),
                'data' => $notification['data']
            ];
        }, $notifications);
    }

    /**
     * Get notification icon based on type
     */
    public function getNotificationIcon(string $type): string
    {
        return match($type) {
            'apartment_lease_expiration', 'lease_expiration', 'franchise_expiration' => '🏠',
            'apartment_lease_renewal', 'lease_renewal' => '🔄',
            'apartment_lease_overdue', 'apartment_renewal_overdue' => '🚨',
            'reminder' => '🔔',
            'maintenance_request' => '🔧',
            default => '📋'
        };
    }

    /**
     * Get notification color based on urgency
     */
    public function getNotificationColor(string $urgency): string
    {
        return match($urgency) {
            'overdue' => '#dc3545',      // red
            'critical' => '#dc3545',     // red
            'high' => '#fd7e14',         // orange
            'normal' => '#17a2b8',       // blue
            'info' => '#28a745',         // green
            default => '#6c757d'         // gray
        };
    }

    /**
     * Send apartment lease renewal notification
     */
    public function sendApartmentRenewalNotification(User $user, ApartmentLease $lease, int $daysUntilRenewal): void
    {
        $statusIcon = $daysUntilRenewal <= 0 ? '🚨' : ($daysUntilRenewal <= 7 ? '⚡' : '🔔');

        if ($daysUntilRenewal <= 0) {
            $title = "{$statusIcon} OVERDUE: Apartment Lease Renewal";
            $message = "Store #{$lease->store_number} apartment lease renewal is " . abs($daysUntilRenewal) . " days overdue!";
        } elseif ($daysUntilRenewal <= 7) {
            $title = "{$statusIcon} URGENT: Apartment Lease Renewal Due Soon";
            $message = "Store #{$lease->store_number} apartment lease renewal is due in {$daysUntilRenewal} days.";
        } else {
            $title = "{$statusIcon} Apartment Lease Renewal Scheduled";
            $message = "Store #{$lease->store_number} apartment lease renewal is due in {$daysUntilRenewal} days.";
        }

        $this->sendBrowserNotification($user, $title, $message, [
            'type' => 'apartment_lease_renewal',
            'lease_id' => $lease->id,
            'store_number' => $lease->store_number,
            'days_until_renewal' => $daysUntilRenewal,
            'urgency' => $daysUntilRenewal <= 0 ? 'overdue' : ($daysUntilRenewal <= 7 ? 'critical' : 'high'),
            'url' => route('admin.apartment-leases.show', $lease->id)
        ]);
    }

    /**
     * Send lease expiration warning notification
     */
    public function sendLeaseExpirationWarning(User $user, $lease, string $leaseType, int $daysUntilExpiration): void
    {
        $statusIcon = $daysUntilExpiration <= 0 ? '🚨' : ($daysUntilExpiration <= 7 ? '⚡' : '⚠️');

        if ($daysUntilExpiration <= 0) {
            $title = "{$statusIcon} EXPIRED: {$leaseType}";
            $message = "Lease has been expired for " . abs($daysUntilExpiration) . " days! Immediate action required.";
        } elseif ($daysUntilExpiration <= 7) {
            $title = "{$statusIcon} CRITICAL: {$leaseType} Expires Soon";
            $message = "Lease expires in {$daysUntilExpiration} days. Urgent action needed.";
        } else {
            $title = "{$statusIcon} WARNING: {$leaseType} Expires Soon";
            $message = "Lease expires in {$daysUntilExpiration} days.";
        }

        $routeName = $lease instanceof ApartmentLease ? 'admin.apartment-leases.show' : 'admin.leases.show';

        $this->sendBrowserNotification($user, $title, $message, [
            'type' => 'lease_expiration',
            'lease_id' => $lease->id,
            'lease_type' => $leaseType,
            'days_until_expiration' => $daysUntilExpiration,
            'urgency' => $daysUntilExpiration <= 0 ? 'overdue' : ($daysUntilExpiration <= 7 ? 'critical' : 'high'),
            'url' => route($routeName, $lease->id)
        ]);
    }

    /**
     * Get notification count for badge display
     */
    public function getNotificationCount(): array
    {
        $notifications = $this->getCurrentNotifications();

        return [
            'total' => $notifications['count'],
            'critical' => $notifications['critical_count'],
            'has_notifications' => $notifications['count'] > 0
        ];
    }

    /**
     * Mark calendar notification as seen
     */
    public function markCalendarNotificationAsSeen(string $notificationId): bool
    {
        try {
            // You can implement this based on your notification storage strategy
            // For now, we'll just log it
            Log::info('Calendar notification marked as seen: ' . $notificationId);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as seen: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all current calendar notifications
     */
    public function clearCalendarNotifications(): bool
    {
        try {
            // Implement clearing logic based on your needs
            Log::info('Calendar notifications cleared');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear notifications: ' . $e->getMessage());
            return false;
        }
    }

}
