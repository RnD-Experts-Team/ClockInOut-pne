<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_date' => 'required|date|after_or_equal:today',
            'new_time' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string|max:500',
        ];
    }
}