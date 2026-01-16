<?php

use App\Http\Controllers\Api\PerformanceController;
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
    });
});
