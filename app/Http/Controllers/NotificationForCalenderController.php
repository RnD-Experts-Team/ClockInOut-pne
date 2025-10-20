<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationForCalenderController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get current real-time notifications
     */
    public function getCurrent()
    {
        try {
            $notifications = $this->notificationService->getCurrentNotifications();
            return response()->json($notifications);
        } catch (\Exception $e) {
            \Log::error('NotificationController getCurrent error: ' . $e->getMessage());

            return response()->json([
                'notifications' => [],
                'count' => 0,
                'critical_count' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark notification as seen
     */
    public function markSeen(Request $request)
    {
        try {
            $notificationId = $request->get('id');
            $result = $this->notificationService->markCalendarNotificationAsSeen($notificationId);

            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Clear all notifications
     */
    public function clear()
    {
        try {
            $result = $this->notificationService->clearCalendarNotifications();
            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
