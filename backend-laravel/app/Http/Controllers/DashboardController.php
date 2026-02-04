<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function owner()
    {
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();
        $startOfYear = now()->startOfYear();

        // Stats
        $todayRevenue = Order::whereDate('createdAt', today())
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->sum('totalAmount');

        $monthlyRevenue = Order::where('createdAt', '>=', $startOfMonth)
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->sum('totalAmount');

        $monthlyOrders = Order::where('createdAt', '>=', $startOfMonth)->count();

        $yearlyRevenue = Order::where('createdAt', '>=', $startOfYear)
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->sum('totalAmount');

        // Charts - Daily (last 7 days)
        $dailyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::whereDate('createdAt', $date)
                ->where('paymentStatus', 'SUDAH_BAYAR')
                ->sum('totalAmount');
            $dailyChart[] = [
                'day' => $date->format('D'),
                'revenue' => (float) $revenue
            ];
        }

        // Charts - Monthly (last 6 months)
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Order::whereYear('createdAt', $date->year)
                ->whereMonth('createdAt', $date->month)
                ->where('paymentStatus', 'SUDAH_BAYAR')
                ->sum('totalAmount');
            $monthlyChart[] = [
                'month' => $date->format('M'),
                'revenue' => (float) $revenue
            ];
        }

        // Top Services
        $topServices = DB::table('order_items')
            ->join('services', 'order_items.serviceId', '=', 'services.id')
            ->select('services.name', DB::raw('COUNT(*) as count'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        // Top Customers
        $topCustomers = DB::table('orders')
            ->join('customers', 'orders.customerId', '=', 'customers.id')
            ->select(
                'customers.name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN orders."paymentStatus" = \'SUDAH_BAYAR\' THEN orders."totalAmount" ELSE 0 END) as revenue')
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        $data = [
            'today' => ['revenue' => (float) $todayRevenue],
            'monthly' => ['revenue' => (float) $monthlyRevenue, 'orders' => $monthlyOrders],
            'yearly' => ['revenue' => (float) $yearlyRevenue],
            'charts' => [
                'daily' => $dailyChart,
                'monthly' => $monthlyChart,
            ],
            'topServices' => $topServices,
            'topCustomers' => $topCustomers,
        ];

        return $this->sendResponse($data, 'Owner dashboard data retrieved');
    }

    public function kasir()
    {
        $data = [
            'today' => [
                'orders' => Order::whereDate('createdAt', today())->count(),
                'revenue' => Order::whereDate('createdAt', today())->where('paymentStatus', 'SUDAH_BAYAR')->sum('totalAmount'),
            ],
            'unpaidOrders' => Order::where('paymentStatus', 'BELUM_BAYAR')->count(),
            'notPickedUp' => Order::whereNotIn('status', ['DIAMBIL', 'DIBATALKAN'])->count(),
            'pendingOrders' => Order::with(['customer', 'items.service'])
                ->whereNotIn('status', ['SELESAI', 'DIAMBIL', 'DIBATALKAN'])
                ->orderBy('createdAt', 'desc')
                ->take(10)
                ->get(),
            'recentOrders' => Order::with(['customer', 'items.service'])
                ->whereDate('createdAt', today())
                ->orderBy('createdAt', 'desc')
                ->take(10)
                ->get(),
        ];
        return $this->sendResponse($data, 'Kasir dashboard data retrieved');
    }
}
