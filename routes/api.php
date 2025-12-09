<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ 1. Public Routes (No Login Required)
// This allows the mobile app to get a token
Route::post('/login', [AuthController::class, 'login']);

// 🔒 2. Protected Routes (Login Required)
// All routes inside here require a valid Bearer Token
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Actions
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']); // Your existing user route logic is moved here
    
    // Dashboard Data for Mobile
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::post('/dashboard/ask-ai', [DashboardController::class, 'askAi']);
});