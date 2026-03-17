<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Id' => 'required',
            'Form.Id' => 'required',
            'Name.First' => 'required',
            'Store' => 'required',
            'DescriptionOfTheIssue' => 'required',
            'UrgencyLevel' => 'required',
            'TheEquipmentWhichHasTheIssue' => 'required',
            'DidYouGoThroughTheBasicTroubleshootsAndTheIssueStillNotFixed' => 'required',
            'TodaysDate' => 'required',
            'Entry.DateSubmitted' => 'required',
            'Entry.Number' => 'required',
            'TheGroupManagerWhoReviewedThisIssueBeforeSubmittingIt.First' => 'required',
        ];
    }
}