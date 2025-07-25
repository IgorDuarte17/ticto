<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TimeRecordController;
use App\Http\Controllers\Api\ViaCepController;
use App\Http\Controllers\Api\CacheController;

// Health check endpoints
Route::get('/health', [HealthController::class, 'check']);
Route::get('/phpinfo', [HealthController::class, 'phpinfo']);

Route::get('/via-cep/{cep}', [ViaCepController::class, 'getAddress'])
    ->where('cep', '[0-9]{8}');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {

    // Employee management
    Route::prefix('employees')->middleware('role:admin')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    });

    // CEP search
    Route::post('/search-cep', [EmployeeController::class, 'searchCep']);

    // Time record management
    Route::prefix('time-records')->group(function () {
        Route::post('/', [TimeRecordController::class, 'store']);
        Route::get('/my-records', [TimeRecordController::class, 'myRecords']);
        Route::get('/today', [TimeRecordController::class, 'todayRecords']);
        Route::get('/can-record', [TimeRecordController::class, 'canRecord']);
        
        // Admin routes
        Route::get('/', [TimeRecordController::class, 'index'])->middleware('role:admin');
    });
    
    // Cache management (admin only)
    Route::prefix('cache')->middleware('role:admin')->group(function () {
        Route::get('/stats', [CacheController::class, 'stats']);
        Route::delete('/clear', [CacheController::class, 'clear']);
    });
});
