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

    public string $paperless_url = '';

    public string $paperless_api_token = '';

    // Ollama Integration
    public string $ollama_url = '';

    public string $ollama_model = '';

    public string $ollama_vision_model = '';

    public array $availableTextModels = [];

    public array $availableVisionModels = [];

    public bool $installingTextModel = false;

    public bool $installingVisionModel = false;

    // AI Provider Configuration
    public string $ai_provider = 'ollama'; // ollama, openai, anthropic, openrouter

    public string $ai_fallback_provider = 'none'; // none, openai, anthropic, openrouter

    public string $openai_api_key = '';

    public string $openai_model = 'gpt-4o'; // gpt-4o, gpt-4-turbo, gpt-4o-mini

    public string $anthropic_api_key = '';

    public string $anthropic_model = 'claude-3-5-sonnet-20241022'; // claude-3-5-sonnet, claude-3-opus, claude-3-haiku

    public string $openrouter_api_key = '';

    public string $openrouter_model = 'anthropic/claude-3.5-sonnet';

    // Current tab
    public string $currentTab = 'company';

    public string $success = '';

    public function mount()
    {
        $this->loadStoragePaths();
        $this->loadOllamaModels();
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

    public function loadOllamaModels()
    {
        try {
            $ollamaService = app(\App\Services\OllamaService::class);
            $models = $ollamaService->getAvailableModels();

            $this->availableTextModels = $models['text'] ?? [];
            $this->availableVisionModels = $models['vision'] ?? [];
        } catch (\Exception $e) {
            \Log::warning('Failed to load Ollama models', ['error' => $e->getMessage()]);
            $this->availableTextModels = [];
            $this->availableVisionModels = [];
        }
    }

    public function refreshOllamaModels()
    {
        $this->loadOllamaModels();
        $this->success = 'Verfügbare Modelle wurden aktualisiert!';
    }

    public function installRecommendedTextModel()
    {
        $this->installingTextModel = true;
        $this->success = '';

        try {
            $ollamaService = app(\App\Services\OllamaService::class);
            $result = $ollamaService->installModel('gpt-oss:20b');

            if ($result['success']) {
                $this->success = 'Text-Modell gpt-oss:20b wird heruntergeladen. Dies kann einige Minuten dauern...';

                // Refresh models after a short delay
                sleep(3);
                $this->loadOllamaModels();

                // Auto-select the newly installed model
                if (! empty($this->availableTextModels)) {
                    foreach ($this->availableTextModels as $model) {
                        if (str_contains($model['name'], 'gpt-oss')) {
                            $this->ollama_model = $model['name'];
                            break;
                        }
                    }
                }

                $this->success = 'Text-Modell gpt-oss:20b wurde erfolgreich installiert!';
            } else {
                $this->success = 'Fehler beim Installieren: '.$result['error'];
            }
        } catch (\Exception $e) {
            $this->success = 'Fehler beim Installieren: '.$e->getMessage();
        } finally {
            $this->installingTextModel = false;
        }
    }

    public function installRecommendedVisionModel()
    {
        $this->installingVisionModel = true;
        $this->success = '';

        try {
            $ollamaService = app(\App\Services\OllamaService::class);
            $result = $ollamaService->installModel('granite-3.2-vision');

            if ($result['success']) {
                $this->success = 'Vision-Modell granite-3.2-vision wird heruntergeladen. Dies kann einige Minuten dauern...';

                // Refresh models after a short delay
                sleep(3);
                $this->loadOllamaModels();

                // Auto-select the newly installed model
                if (! empty($this->availableVisionModels)) {
                    foreach ($this->availableVisionModels as $model) {
                        if (str_contains($model['name'], 'granite-3.2-vision')) {
                            $this->ollama_vision_model = $model['name'];
                            break;
                        }
                    }
                }

                $this->success = 'Vision-Modell granite-3.2-vision wurde erfolgreich installiert!';
            } else {
                $this->success = 'Fehler beim Installieren: '.$result['error'];
            }
        } catch (\Exception $e) {
            $this->success = 'Fehler beim Installieren: '.$e->getMessage();
        } finally {
            $this->installingVisionModel = false;
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
        $this->paperless_url = Setting::get('paperless_url', config('services.paperless.url', ''));
        $this->paperless_api_token = Setting::get('paperless_api_token', config('services.paperless.token', ''));

        // Load Ollama integration
        $this->ollama_url = Setting::get('ollama_url', config('services.ollama.url', 'http://localhost:11434'));
        $this->ollama_model = Setting::get('ollama_model', config('services.ollama.model', 'llama3.2'));
        $this->ollama_vision_model = Setting::get('ollama_vision_model', 'llama3.2-vision');

        // Load AI provider configuration
        $this->ai_provider = Setting::get('ai_provider', 'ollama');
        $this->ai_fallback_provider = Setting::get('ai_fallback_provider', 'none');
        $this->openai_api_key = Setting::get('openai_api_key', '');
        $this->openai_model = Setting::get('openai_model', 'gpt-4o');
        $this->anthropic_api_key = Setting::get('anthropic_api_key', '');
        $this->anthropic_model = Setting::get('anthropic_model', 'claude-3-5-sonnet-20241022');
        $this->openrouter_api_key = Setting::get('openrouter_api_key', '');
        $this->openrouter_model = Setting::get('openrouter_model', 'anthropic/claude-3.5-sonnet');
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

            'paperless_url' => 'nullable|url|max:255',

            'paperless_api_token' => 'nullable|string|max:255',

            'ollama_url' => 'nullable|url|max:255',

            'ollama_model' => 'nullable|string|max:100',

            'ollama_vision_model' => 'nullable|string|max:100',

            'ai_provider' => 'required|in:ollama,openai,anthropic,openrouter',

            'ai_fallback_provider' => 'required|in:none,openai,anthropic,openrouter',

            'openai_api_key' => 'nullable|string|max:255',

            'openai_model' => 'nullable|string|max:100',

            'anthropic_api_key' => 'nullable|string|max:255',

            'anthropic_model' => 'nullable|string|max:100',

            'openrouter_api_key' => 'nullable|string|max:255',

            'openrouter_model' => 'nullable|string|max:100',

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
        Setting::set('paperless_url', $this->paperless_url);
        Setting::set('paperless_api_token', $this->paperless_api_token);

        // Save Ollama integration
        Setting::set('ollama_url', $this->ollama_url);
        Setting::set('ollama_model', $this->ollama_model);
        Setting::set('ollama_vision_model', $this->ollama_vision_model);

        // Save AI provider configuration
        Setting::set('ai_provider', $this->ai_provider);
        Setting::set('ai_fallback_provider', $this->ai_fallback_provider);
        Setting::set('openai_api_key', $this->openai_api_key);
        Setting::set('openai_model', $this->openai_model);
        Setting::set('anthropic_api_key', $this->anthropic_api_key);
        Setting::set('anthropic_model', $this->anthropic_model);
        Setting::set('openrouter_api_key', $this->openrouter_api_key);
        Setting::set('openrouter_model', $this->openrouter_model);

        $this->success = 'Einstellungen erfolgreich gespeichert!';

    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
