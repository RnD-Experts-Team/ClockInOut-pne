<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
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
            'assigned_user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date|after_or_equal:today',
            'assigned_time' => 'nullable|date_format:H:i',
            'estimated_hours' => 'nullable|numeric|min:0.5|max:12',
            'priority' => 'required|in:low,normal,high,urgent',
            'task_type' => 'required|in:maintenance,inspection,cleaning,repair,installation',
            'task_assignment_id' => 'nullable|exists:task_assignments,id',
        ];
    }
}