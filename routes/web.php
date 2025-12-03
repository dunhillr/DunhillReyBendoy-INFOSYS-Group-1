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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-overview', [DashboardController::class, 'index'])->name('dashboard.overview');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('/dashboard/ask-ai', [DashboardController::class, 'askAi'])->name('dashboard.ai');

    // --- Product Routes ---
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('data', [ProductController::class, 'getData'])->name('data');
        Route::get('search', [ProductController::class, 'search'])->name('search');
        Route::get('category', [ProductController::class, 'category'])->name('products.category');


        Route::post('{id}/restore', [ProductController::class, 'restore'])->name('restore');

        Route::get('archived', [ProductController::class, 'archived'])->name('archived');
        Route::get('archived/data', [ProductController::class, 'getArchivedData'])->name('archived.data');
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
    // ✅ NEW: Automation Route
    Route::post('/sales-overview/send-report', [SaleOverviewController::class, 'sendReport'])->name('reports.send-report');

    // --- Profile ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Resource Route (keep this last) ---
    Route::resource('products', ProductController::class);
});

require __DIR__.'/auth.php';