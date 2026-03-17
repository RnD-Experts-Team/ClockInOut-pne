<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShowFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'sort_by' => 'nullable|in:name,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ];
    }
}