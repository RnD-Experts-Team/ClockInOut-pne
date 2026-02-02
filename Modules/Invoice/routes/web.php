<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoice\Http\Controllers\InvoiceController;
use Modules\Invoice\Http\Controllers\InvoiceCardController;

/*
|--------------------------------------------------------------------------
| Invoice Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('Invoice')->name('invoice.')->group(function () {
    
    // Invoice Cards (User Side)
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
    
    // Invoice Management (Admin Side)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/generate-from-card', [InvoiceController::class, 'generateFromCard'])->name('generate-from-card');
        Route::post('/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('download');
        Route::post('/{id}/save-image', [InvoiceController::class, 'saveImage'])->name('save-image');
    });
    
    // Email Template Management (Admin Side)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'index'])->name('index');
        Route::get('/create', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'create'])->name('create');
        Route::post('/', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'edit'])->name('edit');
        Route::get('/{id}/preview', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'preview'])->name('preview');
        Route::put('/{id}', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-default', [\Modules\Invoice\Http\Controllers\EmailTemplateController::class, 'setDefault'])->name('set-default');
    });
    
    // Email Recipient Management (Admin Side)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('email-recipients')->name('email-recipients.')->group(function () {
        Route::get('/', [\Modules\Invoice\Http\Controllers\EmailRecipientController::class, 'index'])->name('index');
        Route::get('/create', [\Modules\Invoice\Http\Controllers\EmailRecipientController::class, 'create'])->name('create');
        Route::post('/', [\Modules\Invoice\Http\Controllers\EmailRecipientController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\Modules\Invoice\Http\Controllers\EmailRecipientController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\Modules\Invoice\Http\Controllers\EmailRecipientController::class, 'update'])->name('update');
        Route::delete('/{id}', [\Modules\Invoice\Http\Controllers\EmailRecipientController::class, 'destroy'])->name('destroy');
    });
    
    // Invoice Cards Management (Admin Side)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('cards')->name('cards.')->group(function () {
        Route::get('/', [InvoiceCardController::class, 'index'])->name('index');
    });
});
