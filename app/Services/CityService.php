<?php
namespace App\Services;

use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CityService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return City::latest()->paginate($perPage);
    }

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(City $city, array $data): City
    {
        $city->update($data);
        return $city;
    }

    public function delete(City $city): void
    {
        $city->delete();
    }
}
