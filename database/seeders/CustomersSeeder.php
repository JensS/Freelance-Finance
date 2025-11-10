<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Tech Solutions GmbH',
                'email' => 'info@techsolutions.de',
                'address' => [
                    'street' => 'Hauptstraße 123',
                    'city' => 'Berlin',
                    'zip' => '10115',
                ],
                'tax_id' => '12/345/67890',
                'eu_vat_id' => 'DE123456789',
                'contact_person' => 'Max Mustermann',
                'notes' => 'Stammkunde seit 2020',
            ],
            [
                'name' => 'Creative Agency Berlin',
                'email' => 'contact@creative-agency.com',
                'address' => [
                    'street' => 'Friedrichstraße 45',
                    'city' => 'Berlin',
                    'zip' => '10969',
                ],
                'tax_id' => '98/765/43210',
                'eu_vat_id' => 'DE987654321',
                'contact_person' => 'Anna Schmidt',
                'notes' => 'Monatliche Projektabrechnung',
            ],
            [
                'name' => 'StartUp Innovations',
                'email' => 'hello@startup-innovations.de',
                'address' => [
                    'street' => 'Kastanienallee 67',
                    'city' => 'Berlin',
                    'zip' => '10435',
                ],
                'tax_id' => '55/666/77788',
                'eu_vat_id' => 'DE556677788',
                'contact_person' => 'Tom Weber',
                'notes' => 'Neukunde, Erstes Projekt Q1 2025',
            ],
        ];

        foreach ($customers as $customer) {
            \App\Models\Customer::create($customer);
        }
    }
}
