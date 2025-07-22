<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;

// Health check endpoint
Route::get('/health', [HealthController::class, 'check']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
