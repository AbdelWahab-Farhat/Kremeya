<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Enums\Gender;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_customer_with_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'city' => 'Cairo',
            'region' => 'Maadi',
            'gender' => Gender::MALE->value, // Assuming Gender enum has 'male' value, adjusting based on actual Enum if needed
        ];

        $response = $this->postJson(route('customers.store'), $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'customer' => ['user']]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('customers', ['phone' => '1234567890']);
    }

    public function test_can_update_customer_and_user(): void
    {
        // manually create user and customer first
        $user = User::factory()->create();
        $customer = Customer::create([ // Using create directly might need guarding against fillables if factory not used
             'user_id' => $user->id,
             'customer_code' => 'C' . $user->id . rand(100,999), // mimic boot logic if needed or let boot handle if factory
             'phone' => '9876543210',
             'gender' => Gender::FEMALE->value,
        ]);

        $updateData = [
            'name' => 'John Updated',
            'email' => 'johncodes@example.com',
            'phone' => '1122334455',
            'gender' => Gender::MALE->value,
        ];

        $response = $this->actingAs($user)->putJson(route('customers.update', $customer->id), $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', ['email' => 'johncodes@example.com', 'name' => 'John Updated']);
        $this->assertDatabaseHas('customers', ['phone' => '1122334455']);
    }
}
