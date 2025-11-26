<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_displayed(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('E-Mail-Adresse');
        $response->assertSee('Passwort');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->owner()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_cannot_authenticate_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_are_redirected_from_login_page(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }
}
