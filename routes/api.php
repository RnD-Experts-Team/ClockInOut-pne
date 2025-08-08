<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceWebhookController;

Route::post('/webhook/maintenance', [MaintenanceWebhookController::class, 'handleWebhook']);

