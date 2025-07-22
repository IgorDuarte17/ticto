<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TimeRecordController;

// Health check endpoints
Route::get('/health', [HealthController::class, 'check']);
Route::get('/phpinfo', [HealthController::class, 'phpinfo']);

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Employee management (Admin and Managers only)
    Route::prefix('employees')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    });
    
    // CEP search (available for all authenticated users)
    Route::post('/search-cep', [EmployeeController::class, 'searchCep']);
    
    // Time record management
    Route::prefix('time-records')->group(function () {
        // Employee routes (all authenticated users)
        Route::post('/', [TimeRecordController::class, 'store']); // Record time
        Route::get('/my-records', [TimeRecordController::class, 'myRecords']); // My records
        Route::get('/today', [TimeRecordController::class, 'todayRecords']); // Today's records
        Route::get('/can-record', [TimeRecordController::class, 'canRecord']); // Check if can record
        
        // Admin/Manager routes
        Route::get('/', [TimeRecordController::class, 'index'])->middleware('role:admin,manager'); // List all records with filters
    });
});

// Legacy route for testing
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
