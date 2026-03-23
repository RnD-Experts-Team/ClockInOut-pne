<?php
use App\Http\Controllers\Api\Admin\CompanyController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('companies/export', [CompanyController::class, 'export'])->name('companies.export');

    Route::apiResource('companies', CompanyController::class);

});
