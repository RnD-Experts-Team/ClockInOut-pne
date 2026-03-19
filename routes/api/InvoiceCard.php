<?php

use App\Http\Controllers\Api\Admin\ModulesInvoice\InvoiceCardController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->prefix('Invoice')->name('invoice.')->group(function () {
     Route::prefix('cards')->name('cards.')->group(function () {
        // Route::get('/', [InvoiceCardController::class, 'index'])->name('index'); // Removed - cards now shown in /clocking
        Route::post('/', [InvoiceCardController::class, 'store'])->name('store');
        Route::get('/{id}', [InvoiceCardController::class, 'show'])->name('show');
        Route::post('/{id}/complete', [InvoiceCardController::class, 'complete'])->name('complete');
        Route::post('/{id}/materials', [InvoiceCardController::class, 'addMaterial'])->name('materials.add');
        Route::delete('/materials/{materialId}', [InvoiceCardController::class, 'deleteMaterial'])->name('materials.delete');
        Route::post('/{id}/add-task', [InvoiceCardController::class, 'addTask'])->name('add-task');
        Route::post('/{id}/remove-task', [InvoiceCardController::class, 'removeTask'])->name('remove-task');
        Route::post('/{id}/complete-task', [InvoiceCardController::class, 'completeTask'])->name('complete-task');
        
        // Admin-only sync route
        Route::post('/sync-clocking-records', [InvoiceCardController::class, 'syncAllClockingRecords'])->name('sync-clocking-records');
        
        // Get incomplete cards for current user
        Route::get('/incomplete', [InvoiceCardController::class, 'getIncompleteCards'])->name('incomplete');
    });
 
});
