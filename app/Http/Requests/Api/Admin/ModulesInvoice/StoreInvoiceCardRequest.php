<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'arrival_odometer' => 'nullable|numeric|min:0',
            'arrival_odometer_image' => 'nullable|image',
            'maintenance_request_ids' => 'nullable|array',
            'maintenance_request_ids.*' => 'exists:maintenance_requests,id',
        ];
    }
}