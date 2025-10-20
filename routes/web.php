<?php

use App\Http\Controllers\Admin\ApartmentLeaseController;
use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Calendar\ClockEventController;
use App\Http\Controllers\Calendar\MaintenanceCalendarController;
use App\Http\Controllers\Calendar\TaskCalendarController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClockingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExpirationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationForCalenderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\RowController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserScheduleController;
use App\Http\Controllers\UserTaskController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkbookController;

// PRIORITY 1: Home Route (Most accessed)
Route::get('/', function () {
    return view('auth.login');
});

require __DIR__.'/auth.php';

// PRIORITY 2: Language switching route (Global)
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

// PRIORITY 3: User Routes (High frequency - daily use)
Route::middleware(['auth', RoleMiddleware::class . ':user'])->group(function () {
    Route::get('/', [PaymentController::class, 'dashboard']);
    Route::get('/clocking', [ClockingController::class, 'index'])->name('clocking.index');
    Route::post('/clock-in', [ClockingController::class, 'clockIn'])->name('clocking.clockIn');
    Route::post('/clock-out', [ClockingController::class, 'clockOut'])->name('clocking.clockOut');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/admin/leases/screenshot/landlord-contact', [LeaseController::class, 'generateLandlordContactScreenshot'])->name('leases.screenshot.landlord-contact');
    Route::post('/admin/leases/screenshot/cost-breakdown', [LeaseController::class, 'generateCostBreakdownScreenshot'])->name('leases.screenshot.cost-breakdown');
    Route::post('/admin/leases/screenshot/lease-tracker', [LeaseController::class, 'generateLeaseTrackerScreenshot'])->name('leases.screenshot.lease-tracker');
    Route::get('/download/screenshot/{filename}', [LeaseController::class, 'downloadScreenshot'])->name('download.screenshot');
});

// PRIORITY 4: Profile Routes (All authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// PRIORITY 5: Calendar System Routes (Core functionality)
Route::middleware(['auth'])->group(function () {
    // Main Calendar Routes
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/events', [CalendarController::class, 'getEvents'])->name('events');
        Route::get('/filters', [CalendarController::class, 'getFilters'])->name('filters');
        Route::get('/month', [CalendarController::class, 'monthView'])->name('month');
        Route::get('/week/{date?}', [CalendarController::class, 'weekView'])->name('week');
        Route::get('/daily/{date?}', [CalendarController::class, 'dailyOverview'])->name('daily');
        Route::get('/daily-events/{date}', [CalendarController::class, 'getDailyEvents'])->name('daily-events');

        // Calendar Event CRUD (Admin only)
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            Route::post('/events', [CalendarController::class, 'store'])->name('events.store');
            Route::put('/events/{event}', [CalendarController::class, 'update'])->name('events.update');
            Route::delete('/events/{event}', [CalendarController::class, 'destroy'])->name('events.destroy');
        });
    });
});

// PRIORITY 6: Business Operations (Frequent use)
Route::middleware(['auth'])->group(function () {
    Route::get('/maintenance-requests/ticket-report', [MaintenanceRequestController::class, 'ticketReport'])
        ->name('maintenance-requests.ticket-report');

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
    Route::get('/leases/landlord-contact', [LeaseController::class, 'landlordContact'])->name('leases.landlord-contact');
    Route::get('/leases/cost-breakdown', [LeaseController::class, 'costBreakdown'])->name('leases.cost-breakdown');
    Route::get('/leases/lease-tracker', [LeaseController::class, 'leaseTracker'])->name('leases.lease-tracker');

    Route::resource('leases', App\Http\Controllers\LeaseController::class);
    Route::get('leases-export', [App\Http\Controllers\LeaseController::class, 'export'])
        ->name('leases.export');

    Route::resource('stores', StoreController::class);
    Route::patch('stores/{store}/toggle-status', [StoreController::class, 'toggleStatus'])
        ->name('stores.toggle-status');

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

    Route::get('payments/dashboard', [PaymentController::class, 'dashboard'])->name('payments.dashboard');
    Route::get('companies/export', [CompanyController::class, 'export'])->name('companies.export');
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/store-image/{store}', [PaymentController::class, 'storeImageView'])->name('payments.store-image');

    // Optional: API routes for AJAX calls
    Route::prefix('api')->group(function () {
        Route::get('stores/search', [StoreController::class, 'search'])->name('api.stores.search');
    });

    Route::post('/apartment-leases/import-xlsx', [ApartmentLeaseController::class, 'importXlsx'])->name('admin.apartment-leases.import-xlsx');
    Route::get('/leases/export/landlord-contact-image', [LeaseController::class, 'exportLandlordContactImage'])->name('leases.export.landlord-contact-image');
    Route::get('/leases/export/cost-breakdown-image', [LeaseController::class, 'exportCostBreakdownImage'])->name('leases.export.cost-breakdown-image');
    Route::get('/leases/export/lease-tracker-image', [LeaseController::class, 'exportLeaseTrackerImage'])->name('leases.export.lease-tracker-image');
});

// PRIORITY 7: Admin Dashboard Routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', RoleMiddleware::class . ':admin'])->name('dashboard');

Route::middleware(['role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

// PRIORITY 8: Admin Clocking Management
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

// PRIORITY 9: Admin Notifications
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/clear', [NotificationController::class, 'clear'])->name('notifications.clear');
});

// PRIORITY 10: Admin Prefix Routes
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

// PRIORITY 11: Workbook System
Route::middleware(['auth',RoleMiddleware::class . ':admin'])->group(function () {
    Route::name('workbook.')->group(function () {
        Route::get('/workbook', [WorkbookController::class, 'index'])->name('index');

        // Columns
        Route::post('/columns', [WorkbookController::class, 'storeColumn'])->name('columns.store');
        Route::put('/columns/{column}', [WorkbookController::class, 'updateColumn'])->name('columns.update');
        Route::delete('/columns/{column}', [WorkbookController::class, 'destroyColumn'])->name('columns.destroy');

        // Rows
        Route::post('/rows', [WorkbookController::class, 'storeRow'])->name('rows.store');
        Route::put('/rows/{row}', [WorkbookController::class, 'updateRow'])->name('rows.update');
        Route::delete('/rows/{row}', [WorkbookController::class, 'destroyRow'])->name('rows.destroy');

        // Cells
        Route::post('/cells', [WorkbookController::class, 'upsertCell'])->name('cells.upsert'); // form submits can use POST
        Route::delete('/cells', [WorkbookController::class, 'destroyCell'])->name('cells.destroy');

        Route::post('/rows/{row}/save', [WorkbookController::class, 'saveRow'])
            ->name('rows.save');
    });
});

// PRIORITY 12: Calendar Extended Features
Route::middleware(['auth'])->group(function () {
    // Admin Activity Logs
    Route::prefix('admin-activity')->name('admin-activity.')->group(function () {
        Route::get('/', [AdminActivityController::class, 'index'])->name('index');
        Route::get('/{activity}', [AdminActivityController::class, 'show'])->name('show');
        Route::get('/daily/{date}', [AdminActivityController::class, 'getDailyActivity'])->name('daily');
        Route::get('/api/stats', [AdminActivityController::class, 'getStats'])->name('stats');
        Route::post('/filter', [AdminActivityController::class, 'filter'])->name('filter');
    });

    // Reminders System
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->name('index');
        Route::get('/create', [ReminderController::class, 'create'])->name('create');
        Route::post('/', [ReminderController::class, 'store'])->name('store');
        Route::get('/{reminder}', [ReminderController::class, 'show'])->name('show');
        Route::put('/{reminder}', [ReminderController::class, 'update'])->name('update');
        Route::post('/{reminder}/dismiss', [ReminderController::class, 'dismiss'])->name('dismiss');
        Route::post('/{reminder}/snooze', [ReminderController::class, 'snooze'])->name('snooze');
        Route::post('/{reminder}/mark-read', [ReminderController::class, 'markAsRead'])->name('mark-read');
        Route::get('/api/due', [ReminderController::class, 'getDueReminders'])->name('due');
        Route::post('/bulk-action', [ReminderController::class, 'bulkAction'])->name('bulk-action');
    });

    // Expiration Tracking
    Route::prefix('expiration')->name('expiration.')->group(function () {
        Route::get('/', [ExpirationController::class, 'index'])->name('index');
        Route::post('/', [ExpirationController::class, 'store'])->name('store');
        Route::get('/{expiration}', [ExpirationController::class, 'show'])->name('show');
        Route::put('/{expiration}', [ExpirationController::class, 'update'])->name('update');
        Route::post('/{expiration}/renew', [ExpirationController::class, 'renew'])->name('renew');
        Route::delete('/{expiration}', [ExpirationController::class, 'destroy'])->name('destroy');
        Route::get('/api/expiring-items', [ExpirationController::class, 'getExpiringItems'])->name('expiring-items');
        Route::post('/update-warning-settings', [ExpirationController::class, 'updateWarningSettings'])->name('update-warning-settings');
    });

    // Clock Events Management
    Route::prefix('clock-events')->name('clock-events.')->group(function () {
        Route::get('/', [ClockEventController::class, 'index'])->name('index');
        Route::post('/', [ClockEventController::class, 'store'])->name('store');
        Route::get('/{clockEvent}', [ClockEventController::class, 'show'])->name('show');
        Route::put('/{clockEvent}', [ClockEventController::class, 'update'])->name('update');
        Route::delete('/{clockEvent}', [ClockEventController::class, 'destroy'])->name('destroy');
        Route::get('/api/events', [ClockEventController::class, 'getEvents'])->name('api.events');
        Route::get('/api/work-hours-summary', [ClockEventController::class, 'getWorkHoursSummary'])->name('work-hours-summary');
    });

    // Maintenance Calendar
    Route::prefix('maintenance-calendar')->name('maintenance-calendar.')->group(function () {
        Route::get('/', [MaintenanceCalendarController::class, 'index'])->name('index');
        Route::get('/events', [MaintenanceCalendarController::class, 'getMaintenanceEvents'])->name('events');
        Route::get('/api/statistics', [MaintenanceCalendarController::class, 'getStatistics'])->name('statistics');

        // Admin only routes for scheduling
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            Route::post('/schedule', [MaintenanceCalendarController::class, 'scheduleMaintenance'])->name('schedule');
            Route::put('/reschedule/{event}', [MaintenanceCalendarController::class, 'reschedule'])->name('reschedule');
        });
    });

    // Task Calendar
    Route::prefix('task-calendar')->name('task-calendar.')->group(function () {
        Route::get('/', [TaskCalendarController::class, 'index'])->name('index');
        Route::get('/events', [TaskCalendarController::class, 'getTaskEvents'])->name('events');
        Route::get('/workload/{user}', [TaskCalendarController::class, 'getUserWorkload'])->name('workload');

        // Admin only routes for task assignment
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            Route::post('/assign', [TaskCalendarController::class, 'assignTask'])->name('assign');
            Route::put('/update-schedule/{event}', [TaskCalendarController::class, 'updateTaskSchedule'])->name('update-schedule');
        });
    });
});

// PRIORITY 13: Export Routes
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->prefix('export')->name('export.')->group(function () {
    // Daily/Range Exports
    Route::post('/day', [ExportController::class, 'exportDay'])->name('day');
    Route::post('/date-range', [ExportController::class, 'exportDateRange'])->name('date-range');

    // Calendar Exports
    Route::post('/calendar-events', [ExportController::class, 'exportCalendarEvents'])->name('calendar-events');
    Route::post('/activity-log', [ExportController::class, 'exportActivityLog'])->name('activity-log');
    Route::post('/work-hours', [ExportController::class, 'exportWorkHours'])->name('work-hours');

    // Screenshot Generation
    Route::post('/calendar-screenshot', [ExportController::class, 'generateScreenshot'])->name('calendar-screenshot');
});

// PRIORITY 14: Lower Priority Routes


Route::get('/leases/landlord-contact', [LeaseController::class, 'landlordContact'])->name('leases.landlord-contact');
Route::get('/leases/cost-breakdown', [LeaseController::class, 'costBreakdown'])->name('leases.cost-breakdown');
Route::get('/leases/lease-tracker', [LeaseController::class, 'leaseTracker'])->name('leases.lease-tracker');

//export Clock in out data to excel
Route::get('/export-clocking/{startDate?}/{endDate?}', [ExportController::class, 'exportToExcel'])->name('export.clocking');




Route::prefix('workbooks')
    ->name('workbooks.')
    ->middleware(['auth', RoleMiddleware::class . ':admin'])
    ->scopeBindings() // ✅ make nested bindings strict (workbook must belong to folder, etc.)
    ->group(function () {

        Route::redirect('/', '/workbooks/folders');

        // Folders
        Route::controller(FolderController::class)->group(function () {
            Route::get('/folders', 'index')->name('folders.index');
            Route::post('/folders', 'store')->name('folders.store');
            Route::get('/folders/{folder}', 'show')->name('folders.show');
            Route::put('/folders/{folder}', 'update')->name('folders.update');
            Route::delete('/folders/{folder}', 'destroy')->name('folders.destroy');
        });

        // Workbooks (nested under folder)
        Route::controller(WorkbookController::class)->group(function () {
            Route::post('/folders/{folder}/workbooks', 'store')->name('store');
            Route::get('/folders/{folder}/workbooks/{workbook}', 'show')->name('show');
            Route::get('/folders/{folder}/workbooks/{workbook}/edit', 'edit')->name('edit');
            Route::put('/folders/{folder}/workbooks/{workbook}', 'update')->name('update');
            Route::delete('/folders/{folder}/workbooks/{workbook}', 'destroy')->name('destroy');
        });

        // Rows (nested under folder + workbook)
        Route::controller(RowController::class)
            ->prefix('/folders/{folder}/workbooks/{workbook}/rows')
            ->name('rows.')
            ->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{row}/edit',  'edit')->name('edit');
                Route::put('/{row}', 'update')->name('update');
                Route::delete('/{row}', 'destroy')->name('destroy');
            });
    });

// PRIORITY 5: Calendar System Routes (Core functionality)
Route::middleware(['auth'])->group(function () {




    // Main Calendar Routes
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/events', [CalendarController::class, 'getEvents'])->name('events');
        Route::get('/filters', [CalendarController::class, 'getFilters'])->name('filters');
        Route::get('/month', [CalendarController::class, 'monthView'])->name('month');
        Route::get('/week/{date?}', [CalendarController::class, 'weekView'])->name('week');
        Route::get('/day/{date?}', [CalendarController::class, 'dayView'])->name('day'); // ➕ ADD THIS
        Route::get('/list', [CalendarController::class, 'listView'])->name('list'); // ➕ ADD THIS
        Route::get('/daily/{date?}', [CalendarController::class, 'dailyOverview'])->name('daily');
        Route::get('/daily/{date}/events', [CalendarController::class, 'getDailyEvents'])->name('daily.events');

        // ➕ ADD THESE NEW ROUTES
        Route::get('/create', [CalendarController::class, 'create'])->name('create');
        Route::get('/reminders', [CalendarController::class, 'reminders'])->name('reminders');
        Route::get('/export', [CalendarController::class, 'export'])->name('export');
        Route::get('/settings', [CalendarController::class, 'settings'])->name('settings');


        // Calendar Event CRUD (Admin only)
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            Route::post('/events', [CalendarController::class, 'store'])->name('events.store');
            Route::put('/events/{event}', [CalendarController::class, 'update'])->name('events.update');
            Route::delete('/events/{event}', [CalendarController::class, 'destroy'])->name('events.destroy');
        });
    });

    Route::get('/admin/calendar/events', [CalendarController::class, 'getEvents'])->name('admin.calendar.events');

    // ➕ ADD NOTIFICATION ROUTES FOR REAL-TIME SYSTEM
    Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
        Route::get('/current', [NotificationForCalenderController::class, 'getCurrent'])->name('current');
        Route::post('/mark-seen', [NotificationForCalenderController::class, 'markSeen'])->name('mark-seen');
        Route::delete('/clear', [NotificationForCalenderController::class, 'clear'])->name('clear');
    });
});

// PRIORITY 12: Calendar Extended Features (UPDATE THIS SECTION)
Route::middleware(['auth'])->group(function () {

    Route::get('/check-reminders', [ReminderController::class, 'checkPendingReminders']);
    Route::post('/reminders/{id}/dismiss', [ReminderController::class, 'dismissReminder']);


    // Reminders System - COMPLETE ROUTES
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->name('index');
        Route::get('/create', [ReminderController::class, 'create'])->name('create');
        Route::post('/', [ReminderController::class, 'store'])->name('store');
        Route::get('/{reminder}', [ReminderController::class, 'show'])->name('show');
        Route::get('/{reminder}/edit', [ReminderController::class, 'edit'])->name('edit');
        Route::put('/{reminder}', [ReminderController::class, 'update'])->name('update');
        Route::delete('/{reminder}', [ReminderController::class, 'destroy'])->name('destroy');

        // AJAX Actions for the dashboard
        Route::post('/{reminder}/dismiss', [ReminderController::class, 'dismiss'])->name('dismiss');
        Route::post('/{reminder}/snooze', [ReminderController::class, 'snooze'])->name('snooze');
        Route::post('/{reminder}/mark-read', [ReminderController::class, 'markAsRead'])->name('mark-read');

        // API endpoints
        Route::get('/api/due', [ReminderController::class, 'getDueReminders'])->name('due');
        Route::post('/bulk-action', [ReminderController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [ReminderController::class, 'export'])->name('export');

            // Personal reminder notifications


        // Statistics endpoint for AJAX
        Route::get('/api/statistics', [ReminderController::class, 'getStatistics'])->name('statistics');
    });

    // Admin Activity Logs (KEEP YOUR EXISTING ROUTES - just showing for reference)
    Route::prefix('admin-activity')->name('admin-activity.')->group(function () {
        Route::get('/', [AdminActivityController::class, 'index'])->name('index');
        Route::get('/{activity}', [AdminActivityController::class, 'show'])->name('show');
        Route::get('/daily/{date}', [AdminActivityController::class, 'getDailyActivity'])->name('daily');
        Route::get('/api/stats', [AdminActivityController::class, 'getStats'])->name('stats');
        Route::post('/filter', [AdminActivityController::class, 'filter'])->name('filter');
    });

    // Expiration Tracking (KEEP YOUR EXISTING ROUTES)
    Route::prefix('expiration')->name('expiration.')->group(function () {
        Route::get('/', [ExpirationController::class, 'index'])->name('index');
        Route::post('/', [ExpirationController::class, 'store'])->name('store');
        Route::get('/{expiration}', [ExpirationController::class, 'show'])->name('show');
        Route::put('/{expiration}', [ExpirationController::class, 'update'])->name('update');
        Route::post('/{expiration}/renew', [ExpirationController::class, 'renew'])->name('renew');
        Route::delete('/{expiration}', [ExpirationController::class, 'destroy'])->name('destroy');
        Route::get('/api/expiring-items', [ExpirationController::class, 'getExpiringItems'])->name('expiring-items');
        Route::post('/update-warning-settings', [ExpirationController::class, 'updateWarningSettings'])->name('update-warning-settings');
    });

    // Clock Events Management (KEEP YOUR EXISTING ROUTES)
    Route::prefix('clock-events')->name('clock-events.')->group(function () {
        Route::get('/', [ClockEventController::class, 'index'])->name('index');
        Route::post('/', [ClockEventController::class, 'store'])->name('store');
        Route::get('/{clockEvent}', [ClockEventController::class, 'show'])->name('show');
        Route::put('/{clockEvent}', [ClockEventController::class, 'update'])->name('update');
        Route::delete('/{clockEvent}', [ClockEventController::class, 'destroy'])->name('destroy');
        Route::get('/api/events', [ClockEventController::class, 'getEvents'])->name('api.events');
        Route::get('/api/work-hours-summary', [ClockEventController::class, 'getWorkHoursSummary'])->name('work-hours-summary');
    });

    // Maintenance Calendar (KEEP YOUR EXISTING ROUTES)
    Route::prefix('maintenance-calendar')->name('maintenance-calendar.')->group(function () {
        Route::get('/', [MaintenanceCalendarController::class, 'index'])->name('index');
        Route::get('/events', [MaintenanceCalendarController::class, 'getMaintenanceEvents'])->name('events');
        Route::get('/api/statistics', [MaintenanceCalendarController::class, 'getStatistics'])->name('statistics');

        // Admin only routes for scheduling
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            Route::post('/schedule', [MaintenanceCalendarController::class, 'scheduleMaintenance'])->name('schedule');
            Route::put('/reschedule/{event}', [MaintenanceCalendarController::class, 'reschedule'])->name('reschedule');
        });
    });

    // Task Calendar (KEEP YOUR EXISTING ROUTES)
    Route::prefix('task-calendar')->name('task-calendar.')->group(function () {
        Route::get('/', [TaskCalendarController::class, 'index'])->name('index');
        Route::get('/events', [TaskCalendarController::class, 'getTaskEvents'])->name('events');
        Route::get('/workload/{user}', [TaskCalendarController::class, 'getUserWorkload'])->name('workload');

        // Admin only routes for task assignment
        Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
            Route::post('/assign', [TaskCalendarController::class, 'assignTask'])->name('assign');
            Route::put('/update-schedule/{event}', [TaskCalendarController::class, 'updateTaskSchedule'])->name('update-schedule');
        });
    });
});

