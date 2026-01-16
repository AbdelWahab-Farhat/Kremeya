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

        $user = User::factory()->create([
            'name'     => 'Abdelwahab Farhat',
            'email'    => 'abdelwahab.dev@gmail.com',
            'password' => 'passItUp',
            'phone'    => '0944909852',
        ]);

        $this->call([
            CitiesSeeder::class,
            RegionsSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $user->assignRole(UserRoles::ADMIN->value);

    }
}
