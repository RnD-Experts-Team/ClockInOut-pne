  
  <?php
use App\Http\Controllers\Api\Admin\ClockEventController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
  
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

});
 
  