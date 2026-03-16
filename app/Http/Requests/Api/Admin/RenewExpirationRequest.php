<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RenewExpirationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_expiration_date' => 'required|date|after:today',
            'action' => 'required|in:renew,extend',
            'notes' => 'nullable|string',
        ];
    }
}