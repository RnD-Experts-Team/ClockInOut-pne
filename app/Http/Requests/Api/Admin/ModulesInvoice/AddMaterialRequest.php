<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class AddMaterialRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'item_name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'receipt_photos' => 'nullable|array|max:5',
            'receipt_photos.*' => 'image|mimes:jpg,png,jpeg|max:5120',
            'maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
        ];
    }
 
}