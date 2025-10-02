<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import the DB facade
use App\Models\Product;
use App\Models\Transaction;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function create()
    {
        return view('sales.create');
    }

    public function history()
    {
        return view('sales.history');
    }

    /**
     * Get the sales data for Yajra DataTables.
     */
    public function getData(Request $request)
    {
        $transactions = Transaction::with('product')->select('transactions.*');
        
        return DataTables::of($transactions)
            ->addColumn('product_name', function (Transaction $transaction) {
                return $transaction->product ? $transaction->product->name : 'N/A';
            })
            ->addColumn('price_at_sale', function (Transaction $transaction) {
                return number_format($transaction->price, 2);
            })
            ->addColumn('subtotal', function (Transaction $transaction) {
                return number_format($transaction->quantity * $transaction->price, 2);
            })
            ->addColumn('created_at_formatted', function (Transaction $transaction) {
                return $transaction->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['product_name', 'created_at_formatted'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // First, validate the sale items
        $validated = $request->validate([
            'sale_items' => 'required|array',
            'sale_items.*.id' => 'required|exists:products,id',
            'sale_items.*.quantity' => 'required|integer|min:1',
            'payment_amount' => 'required|numeric', // Remove the invalid 'min' rule
        ]);

        $totalAmount = 0;

        // Calculate the total amount of the sale
        foreach ($validated['sale_items'] as $item) {
            $product = Product::find($item['id']);
            $totalAmount += $product->price * $item['quantity'];
        }

        // Check if the payment amount is sufficient
        if ($validated['payment_amount'] < $totalAmount) {
            return redirect()->back()->withErrors(['payment_amount' => 'The payment amount is less than the total sale amount.'])->withInput();
        }

        DB::transaction(function () use ($validated, $totalAmount) {
            foreach ($validated['sale_items'] as $item) {
                $product = Product::find($item['id']);

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Not enough stock for product {$product->name}");
                }

                Transaction::create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->quantity -= $item['quantity'];
                $product->save();
            }
        });

        return redirect()->route('sales.create')->with('success', 'Sale recorded successfully!');
    }

    public function topSelling(string $period = 'weekly')
    {
        $startDate = match ($period) {
            'monthly' => Carbon::now()->startOfMonth(),
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfWeek(),
        };

        $endDate = Carbon::now();

        $topSellingProducts = Transaction::select('product_id', DB::raw('SUM(quantity) as total_quantity_sold'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity_sold')
            ->with('product')
            ->limit(10) // Limit to the top 10 products
            ->get();
        
        return view('reports.top-selling', compact('topSellingProducts', 'period'));
    }
}
