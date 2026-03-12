<?php

use App\Http\Controllers\Api\Admin\NativeRequestController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::prefix('requests')
    ->middleware(['auth:sanctum', RoleMiddleware::class . ':store_manager'])
    ->name('native.requests.')
    ->group(function () {
        Route::post('/', [NativeRequestController::class, 'store'])->name('store');
        Route::get('/', [NativeRequestController::class, 'index'])->name('index');
        Route::get('/{request}', [NativeRequestController::class, 'show'])->name('show');
    });