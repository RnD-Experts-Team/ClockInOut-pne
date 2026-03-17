<?php
use App\Http\Controllers\Api\Admin\AdminActivityController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('admin-activity')->name('admin-activity.')->group(function () {

        Route::get('/', [AdminActivityController::class, 'index'])->name('index');
        Route::get('/{activity}', [AdminActivityController::class, 'show'])->name('show');
        Route::get('/daily/{date}', [AdminActivityController::class, 'getDailyActivity'])->name('daily');
        Route::get('/api/stats', [AdminActivityController::class, 'getStats'])->name('stats');
        Route::post('/filter', [AdminActivityController::class, 'filter'])->name('filter');
        });
});
      