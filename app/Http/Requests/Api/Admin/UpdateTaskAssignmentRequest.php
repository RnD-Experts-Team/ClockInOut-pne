<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'assigned_user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:normal,high,urgent',
            'due_date' => 'nullable|date|after:now',
            'assignment_notes' => 'nullable|string|max:1000'
        ];
    }
}