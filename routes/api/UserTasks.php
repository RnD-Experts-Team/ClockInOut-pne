<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserTaskController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('my-tasks', [UserTaskController::class, 'index'])->name('user.tasks.index');
    Route::patch('my-tasks/{taskAssignment}/status', [UserTaskController::class, 'updateStatus'])
        ->name('user.tasks.update-status');
});