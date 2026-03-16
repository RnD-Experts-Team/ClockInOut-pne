<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkReminderActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:dismiss,delete,mark_read',
            'reminder_ids' => 'required|array',
            'reminder_ids.*' => 'exists:calendar_reminders,id',
        ];
    }
}