<?php
namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::firstOrCreate(
            ['email' => 'abdelwahab.dev@gmail.com'],
            [
                'name'     => 'Abdelwahab Farhat',
                'password' => 'passItUp',
                'phone'    => '0944909852',
            ]
        );

        $this->call([
            CitiesSeeder::class,
            RegionsSeeder::class,
            RolesAndPermissionsSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            EmployeeSeeder::class,
            CouponSeeder::class
        ]);

        $user->assignRole(UserRoles::ADMIN->value);

    }
}
