<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Traits\ApiResponse;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CustomerService $service)
    {}

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        $resource = $this->service->getAll($request->all(), $perPage);

        return $this->success($resource, 'Customers fetched successfully');
    }

    public function store(CreateCustomerRequest $request)
    {
        try {
            $resource = $this->service->create($request->validated())->load(['user', 'region', 'city']);
            return $this->success(new CustomerResource($resource), 'Customer created successfully', 201);

        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            $resource = $this->service->update($customer, $request->validated());

            return $this->success($resource, 'Customer updated successfully');

        } catch (\Throwable $e) {
            return $this->error('Failed to update customer', 500);
        }
    }

    public function logs(Customer $customer)
    {
        return \App\Http\Resources\LogResource::collection($customer->logs);
    }

    public function getOrders(Customer $customer, Request $request)
    {
        $filters = $request->only(['status']);
        $orders  = $this->service->getOrders($customer, $filters);
        return $this->success(\App\Http\Resources\OrderResource::collection($orders), 'Customer orders fetched successfully');
    }
}
