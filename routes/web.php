<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

// Public route (accessible to guests)
Route::get('/', function () {
    return view('welcome');
});

// Routes that require login
Route::middleware(['auth', 'verified'])->group(function () {

    // --- Dashboard ---
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // --- Product Routes ---
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('data', [ProductController::class, 'getData'])->name('data');
        Route::get('search', [ProductController::class, 'search'])->name('search');
    });

    // --- Sales Routes ---
    Route::get('/sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/sales/history', [SalesController::class, 'history'])->name('sales.history');
    Route::get('/sales/data', [SalesController::class, 'getData'])->name('sales.data');

    // --- Top Selling Product Route ---
    Route::get('/reports/top-selling/{period?}', [SalesController::class, 'topSelling'])->name('reports.topSelling');

    // --- Profile ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Resource Route (last to prevent conflicts) ---
    Route::resource('products', ProductController::class);
});

require __DIR__.'/auth.php';
