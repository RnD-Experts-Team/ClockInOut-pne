<?php

namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateInvoiceEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'is_default' => 'boolean',
        ];
    }
}