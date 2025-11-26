<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNativeRequestStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can update request status
        return auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,in_progress,done,canceled'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'costs' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'how_we_fixed_it' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status value. Must be pending, in_progress, done, or canceled.',
            'assigned_to.exists' => 'The selected technician is invalid.',
            'costs.numeric' => 'Costs must be a valid number.',
            'costs.min' => 'Costs cannot be negative.',
            'costs.max' => 'Costs cannot exceed $999,999.99.',
            'how_we_fixed_it.max' => 'Resolution notes cannot exceed 5000 characters.',
        ];
    }
}
