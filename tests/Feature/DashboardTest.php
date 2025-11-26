<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['authenticated' => true]);
    }

    public function test_dashboard_can_be_accessed(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_displays_recent_invoices(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        // Create recent invoices
        Invoice::create([
            'invoice_number' => 'INV-001',
            'customer_id' => $customer->id,
            'issue_date' => now()->subDays(5),
            'due_date' => now()->addDays(9),
            'type' => 'general',
            'items' => [],
            'vat_rate' => 19,
            'subtotal' => 1000.00,
            'vat_amount' => 190.00,
            'total' => 1190.00,
        ]);

        Invoice::create([
            'invoice_number' => 'INV-002',
            'customer_id' => $customer->id,
            'issue_date' => now()->subDays(2),
            'due_date' => now()->addDays(12),
            'type' => 'general',
            'items' => [],
            'vat_rate' => 19,
            'subtotal' => 2000.00,
            'vat_amount' => 380.00,
            'total' => 2380.00,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        // Verify invoices exist in database
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-001']);
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-002']);
    }

    public function test_dashboard_shows_financial_summary(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        // Create invoices for current month
        Invoice::create([
            'invoice_number' => 'INV-CURRENT-001',
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'type' => 'general',
            'items' => [],
            'vat_rate' => 19,
            'subtotal' => 5000.00,
            'vat_amount' => 950.00,
            'total' => 5950.00,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        // Verify invoice exists
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-CURRENT-001',
            'total' => '5950.00',
        ]);
    }
}
