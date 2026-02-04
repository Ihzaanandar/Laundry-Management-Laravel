<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::today();

        $data = [
            'date' => $date->format('Y-m-d'),
            'orders' => Order::whereDate('createdAt', $date)->with('customer')->get(),
            'totalRevenue' => Order::whereDate('createdAt', $date)->where('paymentStatus', 'SUDAH_BAYAR')->sum('totalAmount'),
            'totalOrders' => Order::whereDate('createdAt', $date)->count()
        ];
        return $this->sendResponse($data, 'Daily report retrieved');
    }

    public function monthly(Request $request)
    {
        $month = $request->query('month') ?? Carbon::now()->month;
        $year = $request->query('year') ?? Carbon::now()->year;

        $data = [
            'month' => $month,
            'year' => $year,
            'totalRevenue' => Order::whereYear('createdAt', $year)->whereMonth('createdAt', $month)->where('paymentStatus', 'SUDAH_BAYAR')->sum('totalAmount'),
            'totalOrders' => Order::whereYear('createdAt', $year)->whereMonth('createdAt', $month)->count(),
            'dailyStats' => Order::whereYear('createdAt', $year)
                ->whereMonth('createdAt', $month)
                ->selectRaw('DATE(createdAt) as date, COUNT(*) as count, SUM(totalAmount) as revenue')
                ->groupBy('date')
                ->get()
        ];
        return $this->sendResponse($data, 'Monthly report retrieved');
    }

    public function yearly(Request $request)
    {
        $year = $request->query('year') ?? Carbon::now()->year;

        $data = Order::whereYear('createdAt', $year)
            ->selectRaw('MONTH(createdAt) as month, COUNT(*) as count, SUM(totalAmount) as revenue')
            ->groupBy('month')
            ->get();
        return $this->sendResponse($data, 'Yearly report retrieved');
    }
}
