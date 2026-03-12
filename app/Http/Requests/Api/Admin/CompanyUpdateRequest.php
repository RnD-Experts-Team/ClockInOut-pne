<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')->id;

        return [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => "nullable|email|max:255|unique:companies,email,$companyId",
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ];
    }
}