<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ModulesInvoice\EmailRecipientController;
use App\Http\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Invoice Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('Invoice')->name('invoice.')->group(function () {
    
    // Email Recipient Management (Admin Side)
    Route::middleware([RoleMiddleware::class . ':admin'])->prefix('email-recipients')->name('email-recipients.')->group(function () {
        Route::get('/', [EmailRecipientController::class, 'index'])->name('index');
         Route::post('/', [EmailRecipientController::class, 'store'])->name('store');
         Route::put('/{id}', [EmailRecipientController::class, 'update'])->name('update');
        Route::delete('/{id}', [EmailRecipientController::class, 'destroy'])->name('destroy');
    });
    
    
   
});
