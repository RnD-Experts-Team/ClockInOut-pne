<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGasRateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'gas_payment_rate' => 'required|numeric|min:0'
        ];
    }

}