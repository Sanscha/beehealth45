<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice Reader',
            'email' => 'alice@example.com',
            'phone' => '9988776655',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'user' => [
                    'name' => 'Alice Reader',
                    'email' => 'alice@example.com',
                    'role' => 'member',
                ],
            ])
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'phone' => '9988776655',
            'role' => 'member',
        ]);
    }

    public function test_registration_validation_fails_on_duplicate_email_or_phone(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'phone' => '9999999999',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'phone' => '9999999999',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone']);
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'staff_user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'staff',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'staff_user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'role' => 'staff',
                ],
            ])
            ->assertJsonStructure(['token']);
    }

    public function test_user_can_login_with_phone(): void
    {
        User::factory()->create([
            'phone' => '9876500000',
            'password' => bcrypt('phonepass123'),
            'role' => 'member',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => '9876500000',
            'password' => 'phonepass123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'valid@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_authenticated_user_can_access_profile_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => ['id' => $user->id],
            ]);

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
    }
}
