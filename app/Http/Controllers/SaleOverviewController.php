<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

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

        // 1. Build the Query (Remove ->get() for now so we can calculate sums efficiently)
        $query = TransactionDetail::select(
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(transaction_details.quantity) as total_quantity'),
                DB::raw('SUM(transaction_details.quantity * transaction_details.price_at_sale) as total_revenue')
            )
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            
            ->when($month && $month !== 'all', function ($q) use ($month) {
                $q->whereMonth('transactions.created_at', $month);
            })
            ->when($year, function ($q) use ($year) {
                $q->whereYear('transactions.created_at', $year);
            })
            ->groupBy('products.id', 'products.name');

        // 2. Fetch Data (Execute Query)
        // We get the collection here so we can sum it up easily in PHP
        $salesData = $query->get();

        // 3. ✅ Calculate Grand Totals from the full collection
        $grandTotalQty = $salesData->sum('total_quantity');
        $grandTotalRevenue = $salesData->sum('total_revenue');

        return DataTables::of($salesData)
            // 4. ✅ Pass the totals as extra data in the JSON response
            ->with([
                'grand_total_quantity' => $grandTotalQty,
                'grand_total_revenue' => $grandTotalRevenue,
            ])
            ->addColumn('total_quantity', fn($t) => $t->total_quantity)
            ->addColumn('total_revenue', fn($t) => $t->total_revenue)
            ->make(true);
    }

    /**
     * Send Daily Report to n8n Automation
     */
    public function sendReport(Request $request)
    {
        $type = $request->input('type', 'daily'); // Default to daily if triggered manually
        $now = Carbon::today(); // Use today() to capture 12:00 AM onwards

        // 1. Determine Date Range & Title
        if ($type === 'weekly') {
            // Weekly Logic
            $startDate = $now->copy()->startOfWeek();
            $endDate = $now->copy()->endOfWeek();
            $title = "Weekly Sales Report";
            $dateLabel = $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');

        } elseif ($type === 'monthly') {
            // Monthly Logic
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $title = "Monthly Sales Report";
            $dateLabel = $now->format('F Y');

        } else {
            // Default: Daily Logic
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $title = "Daily Sales Report";
            $dateLabel = $now->format('F j, Y');
        }

        // 2. Calculate Metrics (Using Transaction Model)
        $totalRevenue = Transaction::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        $totalTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->count();

        // 3. Get Top 3 Products (Using TransactionDetail)
        // We JOIN with the parent 'transactions' table to filter by date safely
        $topProducts = TransactionDetail::select('transaction_details.product_name', DB::raw('sum(transaction_details.quantity) as qty'))
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id') // Link Child to Parent
            ->whereBetween('transactions.created_at', [$startDate, $endDate]) // Filter using Parent's date
            ->groupBy('transaction_details.product_name')
            ->orderByDesc('qty')
            ->limit(3)
            ->get();

            // ✅ NEW: Format the list into HTML for the email
            // This turns the array into: "<ul><li>Product A: 10</li><li>Product B: 5</li></ul>"
            if ($topProducts->isEmpty()) {
                $formattedTopProducts = "<i>No sales recorded for this period.</i>";
            } else {
                $formattedTopProducts = "<ul>";
                foreach ($topProducts as $item) {
                    $formattedTopProducts .= "<li><strong>{$item->product_name}</strong>: {$item->qty} units</li>";
                }
                $formattedTopProducts .= "</ul>";
            }

        // 4. Prepare Payload
        $payload = [
            'title' => $title,
            'date' => $dateLabel,
            'total_revenue' => '₱' . number_format($totalRevenue, 2),
            'total_transactions' => $totalTransactions,
            'top_products_html' => $formattedTopProducts,
            'status' => "Auto-generated ($type)"
        ];

        try {
            // 4. Send to n8n Webhook
            // (Use your current working URL)
            $n8nWebhookUrl = 'http://localhost:5678/webhook-test/daily-report'; 

            $response = Http::timeout(60)->post($n8nWebhookUrl, $payload);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => "$title sent successfully!"]);
            } else {
                return response()->json(['success' => false, 'message' => 'n8n Error: ' . $response->status()]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection Failed: ' . $e->getMessage()]);
        }
    }

}
