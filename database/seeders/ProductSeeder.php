<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Product::withTrashed()->updateOrCreate(
                ['sku' => 'SKU-SEED-' . str_pad($i, 6, '0', STR_PAD_LEFT)],
                [
                    'name'          => 'Product ' . $i,
                    'description'   => 'Description for product ' . $i,
                    'selling_price' => rand(100, 500) + 0.99,
                    'buying_price'  => rand(50, 90) + 0.50,
                    'is_active'     => true,
                ]
            );
        }
    }
}
