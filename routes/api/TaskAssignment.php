<?php
use App\Http\Controllers\Api\Admin\TaskAssignmentController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->group(function () {
   // Task Management
    Route::apiResource('task-assignments', TaskAssignmentController::class)->except(['store']);
    Route::patch('task-assignments/{taskAssignment}/status', [TaskAssignmentController::class, 'updateStatus'])->name('task-assignments.update-status');
});