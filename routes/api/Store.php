<?php

use App\Http\Controllers\Api\Admin\StoreController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('stores', StoreController::class);
    Route::patch('stores/{store}/toggle-status', [StoreController::class, 'toggleStatus'])
        ->name('stores.toggle-status');

});
