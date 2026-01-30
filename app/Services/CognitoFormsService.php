<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class CognitoFormsService
{
    protected $baseUrl = 'https://www.cognitoforms.com/api/forms/';
    protected $apiToken  ;

    public function __construct()
    {
        $this->apiToken = config('services.cognito_forms.api_token'); // Store token in config/services.php
    }

    /**
     * Update a Cognito Forms entry
     *
     * @param string $formId
     * @param string $entryId
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function updateEntry(string $formId, string $entryId, array $data): array
    {
        $maxRetries = 3;
        $retryDelay = 1000; // ms

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->retry(2, 100)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])->patch($this->baseUrl . $formId . '/entries/' . $entryId,
                    $data ,
                );
            // Log the request and response
            Log::debug('Cognito Forms API Request', [
                'url' => $this->baseUrl . $formId . '/entries/' . $entryId,
                'payload' => $data,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'attempt' => $attempt,
            ]);

            if ($response->status() === 429 && $attempt < $maxRetries) {
                usleep($retryDelay * 1000);
                continue;
            }
            if ($response->status() === 404) {
                // Entry not found in Cognito Forms, update local database
                $maintenanceRequest = MaintenanceRequest::where('entry_number', $entryId)->first();
                if ($maintenanceRequest) {
                    $maintenanceRequest->update([
                        'status' => $data['status'] ?? $maintenanceRequest->status,
                        'costs' => $data['costs'] ?? $maintenanceRequest->costs,
                        'how_we_fixed_it' => $data['how_we_fixed_it'] ?? $maintenanceRequest->how_we_fixed_it,
                        'not_in_cognito' => true,
                    ]);
                    Log::info('Maintenance request updated in local database due to 404 from Cognito Forms', [
                        'entry_id' => $entryId,
                        'data' => $data,
                    ]);
                    return ['success' => true, 'message' => 'Entry not found in Cognito Forms, updated locally'];
                } else {
                    throw new \Exception('Maintenance request not found in local database for entry ID: ' . $entryId);
                }
            }


            if ($response->failed()) {
                throw new \Exception('Cognito Forms API error: ' . $response->body() . ' (Status: ' . $response->status() . ')');
            }

            return $response->json();
        }

        throw new \Exception('Cognito Forms API rate limit exceeded after retries.');
    }
}
