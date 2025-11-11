<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Einstellungen')]
class Index extends Component
{

    // Company Information
    public string $company_name = '';

    public string $street = '';

    public string $city = '';

    public string $zip = '';

    // Bank Details
    public string $iban = '';

    public string $bic = '';

    // Tax Information
    public string $tax_number = '';

    public string $eu_vat_id = '';

    public float $vat_rate = 19.0;

    // Formatting

    public string $date_format = 'd.m.Y';

    // Paperless Integration
    public ?int $paperless_storage_path = null;

    public array $availableStoragePaths = [];

    // Current tab
    public string $currentTab = 'company';

    public string $success = '';

    public function mount()
    {
        $this->loadStoragePaths();
        $this->loadSettings();
    }

    public function loadStoragePaths()
    {
        try {
            $paperlessService = app(\App\Services\PaperlessService::class);
            $this->availableStoragePaths = $paperlessService->getStoragePaths();
        } catch (\Exception $e) {
            \Log::warning('Failed to load Paperless storage paths', ['error' => $e->getMessage()]);
            $this->availableStoragePaths = [];
        }
    }

    public function loadSettings()
    {
        // Load company information
        $this->company_name = Setting::get('company_name', 'Jens Sage');

        $address = Setting::get('company_address', ['street' => 'Your Street 1', 'city' => 'Berlin', 'zip' => '10115']);

        $this->street = $address['street'] ?? 'Your Street 1';

        $this->city = $address['city'] ?? 'Berlin';

        $this->zip = $address['zip'] ?? '10115';

        // Load bank details
        $bankDetails = Setting::get('bank_details', ['iban' => 'DE1234567890', 'bic' => 'BELADEBEXXX']);

        $this->iban = $bankDetails['iban'] ?? 'DE1234567890';

        $this->bic = $bankDetails['bic'] ?? 'BELADEBEXXX';

        // Load tax information
        $this->tax_number = Setting::get('tax_number', '12/345/67890');

        $this->eu_vat_id = Setting::get('eu_vat_id', 'DE123456789');

        $this->vat_rate = Setting::get('vat_rate', 19);

        // Load formatting
        $this->date_format = Setting::get('date_format', 'd.m.Y');

        // Load Paperless integration
        $this->paperless_storage_path = Setting::get('paperless_storage_path');
    }

    public function save()
    {

        $this->validate([

            'company_name' => 'required|string|max:255',

            'street' => 'required|string|max:255',

            'city' => 'required|string|max:255',

            'zip' => 'required|string|max:10',

            'iban' => 'required|string|max:34',

            'bic' => 'required|string|max:11',

            'tax_number' => 'required|string|max:50',

            'eu_vat_id' => 'required|string|max:20',

            'vat_rate' => 'required|numeric|min:0|max:100',

            'date_format' => 'required|string|max:20',

        ]);

        // Save company information

        Setting::set('company_name', $this->company_name);

        Setting::set('company_address', ['street' => $this->street, 'city' => $this->city, 'zip' => $this->zip]);

        // Save bank details

        Setting::set('bank_details', ['iban' => $this->iban, 'bic' => $this->bic]);

        // Save tax information

        Setting::set('tax_number', $this->tax_number);

        Setting::set('eu_vat_id', $this->eu_vat_id);

        Setting::set('vat_rate', $this->vat_rate);

        // Save formatting

        Setting::set('date_format', $this->date_format);

        // Save Paperless integration
        Setting::set('paperless_storage_path', $this->paperless_storage_path);

        $this->success = 'Einstellungen erfolgreich gespeichert!';

    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
