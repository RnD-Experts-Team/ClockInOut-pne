<?php

namespace App\Services\Api\Admin;

use App\Models\WebhookNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function getNotifications($request)
    {
        $perPage = min($request->get('per_page', 10), 50);
            // Query notifications with relationships

        $query = WebhookNotification::with([
            'maintenanceRequest' => function ($q) {
                $q->with(['store', 'urgencyLevel']);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->whereNull('read_at');

        $notifications = $query->paginate($perPage);

        $unreadCount = WebhookNotification::whereNull('read_at')->count();
            // Format notifications for frontend

        $formattedNotifications = $notifications->items();
                // Ensure we have the maintenance_request relationship data

        foreach ($formattedNotifications as $notification) {
            if (!$notification->maintenance_request) {
                Log::warning('Notification missing maintenance request', [
                    'id' => $notification->id
                ]);
            }
        }

        return [
            'success' => true,
            'notifications' => $formattedNotifications,
            'unread_count' => $unreadCount,
            'total' => $notifications->total(),
            'has_more' => $notifications->hasMorePages(),
            'current_page' => $notifications->currentPage(),
            'per_page' => $notifications->perPage(),
            'timestamp' => now()->toISOString()
        ];
    }

    public function getUnread()
    {
        $notifications = WebhookNotification::whereNull('read_at')
            ->with(['maintenanceRequest.store', 'maintenanceRequest.urgencyLevel'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count()
        ];
    }

    public function markAsRead($notification)
    {
        $notification->update([
            'read_at' => now()
        ]);

        Log::info('Notification marked as read', [
            'id' => $notification->id
        ]);

        return [
            'success' => true,
            'message' => 'Notification marked as read',
            'notification_id' => $notification->id
        ];
    }

    public function markAllAsRead()
    {
        $count = WebhookNotification::whereNull('read_at')->count();

        WebhookNotification::whereNull('read_at')
            ->update(['read_at' => now()]);

        Log::info('All notifications marked as read', [
            'count' => $count
        ]);

        return [
            'success' => true,
            'message' => "All {$count} notifications marked as read",
            'count' => $count
        ];
    }

    public function clear($period)
    {
        $cutoff = now()->subDays($period === 'week' ? 7 : (int)$period);
        // Delete notifications older than the cutoff

        $deleted = WebhookNotification::where('created_at', '<', $cutoff)->delete();

        return [
            'success' => true,
            'message' => "Cleared $deleted notification(s) older than $period days.",
            'deleted' => $deleted
        ];
    }
}