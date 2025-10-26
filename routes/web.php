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


    // --- Transaction (Invoice) Routes ---
    // Grouping transaction-related routes under the 'transactions' prefix and name.
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // Displays a list of transactions.
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        // API endpoint to fetch transaction data (likely for tables or lists).
        Route::get('/data', [TransactionController::class, 'getData'])->name('data');
        // Displays the details of a specific transaction.
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        // Generates/displays an invoice for a specific transaction.
        Route::get('/{transaction}/invoice', [TransactionController::class, 'invoice'])->name('invoice');
        // Deletes a specific transaction.
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
