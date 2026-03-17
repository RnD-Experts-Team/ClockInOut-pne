<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleMaintenanceRequest extends FormRequest
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
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:15|max:480',
            'priority' => 'required|in:low,normal,high,urgent',
            'asset_id' => 'nullable|integer',
            'assigned_user_id' => 'nullable|exists:users,id',
            'maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
        ];
    }
}