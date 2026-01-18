<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Http\Resources\RegionResource;
use App\Http\Traits\ApiResponse;
use App\Models\Region;
use App\Services\RegionService;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RegionService $service)
    {}

    public function index(Request $request)
    {
        $perPage  = (int) $request->get('per_page', 15);
        $resource = $this->service->getAll($perPage);

        return $this->paginatedSuccess($resource, RegionResource::class);
    }

    public function store(CreateRegionRequest $request)
    {
        try {
            $region = $this->service->create($request->validated());
            return $this->success(new RegionResource($region), 'Region created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(Region $region)
    {
        return $this->success(new RegionResource($region->load('city')));
    }

    public function update(UpdateRegionRequest $request, Region $region)
    {
        try {
            $region = $this->service->update($region, $request->validated());
            return $this->success(new RegionResource($region), 'Region updated successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(Region $region)
    {
        try {
            $this->service->delete($region);
            return $this->success(null, 'Region deleted successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete region', 500);
        }
    }
}
