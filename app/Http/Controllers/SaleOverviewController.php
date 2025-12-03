<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
                // Optimization: Sum the 'subtotal' column directly if available, 
                // otherwise calculation is fine.
                DB::raw('SUM(transaction_details.quantity * transaction_details.price_at_sale) as total_revenue')
            )
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            
            // ✅ FIX: Only apply month filter if it is NOT 'all'
            ->when($month && $month !== 'all', function ($query) use ($month) {
                $query->whereMonth('transactions.created_at', $month);
            })
            
            ->when($year, function ($query) use ($year) {
                $query->whereYear('transactions.created_at', $year);
            })
            ->groupBy('products.id', 'products.name')
            // Add get() so DataTables receives a Collection, not a Builder
            // (DataTables can handle Builder too, but get() ensures group by executes)
            ->get(); 

        return DataTables::of($salesData)
            ->addColumn('total_quantity', fn($t) => $t->total_quantity)
            ->addColumn('total_revenue', fn($t) => $t->total_revenue)
            ->make(true);
    }

    /**
     * Send Daily Report to n8n Automation
     */
    public function sendReport()
    {
        $today = now()->format('Y-m-d');

        // 1. Calculate Today's Metrics
        $totalRevenue = Transaction::whereDate('created_at', $today)->sum('total_amount');
        $totalTransactions = Transaction::whereDate('created_at', $today)->count();

        // 2. Prepare Data Payload
        $payload = [
            'date' => now()->format('F j, Y'),
            'total_revenue' => '₱' . number_format($totalRevenue, 2),
            'total_transactions' => $totalTransactions,
            'status' => 'Generated via SariSmart POS'
        ];

        try {
            // 3. Send to n8n Webhook
            // ✅ UPDATE THIS LINE with your actual n8n Test URL
            $n8nWebhookUrl = 'https://helpless-crab-57.hooks.n8n.cloud/webhook-test/daily-report';

            // ✅ FIX: Increase timeout to 60 seconds
            $response = Http::timeout(60)->post($n8nWebhookUrl, $payload);
            
            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Report sent to automation workflow!']);
            } else {
                return response()->json(['success' => false, 'message' => 'n8n Error: ' . $response->status()]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection Failed: ' . $e->getMessage()]);
        }
    }

}
