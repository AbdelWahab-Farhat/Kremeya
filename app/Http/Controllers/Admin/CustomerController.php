<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['user', 'city', 'region'])->paginate(10);
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        // For now, redirect to list or show "Not Implemented" as per plan
        // But let's basic implementation
        return view('admin.customers.create');
    }

    public function store(Request $request) {
        // Validation and creation logic would go here
        // For this step I'll focus on listing first as per "beautiful custom UI" request
    }
}
