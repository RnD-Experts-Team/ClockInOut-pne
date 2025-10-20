<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\CalendarReminder;

class ReminderNotification extends Notification
{
    use Queueable;

    protected CalendarReminder $reminder;

    public function __construct(CalendarReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => $this->reminder->title,
            'message' => $this->reminder->description ?? 'You have a reminder',
            'type' => 'reminder',
            'reminder_id' => $this->reminder->id,
            'reminder_type' => $this->reminder->reminder_type,
            'created_at' => now(),
            'action_url' => route('reminders.show', $this->reminder->id),
        ]);
    }
}
