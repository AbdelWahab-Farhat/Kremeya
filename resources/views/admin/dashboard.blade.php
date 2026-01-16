@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<!-- Overview Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <!-- Total Revenue -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Revenue</h3>
            <div class="h-10 w-10 bg-green-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-end justify-between">
            <span class="text-3xl font-bold text-gray-900">${{ number_format($overview['total_revenue'] ?? 0, 2) }}</span>
            {{-- <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+12.5%</span> --}}
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Orders</h3>
            <div class="h-10 w-10 bg-purple-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-end justify-between">
            <span class="text-3xl font-bold text-gray-900">{{ number_format($overview['total_orders'] ?? 0) }}</span>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Customers</h3>
            <div class="h-10 w-10 bg-blue-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-end justify-between">
            <span class="text-3xl font-bold text-gray-900">{{ number_format($overview['total_customers'] ?? 0) }}</span>
        </div>
    </div>

    <!-- Total Products -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Products</h3>
            <div class="h-10 w-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-end justify-between">
            <span class="text-3xl font-bold text-gray-900">{{ number_format($overview['total_products'] ?? 0) }}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column (2/3) -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 flex items-center transition-colors">
                    View All
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Order ID</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-sm text-gray-600">#{{ $order['order_code'] ?? $order['id'] }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $order['customer_name'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($order['status']) {
                                        'completed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    } }}">
                                    {{ ucfirst($order['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">${{ number_format($order['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No recent orders</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column (1/3) -->
    <div class="space-y-8">
        <!-- Orders by Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Order Status</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($ordersByStatus as $status => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-3
                                {{ match($status) {
                                    'completed' => 'bg-green-500',
                                    'pending' => 'bg-yellow-500',
                                    'cancelled' => 'bg-red-500',
                                    default => 'bg-gray-300'
                                } }}">
                            </div>
                            <span class="text-sm font-medium text-gray-700 capitalize">{{ $status }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Top Products</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($topProducts as $product)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="h-10 w-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 font-bold shrink-0">
                            {{ $loop->iteration }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->total_sold }} sold</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 whitespace-nowrap">${{ number_format($product->total_revenue, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
