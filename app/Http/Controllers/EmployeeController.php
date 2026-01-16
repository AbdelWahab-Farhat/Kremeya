<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Traits\ApiResponse;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeService $service)
    {}

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = $request->only(['search']);

        $resource = $this->service->getAll($filters, $perPage);

        return $this->success(EmployeeResource::collection($resource), 'Employees fetched successfully');
    }

    public function store(CreateEmployeeRequest $request)
    {
        try {
            $employee = $this->service->create($request->validated());
            return $this->success(new EmployeeResource($employee), 'Employee created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(Employee $employee)
    {
        return $this->success(new EmployeeResource($employee->load('user')), 'Employee fetched successfully');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $employee = $this->service->update($employee, $request->validated());
            return $this->success(new EmployeeResource($employee), 'Employee updated successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            $this->service->delete($employee);
            return $this->success(null, 'Employee deleted successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
