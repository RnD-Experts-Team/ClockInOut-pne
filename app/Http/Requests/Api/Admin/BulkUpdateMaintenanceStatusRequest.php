<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateMaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:maintenance_requests,id',
            'status' => 'required|in:on_hold,received,in_progress,done,canceled',
            'reason' => 'required_if:status,on_hold|nullable|string|max:1000',
            'costs' => 'required_if:status,done|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,done|nullable|string|max:1000',
            'assigned_to' => 'required_if:status,in_progress|nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today', // IMPROVED: Added validation
            'progress_description' => 'nullable|string|max:1000' // NEW
        ];
    }
}