<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserScheduleController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('my-schedule', [UserScheduleController::class, 'index'])->name('user.schedule.index');

});