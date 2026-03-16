<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpirationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expiration_date' => 'required|date',
            'warning_days' => 'integer|min:1|max:365',
            'notes' => 'nullable|string',
        ];
    }
}