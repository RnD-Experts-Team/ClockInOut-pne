<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,user,store_manager',
            'hourly_pay' => 'required|numeric|min:0',
            'password' => 'nullable|string|min:8',
            'managed_stores' => 'nullable|array',
            'managed_stores.*' => 'exists:stores,id',
        ];
    }
}