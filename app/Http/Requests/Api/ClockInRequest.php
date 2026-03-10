<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'using_car' => 'required|boolean',
            'miles_in' => 'required_if:using_car,1|nullable|integer|min:0',
            'image_in' => 'required_if:using_car,1|nullable|image|mimes:jpg,png,jpeg',
        ];
    }
}