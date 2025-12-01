<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected function monthRange()
    {
        $now = Carbon::now();
        return [
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth(),
            $now
        ];
    }

    public function index()
    {
        [$start, $end, $now] = $this->monthRange();

        // 1. FETCH CURRENT MONTH SUMMARY (Required for Cards)
        $currentSummary = Transaction::whereBetween('created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_revenue, COUNT(*) as transaction_count')
            ->first();

        $currentUnitsSold = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->sum('transaction_details.quantity');

        // 2. FETCH PREVIOUS MONTH SUMMARY (For Percentage Calculation)
        $prevSummary = $this->getPreviousMonthSummary();

        // 3. CALCULATE PERCENTAGE CHANGES
        $calculatePercentage = function ($current, $previous) {
            if ($previous == 0) {
                return ($current > 0) ? 100.0 : 0.0;
            }
            return (($current - $previous) / $previous) * 100;
        };

        // 4. RECOMMENDATION LOGIC
        // If today is past the 15th, "Next Month" becomes primary.
        $dayOfMonth = $now->day;
        $isSecondHalf = $dayOfMonth > 15;

        $monthA_Index = $now->month; // Current Month
        $monthB_Index = $now->copy()->addMonth()->month; // Next Month

        // Fetch recommendations (Removed $lastYear argument)
        $currentMonthRecs = $this->getTopProductsByMonth($monthA_Index);
        $nextMonthRecs = $this->getTopProductsByMonth($monthB_Index);

        return view('dashboard', [
            // Summary Card Data
            'monthlyRevenue' => (float) $currentSummary->total_revenue,
            'totalSales'     => (int) $currentSummary->transaction_count,
            'productsSold'   => (int) $currentUnitsSold,
            'monthLabel'     => $now->format('F Y'),

            // Percentage Indicators
            'revenueChange'  => $calculatePercentage($currentSummary->total_revenue, $prevSummary['total_revenue']),
            'salesChange'    => $calculatePercentage($currentSummary->transaction_count, $prevSummary['transaction_count']),
            'productsChange' => $calculatePercentage($currentUnitsSold, $prevSummary['units_sold']),

            // Recommendation Data
            'recs' => [
                'isSecondHalf'     => $isSecondHalf,
                'currentMonthName' => $now->format('F'),
                'nextMonthName'    => $now->copy()->addMonth()->format('F'),
                'current'          => $currentMonthRecs,
                'next'             => $nextMonthRecs,
            ]
        ]);
    }

    /**
     * AJAX Endpoint for Charts
     */
    public function summary()
    {
        $now = Carbon::now();
        $currentStart = $now->copy()->startOfMonth();
        $currentEnd   = $now->copy()->endOfMonth();
        $prevStart    = $now->copy()->subMonth()->startOfMonth();
        $prevEnd      = $now->copy()->subMonth()->endOfMonth();

        // 1. Current Month Totals
        $currentSummary = Transaction::whereBetween('created_at', [$currentStart, $currentEnd])
            ->selectRaw('COALESCE(SUM(total_amount), 0) AS total_revenue, COUNT(*) AS total_sales')
            ->first();

        $totalQuantity = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$currentStart, $currentEnd])
            ->sum('transaction_details.quantity');

        // 2. Current Month Daily Revenue
        $currentRevenue = Transaction::whereBetween('created_at', [$currentStart, $currentEnd])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $currentDays = [];
        for ($day = $currentStart->copy(); $day->lte($currentEnd); $day->addDay()) {
            $date = $day->toDateString();
            if ($day->lte($now)) {
                $currentDays[] = (float) ($currentRevenue[$date] ?? 0);
            } else {
                $currentDays[] = null;
            }
        }
        $dataPoints = count($currentDays);

        // 3. Previous Month Daily Revenue
        $prevRevenue = Transaction::query()
            ->where('created_at', '>=', $prevStart)
            ->where('created_at', '<', $currentStart)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $prevDays = [];
        $day = $prevStart->copy();
        for ($i = 0; $i < $dataPoints; $i++) {
            $date = $day->toDateString();
            $prevDays[] = (float) ($prevRevenue[$date] ?? 0);
            $day->addDay();
        }

        // 4. Top Products
        $topProducts = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$currentStart, $currentEnd])
            ->selectRaw('products.name AS product_name, COALESCE(SUM(transaction_details.quantity), 0) as total_quantity')
            ->groupBy('products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // 5. Sales by Category (With "Others" Logic)
        $rawCategorySales = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$currentStart, $currentEnd])
            ->selectRaw('
                categories.name as category_name, 
                COALESCE(SUM(transaction_details.quantity * transaction_details.price_at_sale), 0) as total_revenue
            ')
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get();
        
        $topLimit = 5; 
        
        if ($rawCategorySales->count() > $topLimit) {
            $salesByCategory = $rawCategorySales->take($topLimit);
            $othersRevenue = $rawCategorySales->slice($topLimit)->sum('total_revenue');
        
            if ($othersRevenue > 0) {
                $salesByCategory->push((object)[
                    'category_name' => 'Others',
                    'total_revenue' => $othersRevenue
                ]);
            }
        } else {
            $salesByCategory = $rawCategorySales;
        }

        // --- 🆕 Seasonal Recommendations Data ---
        $dayOfMonth = $now->day;
        $isSecondHalf = $dayOfMonth > 15;
        
        // Use the helper to get data from ALL past years
        $currentMonthRecs = $this->getTopProductsByMonth($now->month);
        $nextMonthRecs = $this->getTopProductsByMonth($now->copy()->addMonth()->month);

        if ($currentMonthRecs->isEmpty()) {
            $currentMonthRecs = collect([
                (object)['product_name' => 'Kopiko Brown (Demo)', 'total_sold_last_year' => 150],
                (object)['product_name' => 'Silver Swan Soy Sauce', 'total_sold_last_year' => 120],
                (object)['product_name' => 'Magic Flakes', 'total_sold_last_year' => 95],
                (object)['product_name' => 'Bear Brand Swak', 'total_sold_last_year' => 80],
                (object)['product_name' => 'Lucky Me! Beef', 'total_sold_last_year' => 65],
            ]);
        }

        if ($nextMonthRecs->isEmpty()) {
            $nextMonthRecs = collect([
                (object)['product_name' => 'Coca Cola 1.5L (Demo)', 'total_sold_last_year' => 210],
                (object)['product_name' => 'Nature Spring Water', 'total_sold_last_year' => 180],
                (object)['product_name' => 'San Mig Light', 'total_sold_last_year' => 145],
                (object)['product_name' => 'Red Horse Stallion', 'total_sold_last_year' => 130],
                (object)['product_name' => 'Ding Dong Mixed Nuts', 'total_sold_last_year' => 110],
            ]);
        }
        // 🚨 END DEMO MODE 🚨

        // ✅ Final Return
        return response()->json([
            'currentMonthRevenue' => $currentDays,
            'prevMonthRevenue'    => $prevDays,
            'totalRevenue'        => (float) $currentSummary->total_revenue,
            'totalSales'          => (int) $currentSummary->total_sales,
            'totalQuantity'       => (int) $totalQuantity,
            'top_products'        => $topProducts,
            'sales_by_category'   => $salesByCategory,
            // 🆕 New Data Key
            'recs' => [
                'isSecondHalf'     => $isSecondHalf,
                'currentMonthName' => $now->format('F'),
                'nextMonthName'    => $now->copy()->addMonth()->format('F'),
                'current'          => $currentMonthRecs,
                'next'             => $nextMonthRecs,
            ]
        ]);
    }

    protected function getPreviousMonthSummary()
    {
        $now = Carbon::now();
        $prevStart = $now->copy()->subMonth()->startOfMonth();
        $prevEnd = $now->copy()->subMonth()->endOfMonth();

        $summary = Transaction::whereBetween('created_at', [$prevStart, $prevEnd])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_revenue, COUNT(*) as transaction_count')
            ->first();

        $unitsSold = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$prevStart, $prevEnd])
            ->sum('transaction_details.quantity');

        return [
            'total_revenue'     => (float) $summary->total_revenue,
            'transaction_count' => (int) $summary->transaction_count,
            'units_sold'        => (int) $unitsSold,
        ];
    }

    // ✅ UPDATED: Removed $year parameter
    private function getTopProductsByMonth($month)
    {
        $currentYear = Carbon::now()->year;

        return TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereMonth('transactions.created_at', $month)
            // Look for records in any year BEFORE the current year
            ->whereYear('transactions.created_at', '<', $currentYear)
            ->selectRaw('
                products.name as product_name,
                products.category_id, 
                COALESCE(SUM(transaction_details.quantity), 0) as total_sold_last_year
            ')
            ->groupBy('products.name', 'products.category_id')
            ->orderByDesc('total_sold_last_year')
            ->limit(5)
            ->get();
    }
}