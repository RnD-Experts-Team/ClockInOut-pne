<?php

use App\Http\Controllers\Admin\ApartmentLeaseController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClockingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserScheduleController;
use App\Http\Controllers\UserTaskController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/maintenance-requests/ticket-report', [MaintenanceRequestController::class, 'ticketReport'])
    ->name('maintenance-requests.ticket-report');

// Language switching route
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

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



    Route::get('scorecards', [\App\Http\Controllers\Admin\ScorecardController::class, 'index'])->name('scorecards.index');
    Route::get('scorecards/export', [\App\Http\Controllers\Admin\ScorecardController::class, 'export'])->name('scorecards.export');


    // Custom routes BEFORE resource routes to prevent conflicts
    Route::get('apartment-leases/export', [ApartmentLeaseController::class, 'export'])->name('apartment-leases.export');
    Route::get('apartment-leases/import/form', [ApartmentLeaseController::class, 'importForm'])->name('apartment-leases.import.form');
    Route::post('apartment-leases/import', [ApartmentLeaseController::class, 'import'])->name('apartment-leases.import');
    Route::post('apartment-leases/analytics', [ApartmentLeaseController::class, 'analytics'])->name('apartment-leases.analytics');
    Route::get('apartment-leases/list', [ApartmentLeaseController::class, 'list'])->name('apartment-leases.list');

    // Resource routes AFTER custom routes
    Route::resource('apartment-leases', ApartmentLeaseController::class);
    Route::delete('schedules/shift-types', [ScheduleController::class, 'deleteShiftType'])
        ->name('schedules.delete-shift-type');
    Route::delete('schedules/roles', [ScheduleController::class, 'deleteRole'])
        ->name('schedules.delete-role');
    // === SCHEDULE MANAGEMENT ROUTES - ADMIN ONLY ===
    Route::resource('schedules', ScheduleController::class);

    // Schedule Actions
    Route::post('schedules/{schedule}/publish', [ScheduleController::class, 'publish'])
        ->name('schedules.publish');
    Route::post('schedules/{schedule}/activate', [ScheduleController::class, 'activate'])
        ->name('schedules.activate');



    // Task Management
    Route::resource('task-assignments', TaskAssignmentController::class)
        ->except(['create', 'store']);
    Route::patch('task-assignments/{taskAssignment}/status', [TaskAssignmentController::class, 'updateStatus'])
        ->name('task-assignments.update-status');


    // API endpoints for AJAX
    Route::get('api/tasks/available', [ScheduleController::class, 'getAvailableTasks'])
        ->name('api.tasks.available');
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

    Route::resource('stores', StoreController::class);
    Route::patch('stores/{store}/toggle-status', [StoreController::class, 'toggleStatus'])
        ->name('stores.toggle-status');

    // Optional: API routes for AJAX calls
    Route::prefix('api')->group(function () {
        Route::get('stores/search', [StoreController::class, 'search'])->name('api.stores.search');
    });

    Route::post('/apartment-leases/import-xlsx', [ApartmentLeaseController::class, 'importXlsx'])->name('admin.apartment-leases.import-xlsx');
    Route::get('/leases/export/landlord-contact-image', [LeaseController::class, 'exportLandlordContactImage'])->name('leases.export.landlord-contact-image');
    Route::get('/leases/export/cost-breakdown-image', [LeaseController::class, 'exportCostBreakdownImage'])->name('leases.export.cost-breakdown-image');
    Route::get('/leases/export/lease-tracker-image', [LeaseController::class, 'exportLeaseTrackerImage'])->name('leases.export.lease-tracker-image');
    Route::get('payments/dashboard', [PaymentController::class, 'dashboard'])->name('payments.dashboard');
    Route::get('companies/export', [CompanyController::class, 'export'])->name('companies.export');
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/store-image/{store}', [PaymentController::class, 'storeImageView'])->name('payments.store-image');

    Route::resource('companies', CompanyController::class);
    Route::get('dashboard', [PaymentController::class, 'dashboard'])->name('dashboard');
    Route::resource('payments', PaymentController::class);
    Route::post('payments/portfolio-stats', [PaymentController::class, 'portfolioStats'])->name('payments.portfolio-stats');

    Route::get('payments/reports/cost-by-company', [PaymentController::class, 'costByCompanyReport'])->name('payments.cost-by-company');
    Route::get('payments/reports/monthly-report', [PaymentController::class, 'monthlyReport'])->name('payments.monthly-report');
    Route::get('payments/reports/weekly-maintenance', [PaymentController::class, 'weeklyMaintenanceReport'])->name('payments.weekly-maintenance');
    Route::get('payments/reports/cost-per-store-yearly', [PaymentController::class, 'costPerStoreYearlyReport'])->name('payments.cost-per-store-yearly');
    Route::get('payments/reports/pending-projects', [PaymentController::class, 'pendingProjectsReport'])->name('payments.pending-projects');

    // === USER SCHEDULE & TASK VIEWS - ALL AUTHENTICATED USERS ===
    Route::get('my-schedule', [UserScheduleController::class, 'index'])->name('user.schedule.index');
    Route::get('my-tasks', [UserTaskController::class, 'index'])->name('user.tasks.index');
    Route::patch('my-tasks/{taskAssignment}/status', [UserTaskController::class, 'updateStatus'])
        ->name('user.tasks.update-status');


});

require __DIR__.'/auth.php';
