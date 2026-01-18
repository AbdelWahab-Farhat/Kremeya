<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Http\Traits\ApiResponse;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CityService $service)
    {}

    public function index(Request $request)
    {
        $perPage  = (int) $request->get('per_page', 15);
        $resource = $this->service->getAll($perPage);

        return $this->paginatedSuccess($resource, CityResource::class);
    }

    public function store(CreateCityRequest $request)
    {
        try {
            $city = $this->service->create($request->validated());
            return $this->success(new CityResource($city), 'City created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(City $city)
    {
        return $this->success(new CityResource($city->load('regions')));
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        try {
            $city = $this->service->update($city, $request->validated());
            return $this->success(new CityResource($city), 'City updated successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(City $city)
    {
        try {
            $this->service->delete($city);
            return $this->success(null, 'City deleted successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete city', 500);
        }
    }
}
