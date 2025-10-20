<?php

namespace App\Http\Controllers;

use App\Models\CalendarReminder;
use App\Models\CalendarEvent;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ReminderController extends Controller
{
    protected ReminderService $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Reminders management page
     */
    public function index()
    {
        $reminders = CalendarReminder::latest()->paginate(12);

        $statistics = [
            'total' => CalendarReminder::count(),
            'pending' => CalendarReminder::where('status', 'pending')->count(),
            'overdue' => CalendarReminder::where('status', 'pending')
                ->whereDate('reminder_date', '<', Carbon::today())->count(),
            'upcoming_7_days' => CalendarReminder::whereBetween('reminder_date', [Carbon::today(), Carbon::today()->addDays(7)])->count(),
            'sent' => CalendarReminder::where('status', 'sent')->count(),
            'by_type' => CalendarReminder::select('reminder_type', DB::raw('count(*) as c'))
                ->groupBy('reminder_type')
                ->pluck('c', 'reminder_type')
                ->toArray(),
        ];

        return view('admin.reminders.index', compact('reminders', 'statistics'));
    }

    /**
     * Create reminder page
     */
    public function create(Request $request): View
    {
        $calendarEventId = $request->get('calendar_event_id');
        $calendarEvent = null;

        if ($calendarEventId) {
            $calendarEvent = CalendarEvent::find($calendarEventId);
        }

        return view('admin.reminders.create', compact('calendarEvent'));
    }

    /**
     * Store new reminder (ENHANCED FOR NOTIFICATIONS)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_date' => 'required|date|after_or_equal:today',
            'reminder_time' => 'required|date_format:H:i',
            'reminder_type' => 'required|in:maintenance_followup,custom_reminder,expiration_alert,lease_renewal,payment_due',
            'calendar_event_id' => 'nullable|exists:calendar_events,id',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'notification_methods' => 'nullable|array',
            'notification_methods.*' => 'in:email,browser,sms',
            'related_model_type' => 'nullable|string',
            'related_model_id' => 'nullable|integer',
        ]);

        // ➕ ENHANCED: Create full datetime for accurate polling
        $reminderDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['reminder_date'] . ' ' . $validated['reminder_time']
        );

        $reminder = CalendarReminder::create(array_merge($validated, [
            'admin_user_id' => auth()->id(),
            'status' => 'pending',

            // ➕ NEW: Notification system fields
            'notification_status' => 'pending',      // Ready for polling
            'notified_at' => null,                   // Not notified yet
            'reminder_time' => $reminderDateTime,    // Full timestamp for polling
        ]));

        // Create calendar event if missing
        if (!$reminder->calendar_event_id) {
            $calendarEvent = CalendarEvent::create([
                'title'       => 'Reminder: ' . $reminder->title,
                'description' => $reminder->description,
                'event_type'  => 'reminder',
                'start_date'  => $reminder->reminder_date,
                'start_time'  => $reminder->reminder_time,
                'is_all_day'  => false,
                'color_code'  => '#ffc107',
                'created_by'  => auth()->id(),
            ]);

            $reminder->update(['calendar_event_id' => $calendarEvent->id]);
        }

        // ➕ NEW: Clear cache so polling picks up new reminder immediately
        $cacheKey = "pending_reminders_user_" . auth()->id();
        Cache::forget($cacheKey);

        // ➕ ENHANCED: Better success message with notification timing
        $message = 'Reminder created successfully! ';
        if ($reminderDateTime->isFuture()) {
            $message .= 'You will be notified on ' . $reminderDateTime->format('M j, Y \a\t g:i A');
        } else {
            $message .= 'This reminder is overdue and will notify you within 5 minutes.';
        }

        // Redirect to the show page
        return redirect()
            ->route('reminders.show', $reminder)
            ->with('status', $message);
    }

    /**
     * Show reminder details
     */
    public function show(CalendarReminder $reminder): View
    {
        // Load safe relationships only
        $reminder->load(['calendarEvent', 'adminUser']);

        return view('admin.reminders.show', compact('reminder'));
    }

    /**
     * Edit reminder page
     */
    public function edit(CalendarReminder $reminder): View
    {
        return view('admin.reminders.edit', [
            'reminder' => $reminder,
        ]);
    }

    /**
     * Update reminder (ENHANCED FOR NOTIFICATIONS)
     */
    public function update(Request $request, CalendarReminder $reminder)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_date' => 'required|date',
            'reminder_time' => 'required|date_format:H:i',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'notification_methods' => 'nullable|array',
        ]);

        // ➕ ENHANCED: Update reminder_time as full datetime
        $reminderDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['reminder_date'] . ' ' . $validated['reminder_time']
        );

        $reminder->update(array_merge($validated, [
            'reminder_time' => $reminderDateTime,
            // ➕ NEW: Reset notification status if time changed
            'notification_status' => $reminderDateTime->isFuture() ? 'pending' : $reminder->notification_status,
        ]));

        // Sync calendar event if present
        if ($reminder->calendarEvent) {
            $reminder->calendarEvent->update([
                'title'       => 'Reminder: ' . $reminder->title,
                'description' => $reminder->description,
                'start_date'  => $reminder->reminder_date,
                'start_time'  => $reminder->reminder_time,
            ]);
        }

        // ➕ NEW: Clear cache after update
        $cacheKey = "pending_reminders_user_" . auth()->id();
        Cache::forget($cacheKey);

        return redirect()
            ->route('reminders.show', $reminder)
            ->with('status', 'Reminder updated successfully');
    }

    /**
     * Dismiss reminder (existing method)
     */
    public function dismiss(CalendarReminder $reminder): JsonResponse
    {
        $reminder->dismiss();

        return response()->json([
            'success' => true,
            'message' => 'Reminder dismissed'
        ]);
    }

    /**
     * Snooze reminder (existing method)
     */
    public function snooze(Request $request, CalendarReminder $reminder): JsonResponse
    {
        $minutes = $request->validate([
            'minutes' => 'required|integer|min:5|max:1440'
        ])['minutes'];

        $reminder->snooze($minutes);

        return response()->json([
            'success' => true,
            'message' => 'Reminder snoozed for ' . $minutes . ' minutes'
        ]);
    }

    /**
     * Get due reminders (existing AJAX method)
     */
    public function getDueReminders(): JsonResponse
    {
        $dueReminders = CalendarReminder::byUser(auth()->id())
            ->dueSoon(24)
            ->with('calendarEvent')
            ->get();

        $overdueReminders = CalendarReminder::byUser(auth()->id())
            ->overdue()
            ->with('calendarEvent')
            ->get();

        return response()->json([
            'due_reminders' => $dueReminders->map(function ($reminder) {
                return [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'description' => $reminder->description,
                    'reminder_datetime' => $reminder->getFormattedReminderDateTime(),
                    'type' => $reminder->reminder_type,
                    'is_overdue' => $reminder->shouldSend(),
                ];
            }),
            'overdue_reminders' => $overdueReminders->map(function ($reminder) {
                return [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'description' => $reminder->description,
                    'reminder_datetime' => $reminder->getFormattedReminderDateTime(),
                    'type' => $reminder->reminder_type,
                ];
            }),
            'total_due' => $dueReminders->count(),
            'total_overdue' => $overdueReminders->count(),
        ]);
    }

    /**
     * Mark reminder as read (existing method)
     */
    public function markAsRead(CalendarReminder $reminder): JsonResponse
    {
        $reminder->markAsSent();

        return response()->json([
            'success' => true,
            'message' => 'Reminder marked as read'
        ]);
    }

    /**
     * Bulk actions on reminders (existing method)
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,delete,mark_read',
            'reminder_ids' => 'required|array',
            'reminder_ids.*' => 'exists:calendar_reminders,id'
        ]);

        $reminders = CalendarReminder::whereIn('id', $validated['reminder_ids'])
            ->where('admin_user_id', auth()->id())
            ->get();

        $count = 0;
        foreach ($reminders as $reminder) {
            switch ($validated['action']) {
                case 'dismiss':
                    $reminder->dismiss();
                    $count++;
                    break;
                case 'delete':
                    $reminder->delete();
                    $count++;
                    break;
                case 'mark_read':
                    $reminder->markAsSent();
                    $count++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' reminders updated successfully'
        ]);
    }

    // ➕ ========================================
    // ➕ NEW NOTIFICATION POLLING METHODS
    // ➕ ========================================

    /**
     * Check for pending reminders (5-minute polling endpoint)
     * ➕ NEW METHOD FOR NOTIFICATION SYSTEM
     */
    public function checkPendingReminders(Request $request): JsonResponse
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        try {
            // ➕ FIX: Use local timezone for comparison
            $currentTime = \Carbon\Carbon::now(); // This uses your app timezone

            // Debug current time
            \Log::info("Current time (app timezone): " . $currentTime);
            \Log::info("Current time (UTC): " . $currentTime->utc());

            $reminders = CalendarReminder::where('admin_user_id', $userId)
                ->where('notification_status', 'pending')
                // ➕ FIX: Compare with local time, not UTC
                ->whereTime('reminder_time', '<=', $currentTime->format('H:i:s'))
                ->whereDate('reminder_date', '<=', $currentTime->format('Y-m-d'))
                ->orderBy('reminder_time', 'asc')
                ->get();

            \Log::info("Found reminders: " . $reminders->count());

            // Mark as notified
            foreach ($reminders as $reminder) {
                $reminder->update([
                    'notification_status' => 'shown',
                    'notified_at' => $currentTime
                ]);
            }

            return response()->json([
                'success' => true,
                'reminders' => $reminders->map(function ($reminder) {
                    return [
                        'id' => $reminder->id,
                        'title' => $reminder->title,
                        'description' => $reminder->description,
                        'reminder_time' => $reminder->reminder_time,
                        'priority' => 'normal',
                        'status' => $reminder->notification_status,
                        'type' => $reminder->reminder_type ?? 'custom_reminder'
                    ];
                }),
                'count' => $reminders->count(),
                'notified_count' => $reminders->count(),
                'timestamp' => $currentTime->toISOString(),
                'debug_current_time' => $currentTime->format('Y-m-d H:i:s'),
                'debug_timezone' => config('app.timezone')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking pending reminders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error checking reminders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dismiss a reminder via API
     * ➕ NEW METHOD FOR NOTIFICATION SYSTEM
     */
    public function dismissReminder(Request $request, $id): JsonResponse
    {
        try {
            $reminder = CalendarReminder::where('admin_user_id', auth()->id())
                ->findOrFail($id);

            $reminder->update([
                'notification_status' => 'dismissed',
                'notified_at' => Carbon::now()
            ]);

            // Clear user's cache
            $cacheKey = "pending_reminders_user_" . auth()->id();
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Reminder dismissed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error dismissing reminder: ' . $e->getMessage(), [
                'reminder_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error dismissing reminder'
            ], 500);
        }
    }

    /**
     * Complete a reminder via API
     * ➕ NEW METHOD FOR NOTIFICATION SYSTEM
     */
    public function completeReminder(Request $request, $id): JsonResponse
    {
        try {
            $reminder = CalendarReminder::where('admin_user_id', auth()->id())
                ->findOrFail($id);

            $reminder->update([
                'notification_status' => 'completed',
                'status' => 'completed',
                'notified_at' => Carbon::now()
            ]);

            // Clear user's cache
            $cacheKey = "pending_reminders_user_" . auth()->id();
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Reminder marked as completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Error completing reminder: ' . $e->getMessage(), [
                'reminder_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error completing reminder'
            ], 500);
        }
    }

    /**
     * Get notification statistics for dashboard
     * ➕ NEW METHOD FOR NOTIFICATION SYSTEM
     */
    public function getNotificationStats(): JsonResponse
    {
        $userId = auth()->id();

        try {
            $stats = [
                'pending' => CalendarReminder::where('admin_user_id', $userId)
                    ->where('notification_status', 'pending')
                    ->count(),

                'overdue' => CalendarReminder::where('admin_user_id', $userId)
                    ->where('notification_status', 'pending')
                    ->where('reminder_time', '<', Carbon::now())
                    ->count(),

                'today' => CalendarReminder::where('admin_user_id', $userId)
                    ->where('notification_status', 'pending')
                    ->whereDate('reminder_date', Carbon::today())
                    ->count(),

                'this_week' => CalendarReminder::where('admin_user_id', $userId)
                    ->where('notification_status', 'pending')
                    ->whereBetween('reminder_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => Carbon::now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting notification stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting notification statistics'
            ], 500);
        }
    }
}
