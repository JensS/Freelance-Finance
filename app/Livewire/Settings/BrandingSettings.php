<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Branding Einstellungen')]
class BrandingSettings extends Component
{
    use WithFileUploads;

    // Logo
    public $company_logo;
    public ?string $company_logo_path = null;

    // Heading font
    public $heading_font_file;
    public string $heading_font_family = 'Fira Sans';
    public ?string $heading_font_path = null;
    public string $heading_font_size = '24px';
    public string $heading_font_weight = 'bold';
    public string $heading_font_style = 'normal';
    public string $heading_font_color = '#333333';

    // Small heading font
    public $small_heading_font_file;
    public string $small_heading_font_family = 'Fira Sans';
    public ?string $small_heading_font_path = null;
    public string $small_heading_font_size = '14px';
    public string $small_heading_font_weight = 'bold';
    public string $small_heading_font_style = 'normal';
    public string $small_heading_font_color = '#333333';

    // Body font
    public $body_font_file;
    public string $body_font_family = 'Fira Sans';
    public ?string $body_font_path = null;
    public string $body_font_size = '12px';
    public string $body_font_weight = 'normal';
    public string $body_font_style = 'normal';
    public string $body_font_color = '#333333';

    public string $success = '';

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        // Load logo
        $this->company_logo_path = Setting::get('company_logo_path');

        // Load font styles
        $fontStyles = Setting::get('font_styles', [
            'heading' => [
                'font_family' => 'Fira Sans',
                'font_path' => null,
                'font_size' => '24px',
                'font_weight' => 'bold',
                'font_style' => 'normal',
                'color' => '#333333',
            ],
            'small_heading' => [
                'font_family' => 'Fira Sans',
                'font_path' => null,
                'font_size' => '14px',
                'font_weight' => 'bold',
                'font_style' => 'normal',
                'color' => '#333333',
            ],
            'body' => [
                'font_family' => 'Fira Sans',
                'font_path' => null,
                'font_size' => '12px',
                'font_weight' => 'normal',
                'font_style' => 'normal',
                'color' => '#333333',
            ],
        ]);

        // Heading
        $this->heading_font_family = $fontStyles['heading']['font_family'] ?? 'Fira Sans';
        $this->heading_font_path = $fontStyles['heading']['font_path'] ?? null;
        $this->heading_font_size = $fontStyles['heading']['font_size'] ?? '24px';
        $this->heading_font_weight = $fontStyles['heading']['font_weight'] ?? 'bold';
        $this->heading_font_style = $fontStyles['heading']['font_style'] ?? 'normal';
        $this->heading_font_color = $fontStyles['heading']['color'] ?? '#333333';

        // Small heading
        $this->small_heading_font_family = $fontStyles['small_heading']['font_family'] ?? 'Fira Sans';
        $this->small_heading_font_path = $fontStyles['small_heading']['font_path'] ?? null;
        $this->small_heading_font_size = $fontStyles['small_heading']['font_size'] ?? '14px';
        $this->small_heading_font_weight = $fontStyles['small_heading']['font_weight'] ?? 'bold';
        $this->small_heading_font_style = $fontStyles['small_heading']['font_style'] ?? 'normal';
        $this->small_heading_font_color = $fontStyles['small_heading']['color'] ?? '#333333';

        // Body
        $this->body_font_family = $fontStyles['body']['font_family'] ?? 'Fira Sans';
        $this->body_font_path = $fontStyles['body']['font_path'] ?? null;
        $this->body_font_size = $fontStyles['body']['font_size'] ?? '12px';
        $this->body_font_weight = $fontStyles['body']['font_weight'] ?? 'normal';
        $this->body_font_style = $fontStyles['body']['font_style'] ?? 'normal';
        $this->body_font_color = $fontStyles['body']['color'] ?? '#333333';
    }

    public function save()
    {
        $this->validate([
            'company_logo' => 'nullable|image|max:1024',

            // Heading font
            'heading_font_file' => 'nullable|file|mimes:ttf,otf',
            'heading_font_family' => 'required|string|max:255',
            'heading_font_size' => 'required|string|max:10',
            'heading_font_weight' => 'required|in:normal,bold',
            'heading_font_style' => 'required|in:normal,italic',
            'heading_font_color' => 'required|string|max:7',

            // Small heading font
            'small_heading_font_file' => 'nullable|file|mimes:ttf,otf',
            'small_heading_font_family' => 'required|string|max:255',
            'small_heading_font_size' => 'required|string|max:10',
            'small_heading_font_weight' => 'required|in:normal,bold',
            'small_heading_font_style' => 'required|in:normal,italic',
            'small_heading_font_color' => 'required|string|max:7',

            // Body font
            'body_font_file' => 'nullable|file|mimes:ttf,otf',
            'body_font_family' => 'required|string|max:255',
            'body_font_size' => 'required|string|max:10',
            'body_font_weight' => 'required|in:normal,bold',
            'body_font_style' => 'required|in:normal,italic',
            'body_font_color' => 'required|string|max:7',
        ]);

        // Handle logo upload
        if ($this->company_logo) {
            if ($this->company_logo_path && Storage::disk('public')->exists($this->company_logo_path)) {
                Storage::disk('public')->delete($this->company_logo_path);
            }
            $path = $this->company_logo->store('logo', 'public');
            Setting::set('company_logo_path', $path);
            $this->company_logo_path = $path;
        }

        // Handle heading font upload
        if ($this->heading_font_file) {
            if ($this->heading_font_path && Storage::exists($this->heading_font_path)) {
                Storage::delete($this->heading_font_path);
            }
            $this->heading_font_path = $this->heading_font_file->storeAs('fonts', 'heading-' . $this->heading_font_file->getClientOriginalName());
        }

        // Handle small heading font upload
        if ($this->small_heading_font_file) {
            if ($this->small_heading_font_path && Storage::exists($this->small_heading_font_path)) {
                Storage::delete($this->small_heading_font_path);
            }
            $this->small_heading_font_path = $this->small_heading_font_file->storeAs('fonts', 'small-heading-' . $this->small_heading_font_file->getClientOriginalName());
        }

        // Handle body font upload
        if ($this->body_font_file) {
            if ($this->body_font_path && Storage::exists($this->body_font_path)) {
                Storage::delete($this->body_font_path);
            }
            $this->body_font_path = $this->body_font_file->storeAs('fonts', 'body-' . $this->body_font_file->getClientOriginalName());
        }

        // Save font styles
        Setting::set('font_styles', [
            'heading' => [
                'font_family' => $this->heading_font_family,
                'font_path' => $this->heading_font_path,
                'font_size' => $this->heading_font_size,
                'font_weight' => $this->heading_font_weight,
                'font_style' => $this->heading_font_style,
                'color' => $this->heading_font_color,
            ],
            'small_heading' => [
                'font_family' => $this->small_heading_font_family,
                'font_path' => $this->small_heading_font_path,
                'font_size' => $this->small_heading_font_size,
                'font_weight' => $this->small_heading_font_weight,
                'font_style' => $this->small_heading_font_style,
                'color' => $this->small_heading_font_color,
            ],
            'body' => [
                'font_family' => $this->body_font_family,
                'font_path' => $this->body_font_path,
                'font_size' => $this->body_font_size,
                'font_weight' => $this->body_font_weight,
                'font_style' => $this->body_font_style,
                'color' => $this->body_font_color,
            ],
        ]);

        $this->success = 'Branding-Einstellungen erfolgreich gespeichert!';
        $this->reset('company_logo', 'heading_font_file', 'small_heading_font_file', 'body_font_file');
    }

    public function render()
    {
        return view('livewire.settings.branding-settings');
    }
}
