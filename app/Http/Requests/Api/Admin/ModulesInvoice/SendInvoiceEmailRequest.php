<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class SendInvoiceEmailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'template_id' => 'nullable|exists:invoice_email_templates,id',
        ];
    }
}