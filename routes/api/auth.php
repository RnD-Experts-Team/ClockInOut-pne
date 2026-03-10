
<?php

// use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected
Route::middleware('auth:sanctum')->group(function () {
    // Route::put('/user/update', [UserController::class, 'updateUser']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
});