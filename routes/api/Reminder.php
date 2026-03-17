<?php
use App\Http\Controllers\Api\Admin\ReminderController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
      // Reminders System
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->name('index');
        Route::post('/', [ReminderController::class, 'store'])->name('store');
        Route::get('/{reminder}', [ReminderController::class, 'show'])->name('show');
        Route::put('/{reminder}', [ReminderController::class, 'update'])->name('update');
        Route::post('/{reminder}/dismiss', [ReminderController::class, 'dismiss'])->name('dismiss');
        Route::post('/{reminder}/snooze', [ReminderController::class, 'snooze'])->name('snooze');
        Route::post('/{reminder}/mark-read', [ReminderController::class, 'markAsRead'])->name('mark-read');
        Route::get('/api/due', [ReminderController::class, 'getDueReminders'])->name('due');
        Route::post('/bulk-action', [ReminderController::class, 'bulkAction'])->name('bulk-action');
    });
    Route::get('/check-reminders', [ReminderController::class, 'checkPendingReminders']);
    Route::post('/reminders/{id}/dismiss', [ReminderController::class, 'dismissReminder']);
});
 
