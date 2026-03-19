<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class AddRemoveTaskRequest extends FormRequest
{
    public function authorize()
    {
         return true;
    }

    public function rules()
    {
        return [
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
        ];
    }

  
}