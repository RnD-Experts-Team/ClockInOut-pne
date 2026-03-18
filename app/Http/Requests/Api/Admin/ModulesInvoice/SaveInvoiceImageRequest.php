<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class SaveInvoiceImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image_path' => 'required|string',
        ];
    }
}