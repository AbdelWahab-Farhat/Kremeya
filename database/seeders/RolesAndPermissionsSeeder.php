<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // لو تستخدم web أو api guard عدّلها
        $guard = 'web';

        // Permissions (أمثلة)
        $permissions = [
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.delete',
            'customers.view',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => $guard]);
        }

        // Roles
        $admin    = Role::firstOrCreate(['name' => UserRoles::ADMIN->value, 'guard_name' => $guard]);
        $employee = Role::firstOrCreate(['name' => UserRoles::Employee->value, 'guard_name' => $guard]);
        $customer = Role::firstOrCreate(['name' => UserRoles::CUSTOMER->value, 'guard_name' => $guard]);


        $admin->syncPermissions(Permission::where('guard_name', $guard)->get());

        $employee->syncPermissions([
            'orders.view',
            'orders.create',
            'orders.update',
            'customers.view',
        ]);

        $customer->syncPermissions([
            'orders.view',
        ]);
    }
}
