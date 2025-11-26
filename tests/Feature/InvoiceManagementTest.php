<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['authenticated' => true]);
    }

    public function test_invoices_index_can_be_accessed(): void
    {
        $response = $this->get('/invoices');

        $response->assertStatus(200);
    }

    public function test_invoice_can_be_created_with_customer_and_items(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer GmbH',
            'email' => 'test@customer.de',
            'address' => 'Test Street 1',
            'city' => 'Berlin',
            'zip' => '10115',
            'country' => 'Deutschland',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'TEST-001',
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'type' => 'general',
            'items' => [
                [
                    'description' => 'Consulting Services',
                    'quantity' => 10,
                    'unit_price' => 100.00,
                    'total' => 1000.00,
                ],
            ],
            'vat_rate' => 19,
            'subtotal' => 1000.00,
            'vat_amount' => 190.00,
            'total' => 1190.00,
        ]);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'TEST-001',
            'customer_id' => $customer->id,
        ]);

        $this->assertCount(1, $invoice->items);
        $this->assertEquals('Consulting Services', $invoice->items[0]['description']);
    }

    public function test_invoice_totals_are_calculated_correctly(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'TEST-002',
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'type' => 'general',
            'vat_rate' => 19,
            'items' => [
                [
                    'description' => 'Service 1',
                    'quantity' => 5,
                    'unit_price' => 100.00,
                    'total' => 500.00,
                ],
                [
                    'description' => 'Service 2',
                    'quantity' => 3,
                    'unit_price' => 200.00,
                    'total' => 600.00,
                ],
            ],
            'subtotal' => 0,
            'vat_amount' => 0,
            'total' => 0,
        ]);

        // Calculate totals
        $invoice->calculateTotals();
        $invoice->save();

        $this->assertEquals('1100.00', $invoice->subtotal);
        $this->assertEquals('209.00', $invoice->vat_amount);
        $this->assertEquals('1309.00', $invoice->total);
    }

    public function test_invoice_can_be_deleted(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'TEST-003',
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'type' => 'general',
            'items' => [],
            'vat_rate' => 19,
            'subtotal' => 100.00,
            'vat_amount' => 19.00,
            'total' => 119.00,
        ]);

        $invoiceId = $invoice->id;

        $invoice->delete();

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoiceId,
        ]);
    }

    public function test_invoice_number_generation_is_sequential(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        // Create first invoice
        Invoice::create([
            'invoice_number' => '1',
            'customer_id' => $customer->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'type' => 'general',
            'items' => [],
            'vat_rate' => 19,
            'subtotal' => 100.00,
            'vat_amount' => 19.00,
            'total' => 119.00,
        ]);

        // Generate next invoice number
        $nextNumber = Invoice::generateInvoiceNumber();

        $this->assertEquals('2', $nextNumber);
    }
}
