  
  <?php
use App\Http\Controllers\Api\Admin\TaskCalendarController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
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
 
  