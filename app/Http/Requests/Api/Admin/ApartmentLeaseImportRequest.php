<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
class ApartmentLeaseImportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'xlsx_file'=>'required|file|mimes:xlsx,xls'
        ];
    }
}