<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use Illuminate\Support\Str;

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

        // 1. Current Month Totals (Keep as is)
        $currentSummary = Transaction::whereBetween('created_at', [$currentStart, $currentEnd])
            ->selectRaw('COALESCE(SUM(total_amount), 0) AS total_revenue, COUNT(*) AS total_sales')
            ->first();

        $totalQuantity = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$currentStart, $currentEnd])
            ->sum('transaction_details.quantity');

        // 2. Current Month Daily Revenue (Keep as is)
        $currentRevenue = Transaction::whereBetween('created_at', [$currentStart, $currentEnd])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');

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

        // 3. Previous Month Daily Revenue (Keep as is)
        $prevRevenue = Transaction::query()
            ->where('created_at', '>=', $prevStart)->where('created_at', '<', $currentStart)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');

        $prevDays = [];
        $day = $prevStart->copy();
        for ($i = 0; $i < $dataPoints; $i++) {
            $date = $day->toDateString();
            $prevDays[] = (float) ($prevRevenue[$date] ?? 0);
            $day->addDay();
        }

        // ✅ 4. NEW: Yearly Revenue Comparison (Jan-Dec)
        $thisYear = $now->year;
        $lastYear = $now->subYear()->year;

        // Helper to get monthly sums for a specific year
        $getMonthlyData = function($year) {
            $data = Transaction::whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
                ->groupBy('month')->pluck('total', 'month');
            
            $result = [];
            for ($m = 1; $m <= 12; $m++) {
                $result[] = (float) ($data[$m] ?? 0);
            }
            return $result;
        };

        $thisYearData = $getMonthlyData($thisYear);
        $lastYearData = $getMonthlyData($lastYear);

        // 5. Top Products (Keep as is)
        $topProducts = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$currentStart, $currentEnd])
            ->selectRaw('products.name AS product_name, COALESCE(SUM(transaction_details.quantity), 0) as total_quantity')
            ->groupBy('products.name')->orderByDesc('total_quantity')->limit(10)->get()
            ->map(function ($item) {
                $item->product_name = Str::limit($item->product_name, 12); 
                return $item;
            });

        // 6. Sales by Category (Keep as is)
        $rawCategorySales = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$currentStart, $currentEnd])
            ->selectRaw('categories.name as category_name, COALESCE(SUM(transaction_details.quantity * transaction_details.price_at_sale), 0) as total_revenue')
            ->groupBy('categories.name')->orderByDesc('total_revenue')->get();
        
        $topLimit = 5; 
        if ($rawCategorySales->count() > $topLimit) {
            $salesByCategory = $rawCategorySales->take($topLimit);
            $othersRevenue = $rawCategorySales->slice($topLimit)->sum('total_revenue');
            if ($othersRevenue > 0) {
                $salesByCategory->push((object)[ 'category_name' => 'Others', 'total_revenue' => $othersRevenue ]);
            }
        } else {
            $salesByCategory = $rawCategorySales;
        }

        // 7. Seasonal Recommendations (Keep as is)
        $currentMonthRecs = $this->getTopProductsByMonth($now->month);
        $nextMonthRecs = $this->getTopProductsByMonth($now->copy()->addMonth()->month);

        // DEMO DATA (Keep as is)
        if ($currentMonthRecs->isEmpty()) {
            $currentMonthRecs = collect([
                (object)['product_name' => 'Kopiko Brown', 'avg_sales' => 120, 'target_sales' => 150, 'total_sold_last_year' => 150],
                (object)['product_name' => 'Silver Swan', 'avg_sales' => 100, 'target_sales' => 120, 'total_sold_last_year' => 120],
                (object)['product_name' => 'Magic Flakes', 'avg_sales' => 80, 'target_sales' => 95, 'total_sold_last_year' => 95],
                (object)['product_name' => 'Bear Brand', 'avg_sales' => 60, 'target_sales' => 80, 'total_sold_last_year' => 80],
                (object)['product_name' => 'Lucky Me!', 'avg_sales' => 50, 'target_sales' => 65, 'total_sold_last_year' => 65],
            ]);
        }
        if ($nextMonthRecs->isEmpty()) {
            $nextMonthRecs = collect([
                (object)['product_name' => 'Coke 1.5L', 'avg_sales' => 150, 'target_sales' => 210, 'total_sold_last_year' => 210],
                (object)['product_name' => 'Nature Spring', 'avg_sales' => 130, 'target_sales' => 180, 'total_sold_last_year' => 180],
                (object)['product_name' => 'San Mig Light', 'avg_sales' => 100, 'target_sales' => 145, 'total_sold_last_year' => 145],
                (object)['product_name' => 'Red Horse', 'avg_sales' => 90, 'target_sales' => 130, 'total_sold_last_year' => 130],
                (object)['product_name' => 'Ding Dong', 'avg_sales' => 80, 'target_sales' => 110, 'total_sold_last_year' => 110],
            ]);
        }

        // ✅ Final Return
        return response()->json([
            'currentMonthRevenue' => $currentDays,
            'prevMonthRevenue'    => $prevDays,
            'yearlyData' => [ // 👈 New Data Group
                'current' => $thisYearData,
                'previous' => $lastYearData,
                'currentYearLabel' => $thisYear,
                'prevYearLabel' => $lastYear
            ],
            'totalRevenue'        => (float) $currentSummary->total_revenue,
            'totalSales'          => (int) $currentSummary->total_sales,
            'totalQuantity'       => (int) $totalQuantity,
            'top_products'        => $topProducts,
            'sales_by_category'   => $salesByCategory,
            'recs' => [
                'isSecondHalf'     => $now->day > 15,
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

    /**
     * UPGRADED: Helper to get top products with Trend Analysis (Average vs. Spike)
     */
    private function getTopProductsByMonth($month)
    {
        $currentYear = Carbon::now()->year;

        // 1. First, find the Top 5 Best Sellers for this specific month (historically)
        // We only want to analyze the products that are relevant to this season.
        $topProductIds = TransactionDetail::whereMonth('created_at', $month)
            ->whereYear('created_at', '<', $currentYear) // Use historical data
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(7)
            ->pluck('product_id');

        // 2. Now, for these 5 products, calculate their "Normal Average" vs "Seasonal Performance"
        // We map through the IDs to perform specific calculations for each product.
        return Product::whereIn('id', $topProductIds)->get()->map(function($product) use ($month, $currentYear) {
            
            // A. Calculate Seasonal Sales (The "Spike")
            // How much does this product usually sell in THIS month?
            // We average the sales for this specific month across past years to get a "Seasonal Benchmark"
            $seasonalSalesTotal = $product->transactionDetails()
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', '<', $currentYear)
                ->sum('quantity');
            
            // Count how many years this product has been active in this month to get an average
            $yearsActiveInMonth = $product->transactionDetails()
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', '<', $currentYear)
                ->selectRaw('count(distinct year(created_at)) as years')
                ->value('years');

            $seasonalAverage = $yearsActiveInMonth > 0 ? $seasonalSalesTotal / $yearsActiveInMonth : 0;

            // B. Calculate Normal Monthly Average (The "Baseline")
            // How much does this product sell in a NORMAL month (across the whole year)?
            $totalLifetimeSales = $product->transactionDetails()
                 ->whereYear('created_at', '<', $currentYear)
                 ->sum('quantity');
                 
            $totalMonthsActive = $product->transactionDetails()
                ->whereYear('created_at', '<', $currentYear)
                ->selectRaw('count(distinct date_format(created_at, "%Y-%m")) as months')
                ->value('months');

            $normalAverage = $totalMonthsActive > 0 ? $totalLifetimeSales / $totalMonthsActive : 0;

            // C. Return the Data Structure needed for the Combo Chart
            return [
                // ✅ FIX: Truncate name to 15 chars to prevent chart overflow
                'product_name' => Str::limit($product->name, 12),
                'category_id'  => $product->category_id,
                'avg_sales'    => round($normalAverage, 1),
                'target_sales' => round($seasonalAverage, 1),
                'total_sold_last_year' => round($seasonalAverage, 1) 
            ];
        });
    }

    /**
     * AI Analysis Endpoint
     */
    public function askAi(Request $request)
    {
        // 1. Gather Context Data
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $topProducts = TransactionDetail::join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transaction_details.created_at', [$start, $end])
            ->select('products.name', DB::raw('SUM(transaction_details.quantity) as qty'))
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Top Categories by Volume
        $topCategories = TransactionDetail::join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transaction_details.created_at', [$start, $end])
            ->select('categories.name', DB::raw('SUM(transaction_details.quantity) as qty'))
            ->groupBy('categories.name')
            ->orderByDesc('qty')
            ->limit(3)
            ->get();


        $totalRevenue = Transaction::whereBetween('created_at', [$start, $end])->sum('total_amount');

        // 2. Determine Language and Persona
        $lang = $request->input('lang', 'en'); 

        if ($lang === 'tl') {
            // 🇵🇭 TAGALOG CONTEXT & PROMPT
            $dataContext = "Narito ang datos ng benta para sa " . $now->format('F Y') . ":\n";
            $dataContext .= "- Kabuuang Kita (Revenue): ₱" . number_format($totalRevenue, 2) . "\n";
            $dataContext .= "- Pinakamabentang Produkto: " . $topProducts->map(fn($p) => "{$p->name} ({$p->category_name}, {$p->qty} piraso)")->implode(', ') . ".\n";
            $dataContext .= "- Nangungunang Kategorya: " . $topCategories->map(fn($c) => "{$c->name} ({$c->qty} piraso)")->implode(', ') . ".\n";

            $systemPrompt = "Ikaw ay isang ekspertong business consultant para sa mga Sari-Sari store sa Pilipinas. ";
            $systemPrompt .= "Batay sa datos sa itaas, magbigay ng 3 maikli, matalino, at madaling gawing payo (strategy tips) para tumaas ang kita sa susunod na buwan. ";
            $systemPrompt .= "Isama sa iyong payo ang tungkol sa mga kategorya ng produkto. Halimbawa: 'Kung mabenta ang isang kategorya (tulad ng {$topCategories->first()->name}), magdagdag pa ng ibang brands o variants nito.' ";
            $systemPrompt .= "Mahalaga: Ang iyong sagot ay dapat nasa wikang **TAGALOG** o **TAGLISH** lamang. Huwag mag-English ng buo. ";
            $systemPrompt .= "Maging magalang at direkta sa punto. Huwag gumamit ng markdown formatting tulad ng bold (**) o listahan (-). Gumamit ng simpleng numbering (1., 2., 3.) at line breaks.\n\n";
            
            $finalPrompt = $dataContext . "\n" . $systemPrompt . "\nAno ang maipapayo mo?";
        } else {
            // 🇺🇸 ENGLISH CONTEXT & PROMPT
            $dataContext = "Here is the sales data for " . $now->format('F Y') . ":\n";
            $dataContext .= "- Total Revenue: ₱" . number_format($totalRevenue, 2) . "\n";
            $dataContext .= "- Top Selling Products: " . $topProducts->map(fn($p) => "{$p->name} ({$p->category_name}, {$p->qty} sold)")->implode(', ') . ".\n";
            $dataContext .= "- Top Categories: " . $topCategories->map(fn($c) => "{$c->name} ({$c->qty} sold)")->implode(', ') . ".\n";

            $systemPrompt = "Act as a business consultant for a small retail store (Sari-Sari store) in the Philippines. ";
            $systemPrompt .= "Give me 3 specific, short, and actionable tips to increase profit next month based on this data. ";
            $systemPrompt .= "Include advice on product categories. For example, if a category (like {$topCategories->first()->name}) has high volume, suggest increasing variety or adding complementary items in that category. ";
            $systemPrompt .= "Keep the tone professional but encouraging. Do not use markdown formatting (like ** or -). Use simple numbering (1., 2., 3.) and line breaks.\n\n";

            $finalPrompt = $dataContext . "\n" . $systemPrompt;
        }

        try {
            // 3. Call Local Ollama Instance
            $response = Http::timeout(500)->post('http://127.0.0.1:11434/api/generate', [
                'model' => 'mistral', 
                'prompt' => $finalPrompt,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $aiText = $response->json()['response'];
                return response()->json(['success' => true, 'message' => $aiText]);
            } else {
                return response()->json(['success' => false, 'message' => 'Ollama API Error: ' . $response->status()]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Could not connect to AI. Ensure Ollama is running. (Error: ' . $e->getMessage() . ')'
            ]);
        }
    }
}