<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_date' => 'required|date',
            'new_time' => 'nullable|date_format:H:i',
            'estimated_hours' => 'nullable|numeric|min:0.5|max:12',
        ];
    }
}