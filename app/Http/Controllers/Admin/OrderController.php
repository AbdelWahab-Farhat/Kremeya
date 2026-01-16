<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer.user', 'products'])->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }
}
