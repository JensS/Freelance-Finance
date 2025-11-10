<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InvoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first customer
        $customer = \App\Models\Customer::first();

        if (! $customer) {
            $this->command->warn('No customers found. Please seed customers first.');

            return;
        }

        $invoice = \App\Models\Invoice::create([
            'invoice_number' => '503',
            'customer_id' => $customer->id,
            'type' => 'project',
            'project_name' => 'Outerwear / RUSH Kampagne',
            'service_period_start' => '2025-08-04',
            'service_period_end' => '2025-08-09',
            'service_location' => 'Frankfurt',
            'issue_date' => '2025-08-12',
            'due_date' => '2025-08-26',
            'items' => [
                [
                    'description' => 'Director creative fee / Gage',
                    'quantity' => 3,
                    'unit' => 'Tag',
                    'unit_price' => 2000.00,
                    'total' => 6000.00,
                ],
                [
                    'description' => 'Kameratechnik: A Kamera',
                    'quantity' => 1,
                    'unit' => 'Pauschal',
                    'unit_price' => 1500.00,
                    'total' => 1500.00,
                ],
                [
                    'description' => 'Kameratechnik: B Kamera',
                    'quantity' => 1,
                    'unit' => 'Pauschal',
                    'unit_price' => 300.00,
                    'total' => 300.00,
                ],
                [
                    'description' => 'Filmrollen Kauf: 16mm Magazine B Kamera',
                    'quantity' => 3,
                    'unit' => 'Stk',
                    'unit_price' => 55.00,
                    'total' => 165.00,
                ],
                [
                    'description' => 'Entwicklung und Scan 16mm Film B Kamera',
                    'quantity' => 3,
                    'unit' => 'Stk',
                    'unit_price' => 140.00,
                    'total' => 420.00,
                ],
            ],
            'subtotal' => 8385.00,
            'vat_rate' => 19.00,
            'vat_amount' => 1593.15,
            'total' => 9978.15,
            'notes' => 'Test invoice based on reference document',
        ]);

        $this->command->info("Created test invoice: {$invoice->invoice_number}");
    }
}
