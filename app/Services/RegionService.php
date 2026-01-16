<?php
namespace App\Services;

use App\Models\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RegionService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Region::with('city')->latest()->paginate($perPage);
    }

    public function create(array $data): Region
    {
        return Region::create($data);
    }

    public function update(Region $region, array $data): Region
    {
        $region->update($data);
        return $region;
    }

    public function delete(Region $region): void
    {
        $region->delete();
    }
}
