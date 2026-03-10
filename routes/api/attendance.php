<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
});
