<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'clocking_id'     => 'required|exists:clockings,id',
            'clock_in'        => 'nullable|date',
            'clock_out'       => 'nullable|date',
            'miles_in'        => 'nullable|numeric',
            'miles_out'       => 'nullable|numeric',
            'purchase_cost'   => 'nullable|numeric|min:0',
            'fixed_something' => 'required|boolean',
            'fix_description' => 'nullable|string|max:1000',
        ];
    }
}