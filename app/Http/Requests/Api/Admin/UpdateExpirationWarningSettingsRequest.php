<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpirationWarningSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expiration_ids' => ['required', 'array'],
            'expiration_ids.*' => ['exists:expiration_trackings,id'],
            'warning_days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }
}