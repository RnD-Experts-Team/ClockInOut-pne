<?php

namespace App\Http\Requests\Api\Admin\ApartmentLease;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRenewalRequest extends FormRequest
{
    public function rules()
    {
        return [
            'completion_notes'=>'nullable|string|max:1000'
        ];
    }
}