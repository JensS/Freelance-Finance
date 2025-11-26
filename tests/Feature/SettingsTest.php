<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['authenticated' => true]);
    }

    public function test_settings_page_can_be_accessed(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
    }

    public function test_company_name_can_be_stored_and_retrieved(): void
    {
        Setting::set('company_name', 'Test Company GmbH');

        $companyName = Setting::get('company_name');

        $this->assertEquals('Test Company GmbH', $companyName);
    }

    public function test_company_address_can_be_stored_and_retrieved(): void
    {
        $address = [
            'street' => 'Test Street 1',
            'city' => 'Berlin',
            'zip' => '10115',
        ];

        Setting::set('company_address', $address);

        $retrieved = Setting::get('company_address');

        $this->assertEquals('Test Street 1', $retrieved['street']);
        $this->assertEquals('Berlin', $retrieved['city']);
        $this->assertEquals('10115', $retrieved['zip']);
    }

    public function test_bank_details_can_be_stored_and_retrieved(): void
    {
        $bankDetails = [
            'iban' => 'DE1234567890',
            'bic' => 'BELADEBEXXX',
        ];

        Setting::set('bank_details', $bankDetails);

        $retrieved = Setting::get('bank_details');

        $this->assertEquals('DE1234567890', $retrieved['iban']);
        $this->assertEquals('BELADEBEXXX', $retrieved['bic']);
    }

    public function test_default_value_is_returned_when_setting_not_found(): void
    {
        $value = Setting::get('nonexistent_key', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    public function test_setting_can_be_updated(): void
    {
        Setting::set('test_key', 'initial_value');
        Setting::set('test_key', 'updated_value');

        $value = Setting::get('test_key');

        $this->assertEquals('updated_value', $value);
        $this->assertEquals(1, Setting::where('key', 'test_key')->count());
    }
}
