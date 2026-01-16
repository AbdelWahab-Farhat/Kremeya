<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RegionsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/regions.csv');

        if (!file_exists($path)) {
            $this->command?->error("regions.csv not found at: {$path}");
            return;
        }

        $hasActive = Schema::hasColumn('regions', 'is_active');

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows  = array_map('str_getcsv', $lines);

        $header = array_map(fn($h) => trim($h), array_shift($rows));

        foreach ($rows as $r) {
            $row = array_combine($header, $r);

            $name = trim($row['name'] ?? '');
            if ($name === '') continue;

            $data = [];

            $data["city_id"] = (int)($row['city_id'] ?? 0);

            if ($hasActive) {
                $data['is_active'] = (int)($row['is_active'] ?? 1);
            }
            Region::updateOrCreate(
                ['name' => $name],
                $data
            );
        }
    }
}
