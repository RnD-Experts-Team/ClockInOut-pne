<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MaintenanceWebhookRequest;
use App\Services\Api\MaintenanceWebhookService;

class MaintenanceWebhookController extends Controller
{
    protected $service;

    public function __construct(MaintenanceWebhookService $service)
    {
        $this->service = $service;
    }

    public function handleWebhook(MaintenanceWebhookRequest $request): JsonResponse
    {
        return $this->service->handleWebhook($request);
    }
}