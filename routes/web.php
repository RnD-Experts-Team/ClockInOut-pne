<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClockingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/clock-in', [ClockingController::class, 'clockIn'])->name('clocking.clockIn');
    Route::post('/clock-out', [ClockingController::class, 'clockOut'])->name('clocking.clockOut');
});
Route::middleware('auth')->get('/clocking', [ClockingController::class, 'index'])->name('clocking.index');

require __DIR__.'/auth.php';
