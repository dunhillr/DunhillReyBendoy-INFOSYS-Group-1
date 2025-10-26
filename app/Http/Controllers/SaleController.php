<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import the DB facade
use App\Models\Product;
use App\Models\Transaction;

use App\Models\TransactionDetail;

class SaleController extends Controller
{
    public function create()
    {
        return view('record-sales.create');
    }

    public function store(Request $request)
{
    // 1️⃣ Validate input
    $validated = $request->validate([
        'sale_items' => 'required|array',
        'sale_items.*.id' => 'required|exists:products,id',
        'sale_items.*.quantity' => 'required|integer|min:1',
        'payment_amount' => 'required|numeric|min:0',
    ]);

    // 2️⃣ Wrap in DB transaction for safety
    DB::transaction(function () use ($validated) {

        // Preload products to reduce queries
        $productIds = collect($validated['sale_items'])->pluck('id')->toArray();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // ✅ $products is a Collection keyed by product id

        // Calculate total amount
        $totalAmount = 0;
        foreach ($validated['sale_items'] as $item) {
            $product = $products[$item['id']]; // ✅ single Product model
            $totalAmount += $product->price * $item['quantity'];
        }

        // Check if payment is sufficient
        if ($validated['payment_amount'] < $totalAmount) {
            throw new \Exception("Payment is less than total sale amount.");
        }

        // 3️⃣ Create Transaction
        $transaction = Transaction::create([
            'total_amount' => $totalAmount,
            'payment_amount' => $validated['payment_amount'],
        ]);

        // 4️⃣ Create TransactionDetails
        foreach ($validated['sale_items'] as $item) {
            $product = $products[$item['id']]; // ✅ single model

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'price_at_sale'  => $product->price,
                'quantity'       => $item['quantity'],
                'subtotal'       => $product->price * $item['quantity'],
            ]);
        }
    });

    return redirect()->route('record-sales.create')
                    ->with('success', 'Sale recorded successfully!');
}





}
