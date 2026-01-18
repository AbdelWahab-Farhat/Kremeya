<?php
namespace App\Services;

use App\Enums\Gender;
use App\Enums\UserRoles;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Customer::query()->with(['user', 'region', 'city']);

        if (! empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('customer_code', 'like', "%{$s}%");
            });
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (! empty($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $validated)
    {
        $customer = DB::transaction(function () use ($validated) {

            $user = User::create([
                'phone'    => $validated['phone'],
                'name'     => $validated['name'] ?? null,
                'email'    => $validated['email'] ?? null,
                'password' => isset($validated['password'])
                    ? Hash::make($validated['password'])
                    : null,
            ]);

            $user->assignRole(UserRoles::CUSTOMER->value);

            return $user->customer()->create([
                'city_id'   => $validated['city_id'] ?? null,
                'region_id' => $validated['region_id'] ?? null,
                'gender'    => $validated['gender'] ?? Gender::UNKOWN->value,
            ]);
        });

        // Dispatch AI gender prediction job if gender was not provided
        if (empty($validated['gender'])) {
            \App\Jobs\PredictCustomerGenderJob::dispatch($customer);
        }

        return $customer;
    }

    public function update(Customer $customer, array $validated): CustomerResource
    {
        $customer = DB::transaction(function () use ($customer, $validated) {

            // user data (only if provided)
            $userData = [];
            if (array_key_exists('name', $validated)) {
                $userData['name'] = $validated['name'];
            }

            if (array_key_exists('email', $validated)) {
                $userData['email'] = $validated['email'];
            }

            if (! empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            if (! empty($userData)) {
                $customer->user->update($userData);
            }

            $customer->update([
                'phone'     => $validated['phone'] ?? $customer->phone,
                'city_id'   => $validated['city_id'] ?? $customer->city_id,
                // Could be null
                'region_id' => $validated['region_id'],
                'gender'    => $validated['gender'] ?? $customer->gender,
            ]);

            return $customer->fresh()->load(['user', 'region', 'city']);
        });

        return new CustomerResource($customer);
    }

    public function getCustomerLogs(Customer $customer)
    {
        return $customer->logs()->latest()->get();
    }

    public function getOrders(Customer $customer, array $filters = [])
    {
        $query = $customer->orders();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }
}
