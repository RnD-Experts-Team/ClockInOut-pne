
<?php
use App\Http\Controllers\Api\CalendarController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    // Main Calendar Routes
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/events', [CalendarController::class,  'getEvents'])->name('events');
        Route::get('/filters', [CalendarController::class, 'getFilters'])->name('filters');
        Route::get('/month', [CalendarController::class, 'monthView'])->name('month');
        Route::get('/week/{date?}', [CalendarController::class, 'weekView'])->name('week');
        Route::get('/daily/{date?}', [CalendarController::class, 'dailyOverview'])->name('daily');
        Route::get('/daily-events/{date}', [CalendarController::class, 'getDailyEvents'])->name('daily-events');

       
    });
});