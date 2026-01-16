<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $validated): array
    {
        $identifier = $validated['phone'] ?? $validated['email'] ?? null;

        if (!$identifier) {
            throw ValidationException::withMessages([
                'error' => ['Phone or email is required.'],
            ]);
        }

        $user = User::query()
            ->when(isset($validated['phone']), fn ($q) => $q->where('phone', $validated['phone']))
            ->when(isset($validated['email']), fn ($q) => $q->where('email', $validated['email']))
            ->first();

        // ✅ correct order: Hash::check(plain, hashed)
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'error' => ['Account is not active.'],
            ]);
        }

        $token = $user->createToken($validated['device_name'] ?? 'api-token')->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }
}
