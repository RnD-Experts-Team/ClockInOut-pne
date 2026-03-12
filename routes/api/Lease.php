<?php

use App\Http\Controllers\Api\Admin\LeaseController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('leases/portfolio-stats', [LeaseController::class, 'getPortfolioStats'])
        ->name('leases.portfolio-stats');
 
    Route::post('leases/import', [LeaseController::class, 'import'])
        ->name('leases.import');
    Route::get('leases/download-template', [LeaseController::class, 'downloadTemplate'])
        ->name('leases.download-template');
    Route::get('leases/landlord-contact', [LeaseController::class, 'landlordContact'])->name('leases.landlord-contact');
    Route::get('leases/cost-breakdown', [LeaseController::class, 'costBreakdown'])->name('leases.cost-breakdown');
    Route::get('leases/lease-tracker', [LeaseController::class, 'leaseTracker'])->name('leases.lease-tracker');

    Route::apiResource('leases', LeaseController::class);
    Route::get('leases-export', [LeaseController::class, 'export'])
        ->name('leases.export');
});