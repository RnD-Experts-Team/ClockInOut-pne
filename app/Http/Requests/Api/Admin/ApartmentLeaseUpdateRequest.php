<?php

namespace App\Http\Requests\Api\Admin\ApartmentLease;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApartmentLeaseRequest extends FormRequest
{
    public function rules()
    {
        return [
            'apartment_address'=>'required|string',
            'rent'=>'required|numeric'
        ];
    }
}