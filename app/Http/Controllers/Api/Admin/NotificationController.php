<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;

use App\Models\WebhookNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\Api\Admin\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        Log::info('📡 Notifications API called', [
            'user_id' => auth()->id(),
            'params' => $request->all()
        ]);

        try {

            $data = $this->notificationService->getNotifications($request);

            Log::info('✅ Notifications loaded successfully', [
                'count' => count($data['notifications']),
                'unread' => $data['unread_count'],
                'has_more' => $data['has_more']
            ]);

            return response()->json($data);

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

    public function unread(): JsonResponse
    {
        try {

            $data = $this->notificationService->getUnread();

            return response()->json($data);

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

    public function markAsRead(WebhookNotification $notification): JsonResponse
    {
        try {

            $data = $this->notificationService->markAsRead($notification);

            return response()->json($data);

        } catch (\Exception $e) {

            Log::error('Error marking notification as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    public function markAllAsRead(): JsonResponse
    {
        try {

            $data = $this->notificationService->markAllAsRead();

            return response()->json($data);

        } catch (\Exception $e) {

            Log::error('Error marking all notifications as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        try {

            $period = $request->input('period');

            $data = $this->notificationService->clear($period);

            return response()->json($data);

        } catch (\Exception $e) {

            Log::error('Error clearing notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to clear notifications'
            ], 500);
        }
    }
}