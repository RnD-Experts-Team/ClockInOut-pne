<?php

use App\Http\Controllers\Admin\ApartmentLeaseController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClockingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MaintenanceRequestController;

Route::get('/', function () {
    return view('auth.login');
});
Route::get('/leases/landlord-contact', [LeaseController::class, 'landlordContact'])->name('leases.landlord-contact');
Route::get('/leases/cost-breakdown', [LeaseController::class, 'costBreakdown'])->name('leases.cost-breakdown');
Route::get('/leases/lease-tracker', [LeaseController::class, 'leaseTracker'])->name('leases.lease-tracker');
//export Clock in out data to excel
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
    Route::post('/admin/leases/screenshot/landlord-contact', [LeaseController::class, 'generateLandlordContactScreenshot'])->name('leases.screenshot.landlord-contact');
    Route::post('/admin/leases/screenshot/cost-breakdown', [LeaseController::class, 'generateCostBreakdownScreenshot'])->name('leases.screenshot.cost-breakdown');
    Route::post('/admin/leases/screenshot/lease-tracker', [LeaseController::class, 'generateLeaseTrackerScreenshot'])->name('leases.screenshot.lease-tracker');
    Route::get('/download/screenshot/{filename}', [LeaseController::class, 'downloadScreenshot'])->name('download.screenshot');
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

// FIXED: Apartment Lease Routes - Properly organized
Route::prefix('admin')->name('admin.')->middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    // Custom routes BEFORE resource routes to prevent conflicts
    Route::get('apartment-leases/export', [ApartmentLeaseController::class, 'export'])->name('apartment-leases.export');
    Route::get('apartment-leases/import/form', [ApartmentLeaseController::class, 'importForm'])->name('apartment-leases.import.form');
    Route::post('apartment-leases/import', [ApartmentLeaseController::class, 'import'])->name('apartment-leases.import');
    Route::post('apartment-leases/analytics', [ApartmentLeaseController::class, 'analytics'])->name('apartment-leases.analytics');
    Route::get('apartment-leases/list', [ApartmentLeaseController::class, 'list'])->name('apartment-leases.list');

    // Resource routes AFTER custom routes
    Route::resource('apartment-leases', ApartmentLeaseController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::patch('maintenance-requests/{maintenanceRequest}/status', [MaintenanceRequestController::class, 'updateStatus'])
        ->name('maintenance-requests.update-status');

    Route::patch('maintenance-requests/bulk-status', [MaintenanceRequestController::class, 'bulkUpdateStatus'])
        ->name('maintenance-requests.bulk-update-status');

    Route::get('maintenance-requests-export', [MaintenanceRequestController::class, 'export'])
        ->name('maintenance-requests.export');

    Route::resource('maintenance-requests', MaintenanceRequestController::class)->only([
        'index', 'show', 'destroy'
    ]);

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


    Route::get('/leases/export/landlord-contact-image', [LeaseController::class, 'exportLandlordContactImage'])->name('leases.export.landlord-contact-image');
    Route::get('/leases/export/cost-breakdown-image', [LeaseController::class, 'exportCostBreakdownImage'])->name('leases.export.cost-breakdown-image');
    Route::get('/leases/export/lease-tracker-image', [LeaseController::class, 'exportLeaseTrackerImage'])->name('leases.export.lease-tracker-image');


});

require __DIR__.'/auth.php';
