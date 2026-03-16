<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpirationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'expiration_date' => 'required|date|after:today',
            'expiration_type' => 'required|in:lease_end,officer_term,department_closure,contract_end,license_expiry',
            'warning_days' => 'integer|min:1|max:365',
            'notes' => 'nullable|string',
        ];
    }
}