  
  <?php
use App\Http\Controllers\Api\Admin\MaintenanceCalendarController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
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
});
 
      