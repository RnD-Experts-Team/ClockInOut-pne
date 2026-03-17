<?php
use App\Http\Controllers\Api\Admin\MaintenanceRequestController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
//  
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('maintenance-requests/ticket-repossrt', [MaintenanceRequestController::class, 'ticketReport'])
            ->name('maintenance-requests.ticket-report');
        
        Route::patch('maintenance-requests/{maintenanceRequest}/status', [MaintenanceRequestController::class, 'updateStatus'])
            ->name('maintenance-requests.update-status');

        Route::patch('maintenance-requests/bulk-status', [MaintenanceRequestController::class, 'bulkUpdateStatus'])
            ->name('maintenance-requests.bulk-update-status');

        Route::get('maintenance-requests-export', [MaintenanceRequestController::class, 'export'])
            ->name('maintenance-requests.export');

        Route::resource('maintenance-requests', MaintenanceRequestController::class)->only([
            'index', 'show', 'destroy'
        ]);
});

// API endpoint to get maintenance requests by store
Route::get('/maintenance-requests/by-store/{storeId}', [MaintenanceRequestController::class, 'getByStore'])->middleware(['auth:sanctum', RoleMiddleware::class . ':admin']);

Route::get('/stores/{store_id}/maintenance-requests',[MaintenanceRequestController::class, 'getLatestByStore'])->middleware(['auth.token.store']);

Route::get('/maintenance-requests/{maintenanceRequest}', [MaintenanceRequestController::class, 'showAPI'])->middleware(['auth.token.store']);
