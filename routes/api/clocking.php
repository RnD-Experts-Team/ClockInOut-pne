<?php

use App\Http\Controllers\Api\ClockingController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
//for User
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':user'])->group(function () {
    // Route::get('/clocking', [ClockingController::class, 'index'])->name('clocking.index');
    Route::post('/clock-in', [ClockingController::class, 'clockIn'])->name('clocking.clockIn');
    Route::post('/clock-out', [ClockingController::class, 'clockOut'])->name('clocking.clockOut');
});
//for Admin
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->group(function () {
        Route::get('/admin/clockings', [ClockingController::class, 'ClockingTable'])->name('admin.clocking');
        Route::post('/admin/clocking/update-gas-rate', [ClockingController::class, 'updateGasRate'])
            ->name('admin.clocking.updateGasRate');
        Route::delete('/admin/clocking/{id}', [ClockingController::class, 'destroy'])->name('admin.clocking.destroy');
        Route::post('/admin/clocking/update', [ClockingController::class, 'updateClocking'])
             ->name('admin.clocking.update');
});