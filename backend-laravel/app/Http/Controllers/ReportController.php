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

        $orders = Order::whereDate('createdAt', $date)->with('customer')->get();

        $data = [
            'date' => $date->format('Y-m-d'),
            'orders' => $orders,
            'totalRevenue' => Order::whereDate('createdAt', $date)->where('paymentStatus', 'SUDAH_BAYAR')->sum('totalAmount'),
            'totalOrders' => $orders->count(),
            'paidOrders' => $orders->where('paymentStatus', 'SUDAH_BAYAR')->count(),
            'unpaidOrders' => $orders->where('paymentStatus', 'BELUM_BAYAR')->count(),
        ];
        return $this->sendResponse($data, 'Daily report retrieved');
    }

    public function monthly(Request $request)
    {
        $month = $request->query('month') ?? Carbon::now()->month;
        $year = $request->query('year') ?? Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $orders = Order::whereBetween('createdAt', [$startDate, $endDate])->with('customer')->get();

        // Daily breakdown - PostgreSQL compatible
        $dailyBreakdown = DB::table('orders')
            ->selectRaw('DATE("createdAt") as date, SUM("totalAmount") as revenue')
            ->whereBetween('createdAt', [$startDate, $endDate])
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->groupByRaw('DATE("createdAt")')
            ->orderBy('date')
            ->get()
            ->toArray();

        $data = [
            'month' => (int) $month,
            'year' => (int) $year,
            'orders' => $orders,
            'totalRevenue' => $orders->where('paymentStatus', 'SUDAH_BAYAR')->sum('totalAmount'),
            'totalOrders' => $orders->count(),
            'paidOrders' => $orders->where('paymentStatus', 'SUDAH_BAYAR')->count(),
            'unpaidOrders' => $orders->where('paymentStatus', 'BELUM_BAYAR')->count(),
            'dailyBreakdown' => $dailyBreakdown,
        ];
        return $this->sendResponse($data, 'Monthly report retrieved');
    }

    public function yearly(Request $request)
    {
        $year = $request->query('year') ?? Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $orders = Order::whereBetween('createdAt', [$startDate, $endDate])->with('customer')->get();

        // Monthly breakdown - PostgreSQL compatible
        $monthlyBreakdown = DB::table('orders')
            ->selectRaw('EXTRACT(MONTH FROM "createdAt")::integer as month, SUM("totalAmount") as revenue')
            ->whereBetween('createdAt', [$startDate, $endDate])
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->groupByRaw('EXTRACT(MONTH FROM "createdAt")')
            ->orderBy('month')
            ->get()
            ->toArray();

        $data = [
            'year' => (int) $year,
            'orders' => $orders,
            'totalRevenue' => $orders->where('paymentStatus', 'SUDAH_BAYAR')->sum('totalAmount'),
            'totalOrders' => $orders->count(),
            'paidOrders' => $orders->where('paymentStatus', 'SUDAH_BAYAR')->count(),
            'unpaidOrders' => $orders->where('paymentStatus', 'BELUM_BAYAR')->count(),
            'monthlyBreakdown' => $monthlyBreakdown,
        ];
        return $this->sendResponse($data, 'Yearly report retrieved');
    }
}
