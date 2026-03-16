<?php

use App\Http\Controllers\Api\Admin\ApartmentLeaseController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('apartment-leases/list', [ApartmentLeaseController::class, 'list'])->name('apartment-leases.list');
    Route::get('apartment-leases/export', [ApartmentLeaseController::class, 'export'])->name('apartment-leases.export');

    Route::apiResource('apartment-leases', ApartmentLeaseController::class);

});