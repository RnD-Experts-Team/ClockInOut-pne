<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;


class CompleteTaskRequest extends FormRequest
{
    public function authorize()
    {
         return true;
    }

    public function rules()
    {
        return [
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'complete_single_task' => 'nullable|boolean',
        ];
    }

 
}