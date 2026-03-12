<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,in_progress,completed',
            'costs' => 'required_if:status,completed|nullable|numeric|min:0',
            'how_we_fixed_it' => 'required_if:status,completed|nullable|string|max:1000',
        ];
    }
}