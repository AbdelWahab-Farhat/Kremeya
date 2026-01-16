<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\AuthController::class, 'webLogin'])->name('login');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'webLogout'])->name('logout');

// Admin panel - Protected routes
Route::prefix('admin')->name('admin.')->middleware('auth:web')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);

    // Products
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);

    // Orders
    Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);

    // Employees
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
});

// Old Vue route - Remove
// Route::get('/admin/{any?}', function () {
//     return view('admin');
// })->where('any', '.*');
