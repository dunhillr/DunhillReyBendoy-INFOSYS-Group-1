<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return view('transactions.index'); // Ensure view path is correct
    }

    /**
     * Get Data for DataTables
     */
    public function getData(Request $request)
    {
        $transactions = Transaction::select('transactions.*');

        // Optional date filtering
        if ($request->from_date) {
            $transactions->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $transactions->whereDate('created_at', '<=', $request->to_date);
        }

        return DataTables::of($transactions)
            ->addColumn('total_amount', fn($t) => $t->total_amount)
            ->addColumn('created_at_formatted', fn($t) => $t->created_at->format('Y-m-d H:i:s'))
            ->addColumn('actions', function ($t) {
                // Returns the button HTML for the Actions column
                return '<button class="btn btn-primary btn-sm view-transaction shadow-sm" data-id="' . $t->id . '">
                            <i class="fas fa-eye me-1"></i> View
                        </button>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Display the specified resource (For Modal).
     */
    public function show(Transaction $transaction)
    {
        // Load details AND the associated product info (even if soft deleted/inactive)
        $transaction->load(['details']); 
        
        return response()->json($transaction);
    }
}