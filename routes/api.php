<?php

use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DarbAssabilController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductContoller;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('customers/register', [CustomerController::class, 'store']);
    Route::middleware(['auth:sanctum', 'is_active'])->group(function () {

        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('customers', CustomerController::class)
            ->middleware('role:admin|employee');

        // Products - الزبون يمكنه عرض المنتجات فقط
        Route::get('products', [ProductContoller::class, 'index'])
            ->middleware('role:admin|employee|customer');
        Route::get('products/{product}', [ProductContoller::class, 'show'])
            ->middleware('role:admin|employee|customer');
        Route::post('products', [ProductContoller::class, 'store'])
            ->middleware('role:admin');
        Route::put('products/{product}', [ProductContoller::class, 'update'])
            ->middleware('role:admin');
        Route::delete('products/{product}', [ProductContoller::class, 'destroy'])
            ->middleware('role:admin');

        Route::apiResource('employees', EmployeeController::class)
            ->middleware('role:admin');

        // Orders - الزبون يمكنه إنشاء طلب وعرض طلباته
        Route::get('orders', [OrderController::class, 'index'])
            ->middleware('role:admin|employee|customer');
        Route::post('orders', [OrderController::class, 'store'])
            ->middleware('role:admin|employee|customer');
        Route::get('orders/{order}', [OrderController::class, 'show'])
            ->middleware('role:admin|employee|customer');
        Route::put('orders/{order}', [OrderController::class, 'update'])
            ->middleware('role:admin|employee');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])
            ->middleware('role:admin');

        Route::post('orders/{id}/restore', [OrderController::class, 'restore'])
            ->middleware('role:admin|employee');

        // Logs
        Route::get('customers/{customer}/logs', [CustomerController::class, 'logs'])->middleware('role:admin|employee');
        Route::get('products/{product}/logs', [ProductContoller::class, 'logs'])->middleware('role:admin');
        Route::get('orders/{order}/logs', [OrderController::class, 'logs'])->middleware('role:admin|employee');

        Route::get('customers/{customer}/orders', [CustomerController::class, 'getOrders'])->middleware('role:admin|employee');

        // Cart (nested under customer)
        Route::middleware('role:admin|employee|customer')->group(function () {
            Route::get('customers/{customer}/cart', [CartController::class, 'show']);
            Route::post('customers/{customer}/cart/items', [CartController::class, 'addItems']);
            Route::delete('customers/{customer}/cart/items', [CartController::class, 'removeItems']);
            Route::delete('customers/{customer}/cart', [CartController::class, 'clear']);
        });

        // Performance
        Route::get('performance', [PerformanceController::class, 'index'])
            ->middleware('role:admin|employee');
        Route::get('performance/export', [PerformanceController::class, 'export'])
            ->middleware('role:admin|employee');

        // General
        Route::get('enums/order-status', [GeneralController::class, 'orderStatus']);

        // Coupons
        // Coupons - الزبون يمكنه التحقق من الكوبون
        Route::post('coupons/verify', [CouponController::class, 'verify'])
            ->middleware('role:admin|employee|customer');
        Route::apiResource('coupons', CouponController::class)
            ->middleware('role:admin');

        // Cities & Regions - الزبون يمكنه عرض المدن والمناطق لاختيار العنوان
        Route::get('cities', [CityController::class, 'index'])
            ->middleware('role:admin|employee|customer');
        Route::get('cities/{city}', [CityController::class, 'show'])
            ->middleware('role:admin|employee|customer');
        Route::post('cities', [CityController::class, 'store'])->middleware('role:admin');
        Route::put('cities/{city}', [CityController::class, 'update'])->middleware('role:admin');
        Route::delete('cities/{city}', [CityController::class, 'destroy'])->middleware('role:admin');

        Route::get('regions', [RegionController::class, 'index'])
            ->middleware('role:admin|employee|customer');
        Route::get('regions/{region}', [RegionController::class, 'show'])
            ->middleware('role:admin|employee|customer');
        Route::post('regions', [RegionController::class, 'store'])->middleware('role:admin');
        Route::put('regions/{region}', [RegionController::class, 'update'])->middleware('role:admin');
        Route::delete('regions/{region}', [RegionController::class, 'destroy'])->middleware('role:admin');

        // Wallets
        Route::middleware('role:admin|employee')->group(function () {
            Route::get('customers/{customer}/wallet', [WalletController::class, 'customerWallet']);
            Route::get('customers/{customer}/wallet/transactions', [WalletController::class, 'customerTransactions']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('wallets', [WalletController::class, 'index']);
            Route::get('wallets/transactions', [WalletController::class, 'transactions']);
            Route::post('customers/{customer}/wallet/transact', [WalletController::class, 'transact']);
        });

        // Darb Assabil Shipping
        Route::middleware('role:admin|employee')->group(function () {
            Route::get('darb-shipments', [DarbAssabilController::class, 'index']);
            Route::post('orders/{order}/darb-shipment', [DarbAssabilController::class, 'createShipment']);
            Route::get('darb-shipments/local/{shipment}', [DarbAssabilController::class, 'showLocalShipment']);
            Route::post('darb-shipments/{shipment}/sync', [DarbAssabilController::class, 'syncStatus']);
            Route::delete('darb-shipments/{shipment}', [DarbAssabilController::class, 'cancelShipment']);
        });
    });
});
