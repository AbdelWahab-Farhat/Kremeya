<?php
namespace App\Services;

use App\Enums\UserRoles;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Employee::with('user')
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole(UserRoles::Employee->value);

            return Employee::create([
                'user_id' => $user->id,
                'salary'  => $data['salary'],
            ]);
        });
    }

    public function update(Employee $employee, array $data)
    {
        return DB::transaction(function () use ($employee, $data) {
            $user = $employee->user;

            $userData = array_filter([
                'name'  => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if (isset($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if (! empty($userData)) {
                $user->update($userData);
            }

            if (isset($data['salary'])) {
                $employee->update(['salary' => $data['salary']]);
            }

            return $employee->fresh(['user']);
        });
    }

    public function delete(Employee $employee)
    {
        return DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete(); // This might cascade delete user if configured, but safe to delete specific first if needed
                                 // Based on migration: $table->foreignId('user_id')->constrained()->onDelete('cascade');
                                 // Deleting Employee doesn't delete User automatically unless we want to.
                                 // Usually if we delete an employee record, we might want to delete the user too or just the role?
                                 // "CRUD for Employee" usually implies deleting the "Employee entity" which is the User+Employee data.
                                 // Let's delete the user, which will cascade delete the employee record due to DB constraint (if user is deleted).
                                 // But wait, the constraint is on `employee` table: `user_id` references `users`.
                                 // So deleting `users` deletes `employee`.
                                 // Deleting `employee` does NOT delete `users`.

            // Should we delete the User account? Yes, usually for "Employee CRUD".
            $user->delete();

            return true;
        });
    }
}
