<div x-data="{ activeTab: 'company' }">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Einstellungen</h1>
        <p class="mt-1 text-sm text-gray-500">Verwalten Sie Ihre Unternehmensinformationen</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button
                @click="activeTab = 'company'"
                :class="activeTab === 'company' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Unternehmen
            </button>
            <button
                @click="activeTab = 'tax'"
                :class="activeTab === 'tax' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Steuerinformationen
            </button>
            <button
                @click="activeTab = 'branding'"
                :class="activeTab === 'branding' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Branding
            </button>
            <button
                @click="activeTab = 'integrations'"
                :class="activeTab === 'integrations' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Integrationen
            </button>
            <button
                @click="activeTab = 'knowledge'"
                :class="activeTab === 'knowledge' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Wissensbasis
            </button>
        </nav>
    </div>

    <!-- Success Message -->
    @if($success)
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ $success }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Company Tab Content -->
    <div x-show="activeTab === 'company'" x-cloak>
        <form wire:submit="save" class="space-y-6">
            <!-- Company Information -->
            <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Unternehmensinformationen</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">
                            Firmenname
                        </label>
                        <input
                            wire:model="company_name"
                            type="text"
                            id="company_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('company_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address Street -->
                    <div>
                        <label for="street" class="block text-sm font-medium text-gray-700">
                            Straße und Hausnummer
                        </label>
                        <input
                            wire:model="street"
                            type="text"
                            id="street"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('street')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City and ZIP -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="zip" class="block text-sm font-medium text-gray-700">
                                Postleitzahl
                            </label>
                            <input
                                wire:model="zip"
                                type="text"
                                id="zip"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('zip')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                Stadt
                            </label>
                            <input
                                wire:model="city"
                                type="text"
                                id="city"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('city')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Bankverbindung</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- IBAN -->
                    <div>
                        <label for="iban" class="block text-sm font-medium text-gray-700">
                            IBAN
                        </label>
                        <input
                            wire:model="iban"
                            type="text"
                            id="iban"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="DE89370400440532013000"
                        >
                        @error('iban')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- BIC -->
                    <div>
                        <label for="bic" class="block text-sm font-medium text-gray-700">
                            BIC
                        </label>
                        <input
                            wire:model="bic"
                            type="text"
                            id="bic"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="COBADEFFXXX"
                        >
                        @error('bic')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Formatting -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Formatierung</h3>
                <div class="grid grid-cols-1 gap-6">
                    <!-- Date Format -->
                    <div>
                        <label for="date_format" class="block text-sm font-medium text-gray-700">
                            Datumsformat
                        </label>
                        <input
                            wire:model="date_format"
                            type="text"
                            id="date_format"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="z.B. d.m.Y"
                        >
                        @error('date_format')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            PHP-kompatibles Datumsformat. <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank" class="text-indigo-600 hover:underline">Siehe Dokumentation</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Einstellungen speichern
            </button>
        </div>
        </form>
    </div>

    <!-- Tax Tab Content -->
    <div x-show="activeTab === 'tax'" x-cloak>
        <form wire:submit="save" class="space-y-6">
            <!-- Tax Information -->
            <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Steuerinformationen</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Tax Number -->
                    <div>
                        <label for="tax_number" class="block text-sm font-medium text-gray-700">
                            Steuernummer
                        </label>
                        <input
                            wire:model="tax_number"
                            type="text"
                            id="tax_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('tax_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- EU VAT ID -->
                    <div>
                        <label for="eu_vat_id" class="block text-sm font-medium text-gray-700">
                            Umsatzsteuer-Identifikationsnummer
                        </label>
                        <input
                            wire:model="eu_vat_id"
                            type="text"
                            id="eu_vat_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="DE123456789"
                        >
                        @error('eu_vat_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- VAT Rate -->
                    <div>
                        <label for="vat_rate" class="block text-sm font-medium text-gray-700">
                            Umsatzsteuersatz (%)
                        </label>
                        <input
                            wire:model="vat_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            id="vat_rate"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('vat_rate')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">Standard-Umsatzsteuersatz für Rechnungen</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Einstellungen speichern
            </button>
        </div>
        </form>
    </div>

    <!-- Branding Tab Content -->
    <div x-show="activeTab === 'branding'" x-cloak>
        @livewire('settings.branding-settings')
    </div>

    <!-- Integrations Tab Content -->
    <div x-show="activeTab === 'integrations'" x-cloak>
        <form wire:submit="save" class="space-y-6">
            <!-- Paperless Integration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Paperless Integration</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Paperless URL -->
                        <div>
                            <label for="paperless_url" class="block text-sm font-medium text-gray-700">
                                Paperless URL
                            </label>
                            <input
                                wire:model="paperless_url"
                                type="url"
                                id="paperless_url"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="http://128.140.41.24:8000/"
                            >
                            @error('paperless_url')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Die URL Ihrer Paperless-NGX Installation.
                            </p>
                        </div>

                        <!-- Paperless API Token -->
                        <div>
                            <label for="paperless_api_token" class="block text-sm font-medium text-gray-700">
                                API Token
                            </label>
                            <input
                                wire:model="paperless_api_token"
                                type="password"
                                id="paperless_api_token"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                placeholder="••••••••••••••••"
                            >
                            @error('paperless_api_token')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Ihr Paperless API Token. Zu finden in Paperless unter Einstellungen → API Tokens.
                            </p>
                        </div>

                        <!-- Storage Path -->
                        <div>
                            <label for="paperless_storage_path" class="block text-sm font-medium text-gray-700">
                                Storage Path
                            </label>
                            <select
                                wire:model="paperless_storage_path"
                                id="paperless_storage_path"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Kein Filter (alle Pfade) --</option>
                                @foreach($availableStoragePaths as $path)
                                    <option value="{{ $path['id'] }}">{{ $path['name'] }}</option>
                                @endforeach
                            </select>
                            @error('paperless_storage_path')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Wählen Sie den Storage Path in Paperless, in dem alle Ihre Dokumente gespeichert werden.
                                Dieser Filter wird auf alle Paperless-Interaktionen angewendet (Suche, Import, Upload).
                            </p>
                            @if(empty($availableStoragePaths))
                                <p class="mt-2 text-sm text-amber-600">
                                    ⚠️ Keine Storage Paths gefunden. Stellen Sie sicher, dass Paperless korrekt konfiguriert ist.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Provider Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">AI Provider Konfiguration</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Wählen Sie Ihren primären AI-Provider für Dokumentenerkennung. Bei Bedarf können Sie einen Fallback-Provider konfigurieren.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Primary Provider -->
                        <div>
                            <label for="ai_provider" class="block text-sm font-medium text-gray-700">
                                Primärer AI-Provider
                            </label>
                            <select
                                wire:model="ai_provider"
                                id="ai_provider"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="ollama">Ollama (lokal)</option>
                                <option value="openai">OpenAI</option>
                                <option value="anthropic">Anthropic (Claude)</option>
                                <option value="openrouter">OpenRouter</option>
                            </select>
                            @error('ai_provider')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fallback Provider -->
                        <div>
                            <label for="ai_fallback_provider" class="block text-sm font-medium text-gray-700">
                                Fallback-Provider
                            </label>
                            <select
                                wire:model="ai_fallback_provider"
                                id="ai_fallback_provider"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="none">Kein Fallback</option>
                                <option value="ollama">Ollama (lokal)</option>
                                <option value="openai">OpenAI</option>
                                <option value="anthropic">Anthropic (Claude)</option>
                                <option value="openrouter">OpenRouter</option>
                            </select>
                            @error('ai_fallback_provider')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Wird verwendet, wenn der primäre Provider fehlschlägt.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OpenAI Configuration -->
            <div class="bg-white shadow rounded-lg" x-show="$wire.ai_provider === 'openai' || $wire.ai_fallback_provider === 'openai'">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">OpenAI Konfiguration</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- OpenAI API Key -->
                        <div>
                            <label for="openai_api_key" class="block text-sm font-medium text-gray-700">
                                OpenAI API Key
                            </label>
                            <input
                                wire:model="openai_api_key"
                                type="password"
                                id="openai_api_key"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="sk-..."
                            >
                            @error('openai_api_key')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Ihr OpenAI API-Schlüssel. Erhältlich unter <a href="https://platform.openai.com/api-keys" target="_blank" class="text-indigo-600 hover:text-indigo-500">platform.openai.com</a>.
                            </p>
                        </div>

                        <!-- OpenAI Model -->
                        <div>
                            <label for="openai_model" class="block text-sm font-medium text-gray-700">
                                OpenAI Model
                            </label>
                            <select
                                wire:model="openai_model"
                                id="openai_model"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="gpt-4o">GPT-4o (empfohlen)</option>
                                <option value="gpt-4o-mini">GPT-4o mini (günstiger)</option>
                                <option value="gpt-4-turbo">GPT-4 Turbo</option>
                            </select>
                            @error('openai_model')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Das zu verwendende OpenAI-Modell. GPT-4o bietet die beste Qualität für Dokumentenerkennung.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Anthropic Configuration -->
            <div class="bg-white shadow rounded-lg" x-show="$wire.ai_provider === 'anthropic' || $wire.ai_fallback_provider === 'anthropic'">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Anthropic (Claude) Konfiguration</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Anthropic API Key -->
                        <div>
                            <label for="anthropic_api_key" class="block text-sm font-medium text-gray-700">
                                Anthropic API Key
                            </label>
                            <input
                                wire:model="anthropic_api_key"
                                type="password"
                                id="anthropic_api_key"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="sk-ant-..."
                            >
                            @error('anthropic_api_key')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Ihr Anthropic API-Schlüssel. Erhältlich unter <a href="https://console.anthropic.com/" target="_blank" class="text-indigo-600 hover:text-indigo-500">console.anthropic.com</a>.
                            </p>
                        </div>

                        <!-- Anthropic Model -->
                        <div>
                            <label for="anthropic_model" class="block text-sm font-medium text-gray-700">
                                Anthropic Model
                            </label>
                            <select
                                wire:model="anthropic_model"
                                id="anthropic_model"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="claude-3-5-sonnet-20241022">Claude 3.5 Sonnet (empfohlen)</option>
                                <option value="claude-3-opus-20240229">Claude 3 Opus (höchste Qualität)</option>
                                <option value="claude-3-haiku-20240307">Claude 3 Haiku (schnell & günstig)</option>
                            </select>
                            @error('anthropic_model')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Das zu verwendende Claude-Modell. Sonnet bietet das beste Preis-Leistungs-Verhältnis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OpenRouter Configuration -->
            <div class="bg-white shadow rounded-lg" x-show="$wire.ai_provider === 'openrouter' || $wire.ai_fallback_provider === 'openrouter'">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">OpenRouter Konfiguration</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- OpenRouter API Key -->
                        <div>
                            <label for="openrouter_api_key" class="block text-sm font-medium text-gray-700">
                                OpenRouter API Key
                            </label>
                            <input
                                wire:model="openrouter_api_key"
                                type="password"
                                id="openrouter_api_key"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="sk-or-..."
                            >
                            @error('openrouter_api_key')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Ihr OpenRouter API-Schlüssel. Erhältlich unter <a href="https://openrouter.ai/keys" target="_blank" class="text-indigo-600 hover:text-indigo-500">openrouter.ai/keys</a>.
                            </p>
                        </div>

                        <!-- OpenRouter Model -->
                        <div>
                            <label for="openrouter_model" class="block text-sm font-medium text-gray-700">
                                OpenRouter Model
                            </label>
                            <input
                                wire:model="openrouter_model"
                                type="text"
                                id="openrouter_model"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="anthropic/claude-3.5-sonnet"
                            >
                            @error('openrouter_model')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Das zu verwendende Modell (z.B. anthropic/claude-3.5-sonnet, openai/gpt-4o). Siehe <a href="https://openrouter.ai/models" target="_blank" class="text-indigo-600 hover:text-indigo-500">Modelliste</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ollama Integration -->
            <div class="bg-white shadow rounded-lg" x-show="$wire.ai_provider === 'ollama'">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Ollama AI Integration (Lokal)</h3>
                        <button
                            type="button"
                            wire:click="refreshOllamaModels"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Modelle aktualisieren
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Ollama URL -->
                        <div>
                            <label for="ollama_url" class="block text-sm font-medium text-gray-700">
                                Ollama API URL
                            </label>
                            <input
                                wire:model="ollama_url"
                                type="url"
                                id="ollama_url"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="http://jens.pc.local:11434"
                            >
                            @error('ollama_url')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                Die URL Ihrer Ollama Installation (z.B. http://localhost:11434).
                            </p>
                        </div>

                        <!-- Ollama Model (Text) -->
                        <div wire:poll.2s="checkTextModelProgress">
                            <label for="ollama_model" class="block text-sm font-medium text-gray-700">
                                AI Model (Text)
                            </label>
                            <select
                                wire:model="ollama_model"
                                id="ollama_model"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Bitte wählen --</option>
                                @forelse($availableTextModels as $model)
                                    <option value="{{ $model['name'] }}">
                                        {{ $model['name'] }}
                                        @if(isset($model['size']))
                                            ({{ number_format($model['size'] / 1073741824, 1) }} GB)
                                        @endif
                                    </option>
                                @empty
                                    <option value="" disabled>Keine Text-Modelle verfügbar</option>
                                @endforelse
                            </select>
                            @error('ollama_model')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if(!$hasRecommendedTextModel)
                                <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-amber-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-amber-800 mb-1">
                                                Empfohlenes Modell fehlt: {{ \App\Livewire\Settings\Index::RECOMMENDED_TEXT_MODEL }}
                                            </h4>
                                            <p class="text-xs text-amber-700 mb-3">
                                                Dieses Modell bietet die beste Leistung für finanzielle Analysen und unterstützt "Thinking Mode" für bessere Ergebnisse.
                                            </p>
                                            <button
                                                type="button"
                                                wire:click="installRecommendedTextModel"
                                                wire:loading.attr="disabled"
                                                wire:target="installRecommendedTextModel"
                                                style="background: linear-gradient(to right, #059669, #10b981);"
                                                class="inline-flex justify-center items-center px-4 py-2 border-0 rounded-md shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <svg wire:loading.remove wire:target="installRecommendedTextModel" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                </svg>
                                                <svg wire:loading wire:target="installRecommendedTextModel" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span wire:loading.remove wire:target="installRecommendedTextModel">
                                                    Installieren
                                                </span>
                                                <span wire:loading wire:target="installRecommendedTextModel">
                                                    Installiere...
                                                </span>
                                            </button>
                                            @if($installingTextModel)
                                                <div class="mt-3">
                                                    <!-- Progress Bar -->
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                                        <div class="bg-green-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $installProgressPercent }}%"></div>
                                                    </div>
                                                    <p class="text-xs text-amber-700">
                                                        {{ $installProgress }} ({{ $installProgressPercent }}%)
                                                    </p>
                                                </div>
                                            @endif
                                            <p class="mt-2 text-xs text-amber-600">
                                                ca. 11 GB Download • 5-15 Minuten Installation
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!empty($availableTextModels))
                                <p class="mt-2 text-sm text-gray-500">
                                    Das zu verwendende Ollama Modell für Textanalyse.
                                </p>
                            @endif
                        </div>

                        <!-- Ollama Vision Model -->
                        <div wire:poll.2s="checkVisionModelProgress">
                            <label for="ollama_vision_model" class="block text-sm font-medium text-gray-700">
                                AI Model (Vision)
                            </label>
                            <select
                                wire:model="ollama_vision_model"
                                id="ollama_vision_model"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Bitte wählen --</option>
                                @forelse($availableVisionModels as $model)
                                    <option value="{{ $model['name'] }}">
                                        {{ $model['name'] }}
                                        @if(isset($model['size']))
                                            ({{ number_format($model['size'] / 1073741824, 1) }} GB)
                                        @endif
                                    </option>
                                @empty
                                    <option value="" disabled>Keine Vision-Modelle verfügbar</option>
                                @endforelse
                            </select>
                            @error('ollama_vision_model')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if(!$hasRecommendedVisionModel)
                                <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-blue-800 mb-1">
                                                Empfohlenes Modell fehlt: {{ \App\Livewire\Settings\Index::RECOMMENDED_VISION_MODEL }}
                                            </h4>
                                            <p class="text-xs text-blue-700 mb-3">
                                                Dieses Vision-Modell bietet die beste Leistung für OCR und automatische Belegerkennung mit hoher Genauigkeit.
                                            </p>
                                            <button
                                                type="button"
                                                wire:click="installRecommendedVisionModel"
                                                wire:loading.attr="disabled"
                                                wire:target="installRecommendedVisionModel"
                                                style="background: linear-gradient(to right, #2563eb, #4f46e5);"
                                                class="inline-flex justify-center items-center px-4 py-2 border-0 rounded-md shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <svg wire:loading.remove wire:target="installRecommendedVisionModel" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                </svg>
                                                <svg wire:loading wire:target="installRecommendedVisionModel" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span wire:loading.remove wire:target="installRecommendedVisionModel">
                                                    Installieren
                                                </span>
                                                <span wire:loading wire:target="installRecommendedVisionModel">
                                                    Installiere...
                                                </span>
                                            </button>
                                            @if($installingVisionModel)
                                                <div class="mt-3">
                                                    <!-- Progress Bar -->
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $installProgressPercent }}%"></div>
                                                    </div>
                                                    <p class="text-xs text-blue-700">
                                                        {{ $installProgress }} ({{ $installProgressPercent }}%)
                                                    </p>
                                                </div>
                                            @endif
                                            <p class="mt-2 text-xs text-blue-600">
                                                ca. 2 GB Download • 2-10 Minuten Installation
                                            </p>
                                            <p class="mt-2 text-xs text-blue-500">
                                                <strong>Weitere Optionen:</strong><br>
                                                • <code class="bg-white px-1 py-0.5 rounded">ollama pull llama3.2-vision</code> - Meta's Vision-Modell (7.9 GB)<br>
                                                • <code class="bg-white px-1 py-0.5 rounded">ollama pull llava</code> - Beliebtes Vision-Modell (4.5 GB)<br>
                                                • <code class="bg-white px-1 py-0.5 rounded">ollama pull qwen2-vl</code> - Starke visuelle Analyse (4-8 GB)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!empty($availableVisionModels))
                                <p class="mt-2 text-sm text-gray-500">
                                    Das zu verwendende Vision-Modell für PDF-Analyse und Belegerkennung.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Einstellungen speichern
                </button>
            </div>
        </form>
    </div>

    <!-- Knowledge Base Tab Content -->
    <div x-show="activeTab === 'knowledge'" x-cloak>
        @livewire('settings.knowledge-base-settings')
    </div>
</div>
