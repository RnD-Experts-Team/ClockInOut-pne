<?php

use App\Http\Controllers\ReminderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceWebhookController;
use App\Http\Controllers\MaintenanceRequestController;

Route::post('/webhook/maintenance', [MaintenanceWebhookController::class, 'handleWebhook']);

// API endpoint to get maintenance requests by store
Route::get('/maintenance-requests/by-store/{storeId}', [MaintenanceRequestController::class, 'getByStore']);

