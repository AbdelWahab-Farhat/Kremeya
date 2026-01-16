<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_english_message()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'test@example.com',
            'password' => 'password',
        ], ['Accept-Language' => 'en']);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Welcome to Kremeya Application',
            ]);
    }

    public function test_login_returns_arabic_message()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'test@example.com',
            'password' => 'password',
        ], ['Accept-Language' => 'ar']);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'مرحباً بك في تطبيق كريمية',
            ]);
    }
}
