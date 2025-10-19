<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import the DB facade
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;
use App\Models\TransactionDetail;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('transaction.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    

    public function getData(Request $request)
    {
    $transactions = Transaction::with('details.product')->select('transactions.*');

    // Filter by date
    if ($request->from_date) {
        $transactions->whereDate('created_at', '>=', $request->from_date);
    }
    if ($request->to_date) {
        $transactions->whereDate('created_at', '<=', $request->to_date);
    }

    return DataTables::of($transactions)
        ->addColumn('items', function ($t) {
            return $t->details->map(function ($d) {
                return $d->product->name . ' x ' . $d->quantity;
            })->implode(', ');
        })
        ->addColumn('total_amount', fn($t) => $t->total_amount)
        ->addColumn('payment_amount', fn($t) => $t->payment_amount)
        ->addColumn('created_at_formatted', fn($t) => $t->created_at->format('Y-m-d H:i:s'))
        ->addColumn('actions', function ($t) {
            return '<button class="btn btn-info btn-sm view-transaction" data-id="' . $t->id . '">View</button>';
        })
        ->rawColumns(['actions']) // allow HTML in actions
        ->make(true);
    }


    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        // Load details with product info
        $transaction->load('details'); // eager load the details
        return response()->json($transaction);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
