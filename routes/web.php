<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleOverviewController;
use App\Http\Controllers\DashboardController;

// --- Public route (redirect guests to login) ---
Route::get('/', function () {
    return redirect()->route('login');
});

// --- Routes that require authentication ---
Route::middleware(['auth', 'verified'])->group(function () {

     // --- Dashboard ---
    // Displays the main dashboard (differentiates between admin and regular user).
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // API endpoint to fetch sales data for charts.
    Route::get('/dashboard/sales-data', [DashboardController::class, 'salesData'])->name('dashboard.sales-data');
    // API endpoint to fetch top selling products data for charts.
    Route::get('/dashboard/top-selling-products', [DashboardController::class, 'topSellingProducts'])->name('dashboard.top-selling-products');

    // --- Product Routes ---
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('data', [ProductController::class, 'getData'])->name('data');
        Route::get('search', [ProductController::class, 'search'])->name('search');
    });

    // --- Record-Sales Routes ---
    Route::get('/record-sales/create', [SaleController::class, 'create'])->name('record-sales.create');
    Route::post('/record-sales/store', [SaleController::class, 'store'])->name('record-sales.store');


    // --- Transaction (Invoice) Routes ---
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/data', [TransactionController::class, 'getData'])->name('data');

        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');

        Route::get('/{transaction}/invoice', [TransactionController::class, 'invoice'])->name('invoice');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });

    // --- Reports / Sales Overview ---
    Route::get('/sales-overview', [SaleOverviewController::class, 'index'])->name('reports.sales-overview');
    Route::get('/sales-overview/data', [SaleOverviewController::class, 'getData'])->name('reports.sales-data');

    // --- Profile ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Resource Route (keep this last) ---
    Route::resource('products', ProductController::class);
});

require __DIR__.'/auth.php';
