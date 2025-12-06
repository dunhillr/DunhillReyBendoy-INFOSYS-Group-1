<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // Import the controller we made
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes (No login required)
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Login required via Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard Endpoints for Mobile
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::post('/dashboard/ask-ai', [DashboardController::class, 'askAi']);

    // Get current user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // FUTURE: Add your other routes here (e.g., Products, Sales)
    // Route::get('/products', [ProductController::class, 'index']);
});
