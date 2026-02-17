<?php

use App\Http\Controllers\ReminderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceWebhookController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Middleware\RoleMiddleware;


Route::post('/webhook/maintenance', [MaintenanceWebhookController::class, 'handleWebhook']);

// API endpoint to get maintenance requests by store
Route::get('/maintenance-requests/by-store/{storeId}', [MaintenanceRequestController::class, 'getByStore'])->middleware(['web', 'auth', RoleMiddleware::class . ':admin']);

Route::get(
    '/stores/{store_id}/maintenance-requests',
    [MaintenanceRequestController::class, 'getLatestByStore']
)
    ->middleware(['auth.token.store']);
// 

Route::get('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'showAPI'])->middleware(['auth.token.store']);
