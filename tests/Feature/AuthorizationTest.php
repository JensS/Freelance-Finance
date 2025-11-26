<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    // Owner access tests

    public function test_owner_can_access_dashboard(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_invoices(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/invoices');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_quotes(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/quotes');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_customers(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_accounting(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/accounting');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_reports(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_settings(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
    }

    public function test_owner_can_access_user_management(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(200);
    }

    // Tax Accountant access tests

    public function test_tax_accountant_can_access_dashboard(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_tax_accountant_can_access_accounting(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/accounting');

        $response->assertStatus(200);
    }

    public function test_tax_accountant_can_access_reports(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
    }

    public function test_tax_accountant_can_access_transaction_verification(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/transactions/verify-imports');

        $response->assertStatus(200);
    }

    // Tax Accountant restricted access tests

    public function test_tax_accountant_cannot_access_invoices(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/invoices');

        $response->assertStatus(403);
    }

    public function test_tax_accountant_cannot_access_quotes(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/quotes');

        $response->assertStatus(403);
    }

    public function test_tax_accountant_cannot_access_customers(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(403);
    }

    public function test_tax_accountant_cannot_access_settings(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_tax_accountant_cannot_access_user_management(): void
    {
        $user = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(403);
    }
}
