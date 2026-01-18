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

    /**
     * DO NOT USE IT (ABDELWAHAB FARHAT 2025-1-18)
     * @deprecated
     */
    public function delete(Employee $employee)
    {
        return DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            $user->delete();

            return true;
        });
    }
}
