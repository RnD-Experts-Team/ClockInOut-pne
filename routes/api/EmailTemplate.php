<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ModulesInvoice\EmailTemplateController;
use App\Http\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Invoice Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('Invoice')->name('invoice.')->group(function () {
    
    // Email Template Management (Admin Side)
    Route::middleware([RoleMiddleware::class . ':admin'])->prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::post('/', [EmailTemplateController::class, 'store'])->name('store');
        Route::get('/{id}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
        Route::put('/{id}', [EmailTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [EmailTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-default', [EmailTemplateController::class, 'setDefault'])->name('set-default');
    });
    
   
});
