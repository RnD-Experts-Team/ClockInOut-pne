<?php
use App\Http\Controllers\Api\Admin\AdminNativeRequestController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->group(function () {
  // Native Maintenance Requests - Admin
    Route::prefix('requests')
        ->name('native.')
        ->group(function () {
            Route::get('/', [AdminNativeRequestController::class, 'index'])->name('index');
            Route::get('/ticket-report', [AdminNativeRequestController::class, 'ticketReport'])->name('ticketReport');
            Route::get('/{request}', [AdminNativeRequestController::class, 'show'])->name('show');
            Route::patch('/{request}/status', [AdminNativeRequestController::class, 'updateStatus'])->name('updateStatus');
        });
});