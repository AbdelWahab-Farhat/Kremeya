<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PerformanceService;

class DashboardController extends Controller
{
    public function __construct(protected PerformanceService $performanceService)
    {
    }

    public function index()
    {
        $overview       = $this->performanceService->getOverviewStats();
        $recentOrders   = $this->performanceService->getRecentOrders();
        $topProducts    = $this->performanceService->getTopSellingProducts();
        $ordersByStatus = $this->performanceService->getOrdersByStatus();

        return view('admin.dashboard', compact('overview', 'recentOrders', 'topProducts', 'ordersByStatus'));
    }
}
