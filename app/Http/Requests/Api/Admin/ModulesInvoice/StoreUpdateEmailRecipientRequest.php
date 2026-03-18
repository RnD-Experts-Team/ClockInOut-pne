<?php
namespace App\Http\Requests\Api\Admin\ModulesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateEmailRecipientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'is_default' => 'boolean',
        ];
    }
}