<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClockOutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'miles_out' => 'nullable|integer|min:0',
            'image_out' => 'nullable|image|mimes:jpg,png,jpeg',
        ];
    }
}