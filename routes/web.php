<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClockingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RoleMiddleware; // Import the middleware
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MaintenanceRequestController;


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

Route::middleware(['auth'])->group(function () {
    Route::resource('maintenance-requests', MaintenanceRequestController::class)->only([
        'index', 'show', 'destroy'
    ]);
    
    Route::patch('maintenance-requests/{maintenanceRequest}/status', [MaintenanceRequestController::class, 'updateStatus'])
         ->name('maintenance-requests.update-status');
    
    Route::patch('maintenance-requests/bulk-status', [MaintenanceRequestController::class, 'bulkUpdateStatus'])
         ->name('maintenance-requests.bulk-update-status');
         
    Route::get('maintenance-requests-export', [MaintenanceRequestController::class, 'export'])
         ->name('maintenance-requests.export');

    Route::post('leases/portfolio-stats', [App\Http\Controllers\LeaseController::class, 'getPortfolioStats'])
         ->name('leases.portfolio-stats');
        Route::get('leases/import/form', [App\Http\Controllers\LeaseController::class, 'showImport'])
         ->name('leases.import.form');
    Route::post('leases/import', [App\Http\Controllers\LeaseController::class, 'import'])
         ->name('leases.import');
    Route::get('leases/download-template', [App\Http\Controllers\LeaseController::class, 'downloadTemplate'])
         ->name('leases.download-template');
    Route::resource('leases', App\Http\Controllers\LeaseController::class);
    Route::get('leases-export', [App\Http\Controllers\LeaseController::class, 'export'])
         ->name('leases.export');
});

require __DIR__.'/auth.php';
