<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class SaleOverviewController extends Controller
{
    /**
     * Show the Sales Overview page.
     */
    public function index()
    {
        // ✅ All 12 months (always available)
        $availableMonths = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => date('F', mktime(0, 0, 0, $month, 1))];
        });

        // ✅ Get only years that have transactions
        $availableYears = Transaction::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // ✅ Determine which year to auto-select
        $currentYear = now()->year;
        $selectedYear = in_array($currentYear, $availableYears) ? $currentYear : ($availableYears[0] ?? $currentYear);

        return view('reports.sales-overview', compact('availableMonths', 'availableYears', 'selectedYear'))
            ->with('selectedMonth', now()->month);
    }

    /**
     * Get aggregated sales data for DataTables.
     */
    public function getData(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        $salesData = TransactionDetail::select(
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(transaction_details.quantity) as total_quantity'),
                DB::raw('SUM(transaction_details.quantity * transaction_details.price_at_sale) as total_revenue')
            )
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->when($month, function ($query) use ($month) {
                $query->whereMonth('transactions.created_at', $month);
            })
            ->when($year, function ($query) use ($year) {
                $query->whereYear('transactions.created_at', $year);
            })
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->get();

        return DataTables::of($salesData)
            ->addColumn('total_quantity', fn($t) => $t->total_quantity)
            ->addColumn('total_revenue', fn($t) => $t->total_revenue)
            ->make(true);
    }

}
