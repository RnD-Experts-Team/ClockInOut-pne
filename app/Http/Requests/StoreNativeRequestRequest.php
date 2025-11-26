<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNativeRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only store managers can create native requests
        return auth()->user()->role === 'store_manager';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'equipment_with_issue' => ['required', 'string', 'max:255'],
            'description_of_issue' => ['required', 'string'],
            'urgency_level_id' => ['required', 'exists:native_urgency_levels,id'],
            'basic_troubleshoot_done' => ['required', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'], // Max 5 files
            'attachments.*' => ['file', 'mimes:jpeg,png,jpg,pdf'], // No size limit
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'store_id.required' => 'Please select a store.',
            'store_id.exists' => 'The selected store is invalid.',
            'equipment_with_issue.required' => 'Please specify the equipment with issue.',
            'equipment_with_issue.max' => 'Equipment name cannot exceed 255 characters.',
            'description_of_issue.required' => 'Please provide a description of the issue.',
            'urgency_level_id.required' => 'Please select an urgency level.',
            'urgency_level_id.exists' => 'The selected urgency level is invalid.',
            'basic_troubleshoot_done.required' => 'Please indicate if basic troubleshooting was done.',
            'attachments.max' => 'You can upload a maximum of 5 files.',
            'attachments.*.mimes' => 'Attachments must be JPEG, PNG, JPG, or PDF files.',

        ];
    }
}
