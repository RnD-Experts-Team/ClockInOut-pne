<?php

use App\Http\Controllers\Api\Admin\NotificationController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');
});