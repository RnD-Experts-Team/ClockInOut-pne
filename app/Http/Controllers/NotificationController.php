<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\WebhookNotification;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get notifications - ALWAYS returns JSON
     */
    public function index(Request $request): JsonResponse
    {
        Log::info('📡 Notifications API called', [
            'user_id' => auth()->id(),
            'params' => $request->all()
        ]);

        try {
            $perPage = min($request->get('per_page', 10), 50); // Limit max per page

            // Query notifications with relationships
            $query = WebhookNotification::with([
                'maintenanceRequest' => function($q) {
                    $q->with(['store', 'urgencyLevel']);
                }
            ])->orderBy('created_at', 'desc')
            ->whereNull('read_at')
            ;

            $notifications = $query->paginate($perPage);
            $unreadCount = WebhookNotification::whereNull('read_at')->count();

            // Format notifications for frontend
            $formattedNotifications = $notifications->items();
            foreach ($formattedNotifications as $notification) {
                // Ensure we have the maintenance_request relationship data
                if (!$notification->maintenance_request) {
                    Log::warning('Notification missing maintenance request', ['id' => $notification->id]);
                }
            }

            $response = [
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount,
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'timestamp' => now()->toISOString()
            ];

            Log::info('✅ Notifications loaded successfully', [
                'count' => count($formattedNotifications),
                'unread' => $unreadCount,
                'has_more' => $notifications->hasMorePages()
            ]);
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('❌ Error loading notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load notifications',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'notifications' => [],
                'unread_count' => 0,
                'total' => 0,
                'has_more' => false
            ], 500);
        }
    }

    /**
     * Get unread notifications
     */
    public function unread(): JsonResponse
    {
        try {
            $notifications = WebhookNotification::whereNull('read_at')
                ->with(['maintenanceRequest.store', 'maintenanceRequest.urgencyLevel'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => $notifications->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading unread notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to load unread notifications',
                'notifications' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(WebhookNotification $notification): JsonResponse
    {
        try {
            $notification->update(['read_at' => now()]);

            Log::info('Notification marked as read', ['id' => $notification->id]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'notification_id' => $notification->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $count = WebhookNotification::whereNull('read_at')->count();

            WebhookNotification::whereNull('read_at')->update(['read_at' => now()]);

            Log::info('All notifications marked as read', ['count' => $count]);

            return response()->json([
                'success' => true,
                'message' => "All {$count} notifications marked as read",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    public function clear(Request $request)
    {
        $period = $request->input('period');

        $cutoff = now()->subDays($period === 'week' ? 7 : (int)$period);

        // Delete notifications older than the cutoff
        $deleted = WebhookNotification::where('created_at', '<', $cutoff)->delete();

        return redirect()->back()->with('success', "Cleared $deleted notification(s) older than $period days.");
    }
}
