<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MaintenanceWebhookController;


Route::post('/webhook/maintenance', [MaintenanceWebhookController::class, 'handleWebhook']);

