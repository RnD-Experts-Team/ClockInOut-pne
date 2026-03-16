<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
        ];
    }
}