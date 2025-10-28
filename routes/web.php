<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleOverviewController; // <--- ADD THIS LINE
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // --- User Profile Routes ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Reports / Sales Overview --- // <--- PLACE YOUR NEW ROUTES HERE
    Route::get('/sales-overview', [SaleOverviewController::class, 'index'])->name('reports.sales-overview');
    Route::get('/sales-overview/data', [SaleOverviewController::class, 'getData'])->name('reports.sales-data');
});

require __DIR__.'/auth.php';
