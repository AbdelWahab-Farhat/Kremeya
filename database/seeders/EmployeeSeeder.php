<?php
namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $user = User::firstOrCreate(
                ['email' => 'employee' . $i . '@kremeya.com'],
                [
                    'name'     => 'Employee User ' . $i,
                    'password' => Hash::make('password'),
                    'phone'    => '09200000' . sprintf('%02d', $i),
                ]
            );

            $employee = Employee::where('user_id', $user->id)->first();

            if (! $employee) {
                $employee          = new Employee();
                $employee->user_id = $user->id;
                $employee->salary  = 1500 + $i * 100;
                $employee->save();
            }
        }
    }
}
