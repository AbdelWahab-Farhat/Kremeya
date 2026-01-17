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

    Route::middleware(['auth:sanctum', 'is_active'])->group(function () {

        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('customers', CustomerController::class)
            ->middleware('role:admin|employee');

        Route::apiResource('products', ProductContoller::class)
            ->middleware('role:admin');

        Route::apiResource('employees', EmployeeController::class)
            ->middleware('role:admin');

        Route::apiResource('orders', OrderController::class)
            ->middleware('role:admin|employee');

        Route::post('orders/{id}/restore', [OrderController::class, 'restore'])
            ->middleware('role:admin|employee');

        //
        Route::apiResource('products', ProductContoller::class)->middleware('role:admin|employee');
        // Logs
        Route::get('customers/{customer}/logs', [CustomerController::class, 'logs'])->middleware('role:admin|employee');
        Route::get('products/{product}/logs', [ProductContoller::class, 'logs'])->middleware('role:admin');
        Route::get('orders/{order}/logs', [OrderController::class, 'logs'])->middleware('role:admin|employee');

        Route::get('customers/{customer}/orders', [CustomerController::class, 'getOrders'])->middleware('role:admin|employee');

        // Cart (nested under customer)
        Route::middleware('role:admin|employee')->group(function () {
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
        Route::post('coupons/verify', [CouponController::class, 'verify'])
            ->middleware('role:admin|employee');
        Route::apiResource('coupons', CouponController::class)
            ->middleware('role:admin');

        // Cities & Regions
        Route::apiResource('cities', CityController::class)->middleware('role:admin|employee');
        Route::apiResource('regions', RegionController::class)->middleware('role:admin|employee');

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
