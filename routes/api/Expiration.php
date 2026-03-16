<?php
use App\Http\Controllers\Api\Admin\ExpirationController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
  
    // Expiration Tracking
    Route::prefix('expiration')->name('expiration.')->group(function () {
        Route::get('/', [ExpirationController::class, 'index'])->name('index');
        Route::post('/', [ExpirationController::class, 'store'])->name('store');
        Route::get('/{expiration}', [ExpirationController::class, 'show'])->name('show');
        Route::put('/{expiration}', [ExpirationController::class, 'update'])->name('update');
        Route::post('/{expiration}/renew', [ExpirationController::class, 'renew'])->name('renew');
        Route::delete('/{expiration}', [ExpirationController::class, 'destroy'])->name('destroy');
        Route::get('/api/expiring-items', [ExpirationController::class, 'getExpiringItems'])->name('expiring-items');
        Route::post('/update-warning-settings', [ExpirationController::class, 'updateWarningSettings'])->name('update-warning-settings');
    });
});
 