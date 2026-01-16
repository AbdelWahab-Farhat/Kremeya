<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    protected PerformanceService $performanceService;

    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Get performance dashboard data.
     */
    public function index(Request $request): JsonResponse
    {
        // Gather all statistics
        $overview       = $this->performanceService->getOverviewStats();
        $ordersByStatus = $this->performanceService->getOrdersByStatus();
        $topProducts    = $this->performanceService->getTopSellingProducts();
        $recentOrders   = $this->performanceService->getRecentOrders();
        $salesChart     = $this->performanceService->getSalesChart(7); // Last 7 days
        $insights       = $this->performanceService->getInsights();

        return response()->json([
            'success' => true,
            'data'    => [
                'overview' => $overview,
                'charts'   => [
                    'sales_last_7_days' => $salesChart,
                    'orders_by_status'  => $ordersByStatus,
                ],
                'lists'    => [
                    'top_products'  => $topProducts,
                    'recent_orders' => $recentOrders,
                ],
                'insights' => $insights,
            ],
            'message' => 'Performance statistics retrieved successfully.',
        ]);
    }
}
