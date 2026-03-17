<?php

use App\Http\Controllers\Api\Admin\FolderController;
use App\Http\Controllers\Api\Admin\RowController;
use App\Http\Controllers\Api\Admin\WorkbookController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('workbooks')
    ->name('workbooks.')
    ->middleware(['auth:sanctum', RoleMiddleware::class . ':admin'])
    ->scopeBindings()
    ->group(function () {

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
            Route::put('/folders/{folder}/workbooks/{workbook}', 'update')->name('update');
            Route::delete('/folders/{folder}/workbooks/{workbook}', 'destroy')->name('destroy');
        });

        // Rows (nested under folder + workbook)
        Route::controller(RowController::class)
            ->prefix('/folders/{folder}/workbooks/{workbook}/rows')
            ->name('rows.')
            ->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{row}', 'update')->name('update');
                Route::delete('/{row}', 'destroy')->name('destroy');
            });
    });

