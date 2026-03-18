<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ModulesInvoice\InvoiceController;
use App\Http\Middleware\RoleMiddleware;

 

Route::middleware(['auth:sanctum'])->prefix('Invoice')->name('invoice.')->group(function () {
      // Invoice Management (Admin Side)
    Route::middleware([RoleMiddleware::class . ':admin'])->prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/generate-from-card', [InvoiceController::class, 'generateFromCard'])->name('generate-from-card');
        Route::post('/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('download');
        Route::post('/{id}/save-image', [InvoiceController::class, 'saveImage'])->name('save-image');
    });
 
});
