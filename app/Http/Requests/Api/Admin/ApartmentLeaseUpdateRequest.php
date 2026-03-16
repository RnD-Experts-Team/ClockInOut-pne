<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApartmentLeaseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules():array
    {
        return [
            'store_id' => 'nullable|exists:stores,id',
            'new_store_number' => 'nullable|string|max:255',
            'new_store_name' => 'nullable|string|max:255',
            'store_number' => 'nullable|numeric',
            'apartment_address' => 'required|string',
            'rent' => 'required|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'number_of_AT' => 'required|integer|min:1',
            'has_car' => 'required|integer|min:0',
            'is_family' => 'nullable|in:Yes,No,yes,no',
            'expiration_date' => 'nullable|date',
            'drive_time' => 'nullable|string',
            'notes' => 'nullable|string',
            'lease_holder' => 'required|string',
            'renewal_date' => 'nullable|date',
            'renewal_status' => 'nullable|in:pending,in_prLogress,completed,declined',
            'renewal_notes' => 'nullable|string',
        ];
    }
}