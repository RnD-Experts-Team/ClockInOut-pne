<?php
use App\Http\Controllers\Api\Admin\ScheduleController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->group(function () {
   
    Route::delete('schedules/shift-types', [ScheduleController::class, 'deleteShiftType'])
        ->name('schedules.delete-shift-type');
    Route::delete('schedules/roles', [ScheduleController::class, 'deleteRole'])->name('schedules.delete-role');
    // === SCHEDULE MANAGEMENT ROUTES - ADMIN ONLY ===
    Route::resource('schedules', ScheduleController::class);

    // Schedule Actions
    Route::post('schedules/{schedule}/publish', [ScheduleController::class, 'publish'])->name('schedules.publish');
    Route::post('schedules/{schedule}/activate', [ScheduleController::class, 'activate'])->name('schedules.activate');

});