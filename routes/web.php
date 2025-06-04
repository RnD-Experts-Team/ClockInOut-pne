<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClockingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RoleMiddleware; // Import the middleware
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AttendanceController;


Route::get('/', function () {
    return view('auth.login');
});
//export Clcok in out data to excel
Route::get('/export-clocking/{startDate?}/{endDate?}', [ExportController::class, 'exportToExcel'])->name('export.clocking');

Route::middleware(['role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});
// Dashboard - Only for Admins
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', RoleMiddleware::class . ':admin'])->name('dashboard');

// Clocking - Only for Users
Route::middleware(['auth', RoleMiddleware::class . ':user'])->group(function () {
    Route::get('/clocking', [ClockingController::class, 'index'])->name('clocking.index');
    Route::post('/clock-in', [ClockingController::class, 'clockIn'])->name('clocking.clockIn');
    Route::post('/clock-out', [ClockingController::class, 'clockOut'])->name('clocking.clockOut');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
});

// Profile Routes - Available to All Authenticated Users
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/admin/clockings', [ClockingController::class, 'ClockingTable'])->name('admin.clocking');
    Route::post('/admin/clocking/update-gas-rate', [ClockingController::class, 'updateGasRate'])
    ->name('admin.clocking.updateGasRate');

    Route::delete('/admin/clocking/{id}', [ClockingController::class, 'destroy'])->name('admin.clocking.destroy');
});

Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/admin/clockings/export', [ExportController::class, 'exportCSV'])->name('admin.clocking.export');
});


Route::post('/admin/clocking/update', [ClockingController::class, 'updateClocking'])
    ->name('admin.clocking.update');

require __DIR__.'/auth.php';
