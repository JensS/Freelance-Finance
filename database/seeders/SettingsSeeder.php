<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'company_name' => 'Jens Sage',
            'company_address' => [
                'street' => 'Your Street 1',
                'city' => 'Berlin',
                'zip' => '10115',
            ],
            'bank_details' => [
                'iban' => 'DE1234567890',
                'bic' => 'BELADEBEXXX',
            ],
            'tax_number' => '12/345/67890',
            'eu_vat_id' => 'DE123456789',
            'vat_rate' => 19,
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
