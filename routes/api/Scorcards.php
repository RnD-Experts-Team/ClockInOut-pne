<?php

use App\Http\Controllers\Api\Admin\ScorecardController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('scorecards', [ScorecardController::class, 'index'])->name('scorecards.index');
    Route::get('scorecards/export', [ScorecardController::class, 'export'])->name('scorecards.export');
});