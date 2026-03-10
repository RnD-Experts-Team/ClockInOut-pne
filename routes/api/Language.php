<?php
use App\Http\Controllers\Api\LanguageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('/language/switch', [LanguageController::class, 'switch']);