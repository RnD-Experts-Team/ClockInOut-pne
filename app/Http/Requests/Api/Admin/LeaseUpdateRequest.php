<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LeaseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'store_number' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'aws' => 'nullable|numeric|min:0',
            'base_rent' => 'nullable|numeric|min:0',
            'percent_increase_per_year' => 'nullable|numeric|min:0|max:100',
            'cam' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            're_taxes' => 'nullable|numeric|min:0',
            'current_term' => 'nullable|integer|min:1|max:10',
            'others' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'franchise_agreement_expiration_date' => 'nullable|date',
            'renewal_options' => 'nullable|string|max:255',
            'initial_lease_expiration_date' => 'nullable|date',
            'sqf' => 'nullable|integer|min:0',
            'hvac' => 'nullable|boolean',
            'landlord_responsibility' => 'nullable|string',
            'landlord_name' => 'nullable|string|max:255',
            'landlord_email' => 'nullable|email|max:255',
            'landlord_phone' => 'nullable|string|max:255',
            'landlord_address' => 'nullable|string',
            'comments' => 'nullable|string',
            'renewal_date' => 'nullable|date',
            'renewal_notes' => 'nullable|string',
            'renewal_status' => 'nullable|in:pending,in_progress,completed,declined',
        ];
    }
}