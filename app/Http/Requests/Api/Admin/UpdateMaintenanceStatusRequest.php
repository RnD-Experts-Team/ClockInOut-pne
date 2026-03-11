<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:on_hold,received,in_progress,done,canceled',
            'reason' => 'required_if:status,on_hold|nullable|string|max:1000',
            'costs' => 'required_if:status,done|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,done|nullable|string|max:1000',
            'assigned_to' => 'required_if:status,in_progress,done|nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'progress_description' => 'nullable|string|max:1000',
            'task_end_date' => 'required_if:status,done|nullable|date', // NEW: Manual input field
        ];
    }
}