<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store')->id;

        return [
            'store_number' => "required|string|max:255|unique:stores,store_number,$storeId",
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'nullable',
        ];
    }
}