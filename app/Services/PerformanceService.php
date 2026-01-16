<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    /**
     * Get high-level overview statistics.
     */
    public function getOverviewStats(): array
    {
        $totalOrders    = Order::count();
        $totalCustomers = Customer::count();
        $totalProducts  = Product::count();

        // Calculate total revenue from all orders
        // Assuming revenue is sum of (quantity * unit_price) in order_product pivot
        $totalRevenue = DB::table('order_product')
            ->select(DB::raw('SUM(quantity * unit_price) as total'))
            ->value('total') ?? 0;

        return [
            'total_revenue'   => (float) $totalRevenue,
            'total_orders'    => $totalOrders,
            'total_customers' => $totalCustomers,
            'total_products'  => $totalProducts,
        ];
    }

    /**
     * Get orders count grouped by status.
     */
    public function getOrdersByStatus(): array
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status') // Key: status, Value: count
            ->toArray();
    }

    /**
     * Get top selling products based on quantity sold.
     */
    public function getTopSellingProducts(int $limit = 5)
    {
        return DB::table('order_product')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_product.quantity) as total_sold'),
                DB::raw('SUM(order_product.quantity * order_product.unit_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent orders.
     */
    public function getRecentOrders(int $limit = 5)
    {
        return Order::with(['customer.user']) // Simplified eager loading
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id'            => $order->id,
                    'order_code'    => $order->order_code,
                    'customer_name' => $order->customer?->user?->name ?? 'N/A',
                    'total'         => $order->total,
                    'status'        => $order->status->value ?? $order->status,
                    'created_at'    => $order->created_at->toIso8601String(),
                ];
            });
    }

    /**
     * Get daily sales chart data for the last N days.
     */
    public function getSalesChart(int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $sales = DB::table('orders')
            ->join('order_product', 'orders.id', '=', 'order_product.order_id')
            ->where('orders.created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_product.quantity * order_product.unit_price) as daily_total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('daily_total', 'date')
            ->toArray();

        // Fill in missing days with 0
        $chartData = [];
        for ($i = 0; $i < $days; $i++) {
            $date        = $startDate->copy()->addDays($i)->format('Y-m-d');
            $chartData[] = [
                'date'  => $date,
                'total' => (float) ($sales[$date] ?? 0),
            ];
        }

        return $chartData;
    }

    /**
     * Provide simple analysis/insights.
     */
    public function getInsights(): array
    {
        $insights = [];

        // Insight 1: Compare this week vs last week revenue
        $startThisWeek = Carbon::now()->startOfWeek();
        $startLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endLastWeek   = Carbon::now()->subWeek()->endOfWeek();

        $revenueThisWeek = DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startThisWeek)
            ->sum(DB::raw('quantity * unit_price'));

        $revenueLastWeek = DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startLastWeek, $endLastWeek])
            ->sum(DB::raw('quantity * unit_price'));

        if ($revenueLastWeek > 0) {
            $percentageChange = (($revenueThisWeek - $revenueLastWeek) / $revenueLastWeek) * 100;
            $direction        = $percentageChange >= 0 ? 'increase' : 'decrease';
            $insights[]       = sprintf(
                "Revenue has %sd by %.1f%% compared to last week.",
                $direction,
                abs($percentageChange)
            );
        } else {
            $insights[] = "No revenue data from last week to compare.";
        }

        // Insight 2: Average Order Value
        $totalRevenue = DB::table('order_product')->sum(DB::raw('quantity * unit_price'));
        $totalOrders  = Order::count();
        if ($totalOrders > 0) {
            $aov        = $totalRevenue / $totalOrders;
            $insights[] = "Average Order Value is " . number_format($aov, 2);
        }

        return $insights;
    }
}
