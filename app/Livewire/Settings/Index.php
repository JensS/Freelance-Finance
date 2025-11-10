<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Einstellungen')]
class Index extends Component
{
    use WithFileUploads;

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

    // Branding

    public $company_logo; // For upload

    public ?string $company_logo_path = null; // Stored path

    public $pdf_font; // For upload

    public ?string $pdf_font_family = '';

    public ?string $pdf_font_path = null;

    // Formatting

    public string $date_format = 'd.m.Y';

    public string $success = '';

    public function mount()
    {

        $this->loadSettings();

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

        // Load branding information

        $this->company_logo_path = Setting::get('company_logo_path');

        $this->pdf_font_family = Setting::get('pdf_font_family');

        $this->pdf_font_path = Setting::get('pdf_font_path');

        // Load formatting

        $this->date_format = Setting::get('date_format', 'd.m.Y');

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

            'company_logo' => 'nullable|image|max:1024', // 1MB Max

            'pdf_font' => 'nullable|file|mimes:ttf,otf',

            'pdf_font_family' => 'nullable|string|max:255',

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

        // Handle logo upload

        if ($this->company_logo) {

            // Delete old logo if it exists

            if ($this->company_logo_path && Storage::disk('public')->exists($this->company_logo_path)) {

                Storage::disk('public')->delete($this->company_logo_path);

            }

            $path = $this->company_logo->store('logo', 'public');

            Setting::set('company_logo_path', $path);

            $this->company_logo_path = $path;

        }

        // Handle font upload

        if ($this->pdf_font) {

            if ($this->pdf_font_path && Storage::exists($this->pdf_font_path)) {

                Storage::delete($this->pdf_font_path);

            }

            $fontPath = $this->pdf_font->storeAs('fonts', $this->pdf_font->getClientOriginalName());

            Setting::set('pdf_font_path', $fontPath);

            $this->pdf_font_path = $fontPath;

        }

        if ($this->pdf_font_family) {

            Setting::set('pdf_font_family', $this->pdf_font_family);

        }

        // Save formatting

        Setting::set('date_format', $this->date_format);

        $this->success = 'Einstellungen erfolgreich gespeichert!';

        $this->reset('company_logo', 'pdf_font');

    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
