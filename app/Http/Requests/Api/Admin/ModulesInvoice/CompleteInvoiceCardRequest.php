<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class CompleteInvoiceCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:completed,not_done',
            'notes' => 'nullable|string|max:1000',
            'not_done_reason' => 'required_if:status,not_done|nullable|string|max:1000',
            'selected_maintenance_request_id' => 'nullable|exists:maintenance_requests,id',
        ];
    }
}