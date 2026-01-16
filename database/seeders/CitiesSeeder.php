<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/cities.csv');

        if (!file_exists($path)) {
            $this->command?->error("cities.csv not found at: {$path}");
            return;
        }



        $hasActive = Schema::hasColumn('cities', 'is_active');

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows  = array_map('str_getcsv', $lines);

        $header = array_map(fn($h) => trim($h), array_shift($rows));

        foreach ($rows as $r) {
            $row = array_combine($header, $r);

            $name = trim($row['name'] ?? '');
            if ($name === '') continue;

            $data = [];

            $data["is_region_required"] = (int)($row['is_region_required'] ?? 0);

            if ($hasActive) {
                $data['is_active'] = (int)($row['is_active'] ?? 1);
            }
            City::updateOrCreate(
                ['name' => $name],
                $data
            );
        }
    }
}
