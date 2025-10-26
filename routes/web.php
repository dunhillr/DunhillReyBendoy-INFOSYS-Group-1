<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

 // --- Record-Sales Routes ---
    // Displays the form to create a new sale record.
    Route::get('/record-sales/create', [SaleController::class, 'create'])->name('record-sales.create');
    // Handles the submission of the new sale form.
    Route::post('/record-sales/store', [SaleController::class, 'store'])->name('record-sales.store');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
