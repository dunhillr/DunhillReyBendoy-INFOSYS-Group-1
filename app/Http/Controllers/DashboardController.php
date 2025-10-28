<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->type === 'admin') {
            return view('admin.dashboard');
        }
        return view('dashboard');
    }

    public function salesData(Request $request)
    {
        $period = $request->get('period', 'week');

        switch ($period) {
            case 'month':
                $sales = Transaction::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->where('created_at', '>=', now()->subMonth())
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
                break;
            case 'year':
                $sales = Transaction::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->date = date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                    return $item;
                });
                break;
            case 'week':
            default:
                $sales = Transaction::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->where('created_at', '>=', now()->subWeek())
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
                break;
        }

        return response()->json($sales);
    }

    public function topSellingProducts(Request $request)
    {
        $period = $request->get('period', 'week');
        $limit = $request->get('limit', 5);

        $query = TransactionDetail::select('products.name', DB::raw('SUM(transaction_details.quantity) as total_quantity'))
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit);

        switch ($period) {
            case 'month':
                $query->where('transactions.created_at', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('transactions.created_at', '>=', now()->subYear());
                break;
            case 'week':
            default:
                $query->where('transactions.created_at', '>=', now()->subWeek());
                break;
        }

        return response()->json($query->get());
    }
}
