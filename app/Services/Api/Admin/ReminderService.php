<?php

namespace App\Services\Api\Admin;

use App\Models\CalendarEvent;
use App\Models\CalendarReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function getReminders(): array
    {
        $reminders = CalendarReminder::latest()->paginate(12);

        $statistics = [
            'total' => CalendarReminder::count(),

            'pending' => CalendarReminder::where('status', 'pending')->count(),

            'overdue' => CalendarReminder::where('status', 'pending')
                ->whereDate('reminder_date', '<', Carbon::today())
                ->count(),

            'upcoming_7_days' => CalendarReminder::whereBetween(
                'reminder_date',
                [Carbon::today(), Carbon::today()->addDays(7)]
            )->count(),

            'sent' => CalendarReminder::where('status', 'sent')->count(),

            'by_type' => CalendarReminder::select('reminder_type', DB::raw('count(*) as c'))
                ->groupBy('reminder_type')
                ->pluck('c', 'reminder_type')
                ->toArray(),
        ];

        return [
            'reminders' => $reminders,
            'statistics' => $statistics
        ];
    }
    public function store(array $validated, int $adminId): array
    {
        // Create full datetime
        $reminderDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['reminder_date'] . ' ' . $validated['reminder_time']
        );

        $reminder = CalendarReminder::create(array_merge($validated, [
            'admin_user_id' => $adminId,
            'status' => 'pending',

            // Notification fields
            'notification_status' => 'pending',
            'notified_at' => null,
            'reminder_time' => $reminderDateTime,
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
                'created_by'  => $adminId,
            ]);

            $reminder->update([
                'calendar_event_id' => $calendarEvent->id
            ]);
        }

        // Clear cache
        $cacheKey = "pending_reminders_user_" . $adminId;
        Cache::forget($cacheKey);

        // Success message
        $message = 'Reminder created successfully! ';

        if ($reminderDateTime->isFuture()) {
            $message .= 'You will be notified on ' . $reminderDateTime->format('M j, Y \a\t g:i A');
        } else {
            $message .= 'This reminder is overdue and will notify you within 5 minutes.';
        }

        return [
            'reminder' => $reminder,
            'message' => $message
        ];
    }
    public function getReminder($reminder)
    {
        $reminder->load([
            'calendarEvent',
            'adminUser'
        ]);

        return $reminder;
    }
    public function updateReminder(array $validated, $reminder, int $adminId): array
    {
        // Create full datetime
        $reminderDateTime = \Carbon\Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['reminder_date'] . ' ' . $validated['reminder_time']
        );

        $reminder->update(array_merge($validated, [
            'reminder_time' => $reminderDateTime,
            'notification_status' => $reminderDateTime->isFuture()
                ? 'pending'
                : $reminder->notification_status,
        ]));

        // Sync calendar event if exists
        if ($reminder->calendarEvent) {

            $reminder->calendarEvent->update([
                'title' => 'Reminder: ' . $reminder->title,
                'description' => $reminder->description,
                'start_date' => $reminder->reminder_date,
                'start_time' => $reminder->reminder_time,
            ]);
        }

        // Clear cache
        $cacheKey = "pending_reminders_user_" . $adminId;
        \Cache::forget($cacheKey);

        return [
            'reminder' => $reminder,
            'message' => 'Reminder updated successfully'
        ];
    }
    public function dismissReminder($reminder): array
    {
        $reminder->dismiss();

        return [
            'message' => 'Reminder dismissed'
        ];
    }
    public function dismissReminderForUser(int $userId, int $id): array
    {
        $reminder = CalendarReminder::where('admin_user_id', $userId)
            ->findOrFail($id);

        $reminder->update([
            'notification_status' => 'dismissed',
            'notified_at' => Carbon::now()
        ]);

        // Clear user's cache
        $cacheKey = "pending_reminders_user_" . $userId;
        Cache::forget($cacheKey);

        return [
            'success' => true,
            'message' => 'Reminder dismissed successfully'
        ];
    }

    public function snoozeReminder($reminder, int $minutes): array
    {
        $reminder->snooze($minutes);

        return [
            'message' => 'Reminder snoozed for ' . $minutes . ' minutes'
        ];
    }
    public function markAsRead($reminder): array
    {
        $reminder->markAsSent();

        return [
            'message' => 'Reminder marked as read'
        ];
    }
    public function getDueReminders(int $userId): array
    {
        $dueReminders = \App\Models\CalendarReminder::byUser($userId)
            ->dueSoon(24)
            ->with('calendarEvent')
            ->get();

        $overdueReminders = \App\Models\CalendarReminder::byUser($userId)
            ->overdue()
            ->with('calendarEvent')
            ->get();

        return [
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
        ];
    }
    public function bulkAction(array $validated, int $adminId): array
    {
        $reminders = \App\Models\CalendarReminder::whereIn('id', $validated['reminder_ids'])
            ->where('admin_user_id', $adminId)
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

        // Optional: clear polling cache
        Cache::forget("pending_reminders_user_" . $adminId);

        return [
            'count' => $count,
            'message' => $count . ' reminders updated successfully'
        ];
    }
    public function checkPendingReminders(int $userId): array
    {
        $currentTime = Carbon::now();

        Log::info("Current time (app timezone): " . $currentTime);
        Log::info("Current time (UTC): " . $currentTime->utc());

        $reminders = CalendarReminder::where('admin_user_id', $userId)
            ->where('notification_status', 'pending')
            ->whereTime('reminder_time', '<=', $currentTime->format('H:i:s'))
            ->whereDate('reminder_date', '<=', $currentTime->format('Y-m-d'))
            ->orderBy('reminder_time', 'asc')
            ->get();

        Log::info("Found reminders: " . $reminders->count());

        foreach ($reminders as $reminder) {
            $reminder->update([
                'notification_status' => 'shown',
                'notified_at' => $currentTime
            ]);
        }

        return [
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
        ];
    }

    
}