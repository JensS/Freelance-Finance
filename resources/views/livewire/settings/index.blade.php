<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Einstellungen</h1>
        <p class="mt-1 text-sm text-gray-500">Verwalten Sie Ihre Unternehmensinformationen</p>
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

        <!-- Branding & Design -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Branding & Design</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Company Logo -->
                    <div>
                        <label for="company_logo" class="block text-sm font-medium text-gray-700">
                            Firmenlogo
                        </label>
                        <div class="mt-1 flex items-center">
                            @if ($company_logo_path)
                                <div class="mr-4 flex-shrink-0">
                                    <img src="{{ Storage::url($company_logo_path) }}" alt="Firmenlogo" class="h-12 w-auto">
                                </div>
                            @endif
                            <input
                                wire:model="company_logo"
                                type="file"
                                id="company_logo"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            >
                        </div>
                        @error('company_logo')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="company_logo" class="mt-2 text-sm text-gray-500">Wird hochgeladen...</div>
                    </div>

                    <!-- PDF Font Family -->
                    <div>
                        <label for="pdf_font_family" class="block text-sm font-medium text-gray-700">
                            PDF Schriftart Name
                        </label>
                        <input
                            wire:model="pdf_font_family"
                            type="text"
                            id="pdf_font_family"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="z.B. Fira Sans"
                        >
                        @error('pdf_font_family')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">Der Name der Schriftart, wie er im CSS verwendet wird.</p>
                    </div>

                    <!-- PDF Font File -->
                    <div>
                        <label for="pdf_font" class="block text-sm font-medium text-gray-700">
                            PDF Schriftart-Datei (.ttf, .otf)
                        </label>
                        <input
                            wire:model="pdf_font"
                            type="file"
                            id="pdf_font"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        >
                        @if($pdf_font_path)
                            <p class="mt-2 text-sm text-gray-500">Aktuell: {{ $pdf_font_path }}</p>
                        @endif
                        @error('pdf_font')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="pdf_font" class="mt-2 text-sm text-gray-500">Wird hochgeladen...</div>
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
