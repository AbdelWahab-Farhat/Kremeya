<?php
namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'customer' . $i . '@kremeya.com'],
                [
                    'name'     => 'Customer User ' . $i,
                    'password' => Hash::make('password'),
                    'phone'    => '09100000' . sprintf('%02d', $i),
                ]
            );

            Customer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'city_id'       => 1,
                    'region_id'     => null,
                    'gender'        => $i % 2 == 0 ? Gender::MALE : Gender::FEMALE,
                    'customer_code' => 'CUST-SEED-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                ]
            );
        }
    }
}
