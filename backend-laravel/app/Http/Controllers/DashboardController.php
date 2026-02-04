<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Owner Dashboard with caching and optimized queries
     */
    public function owner()
    {
        // Cache dashboard data for 1 minute to reduce database load
        $data = Cache::remember('dashboard_owner_' . today()->format('Y-m-d'), 60, function () {
            return $this->getOwnerDashboardData();
        });

        return $this->sendResponse($data, 'Owner dashboard data retrieved');
    }

    /**
     * Get owner dashboard data with optimized queries
     */
    private function getOwnerDashboardData(): array
    {
        $today = today();
        $startOfMonth = now()->startOfMonth();
        $startOfYear = now()->startOfYear();
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

        // Batch query for stats using single query with conditional aggregation
        $stats = DB::table('orders')
            ->selectRaw('
                SUM(CASE WHEN DATE("createdAt") = ? AND "paymentStatus" = \'SUDAH_BAYAR\' THEN "totalAmount" ELSE 0 END) as today_revenue,
                SUM(CASE WHEN "createdAt" >= ? AND "paymentStatus" = \'SUDAH_BAYAR\' THEN "totalAmount" ELSE 0 END) as monthly_revenue,
                SUM(CASE WHEN "createdAt" >= ? AND "paymentStatus" = \'SUDAH_BAYAR\' THEN "totalAmount" ELSE 0 END) as yearly_revenue,
                COUNT(CASE WHEN "createdAt" >= ? THEN 1 END) as monthly_orders
            ', [$today, $startOfMonth, $startOfYear, $startOfMonth])
            ->first();

        // Daily chart - single query instead of 7 separate queries
        $dailyData = DB::table('orders')
            ->selectRaw('DATE("createdAt") as date, SUM("totalAmount") as revenue')
            ->where('createdAt', '>=', $sevenDaysAgo)
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->groupByRaw('DATE("createdAt")')
            ->pluck('revenue', 'date')
            ->toArray();

        $dailyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dailyChart[] = [
                'day' => $date->format('D'),
                'revenue' => (float) ($dailyData[$dateKey] ?? 0)
            ];
        }

        // Monthly chart - single query instead of 6 separate queries
        $monthlyData = DB::table('orders')
            ->selectRaw('EXTRACT(YEAR FROM "createdAt") as year, EXTRACT(MONTH FROM "createdAt") as month, SUM("totalAmount") as revenue')
            ->where('createdAt', '>=', $sixMonthsAgo)
            ->where('paymentStatus', 'SUDAH_BAYAR')
            ->groupByRaw('EXTRACT(YEAR FROM "createdAt"), EXTRACT(MONTH FROM "createdAt")')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month)
            ->toArray();

        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->year . '-' . $date->month;
            $monthlyChart[] = [
                'month' => $date->format('M'),
                'revenue' => (float) ($monthlyData[$key]->revenue ?? 0)
            ];
        }

        // Top Services - already optimized
        $topServices = DB::table('order_items')
            ->join('services', 'order_items.serviceId', '=', 'services.id')
            ->select('services.name', DB::raw('COUNT(*) as count'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        // Top Customers - already optimized
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

        return [
            'today' => ['revenue' => (float) ($stats->today_revenue ?? 0)],
            'monthly' => [
                'revenue' => (float) ($stats->monthly_revenue ?? 0),
                'orders' => (int) ($stats->monthly_orders ?? 0)
            ],
            'yearly' => ['revenue' => (float) ($stats->yearly_revenue ?? 0)],
            'charts' => [
                'daily' => $dailyChart,
                'monthly' => $monthlyChart,
            ],
            'topServices' => $topServices,
            'topCustomers' => $topCustomers,
        ];
    }

    /**
     * Kasir Dashboard with optimized queries
     */
    public function kasir()
    {
        // Cache for 30 seconds since kasir needs more real-time data
        $data = Cache::remember('dashboard_kasir_' . today()->format('Y-m-d-H-i'), 30, function () {
            return $this->getKasirDashboardData();
        });

        return $this->sendResponse($data, 'Kasir dashboard data retrieved');
    }

    /**
     * Get kasir dashboard data with optimized queries
     */
    private function getKasirDashboardData(): array
    {
        $today = today();

        // Single query for counts
        $counts = DB::table('orders')
            ->selectRaw('
                COUNT(CASE WHEN DATE("createdAt") = ? THEN 1 END) as today_orders,
                SUM(CASE WHEN DATE("createdAt") = ? AND "paymentStatus" = \'SUDAH_BAYAR\' THEN "totalAmount" ELSE 0 END) as today_revenue,
                COUNT(CASE WHEN "paymentStatus" = \'BELUM_BAYAR\' THEN 1 END) as unpaid_orders,
                COUNT(CASE WHEN "status" NOT IN (\'DIAMBIL\', \'DIBATALKAN\') THEN 1 END) as not_picked_up
            ', [$today, $today])
            ->first();

        // Pending orders with eager loading and select specific columns
        $pendingOrders = Order::with(['customer:id,name,phone', 'items.service:id,name,price'])
            ->select('id', 'orderNumber', 'customerId', 'status', 'paymentStatus', 'totalAmount', 'createdAt')
            ->whereNotIn('status', ['SELESAI', 'DIAMBIL', 'DIBATALKAN'])
            ->orderBy('createdAt', 'desc')
            ->take(10)
            ->get();

        // Recent orders with eager loading and select specific columns
        $recentOrders = Order::with(['customer:id,name,phone', 'items.service:id,name,price'])
            ->select('id', 'orderNumber', 'customerId', 'status', 'paymentStatus', 'totalAmount', 'createdAt')
            ->whereDate('createdAt', $today)
            ->orderBy('createdAt', 'desc')
            ->take(10)
            ->get();

        return [
            'today' => [
                'orders' => (int) ($counts->today_orders ?? 0),
                'revenue' => (float) ($counts->today_revenue ?? 0),
            ],
            'unpaidOrders' => (int) ($counts->unpaid_orders ?? 0),
            'notPickedUp' => (int) ($counts->not_picked_up ?? 0),
            'pendingOrders' => $pendingOrders,
            'recentOrders' => $recentOrders,
        ];
    }

    /**
     * Clear dashboard cache (call after order changes)
     */
    public static function clearCache(): void
    {
        Cache::forget('dashboard_owner_' . today()->format('Y-m-d'));
        Cache::forget('dashboard_kasir_' . today()->format('Y-m-d-H-i'));
    }
}
