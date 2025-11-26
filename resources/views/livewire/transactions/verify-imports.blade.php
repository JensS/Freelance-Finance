<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Transaktionen überprüfen</h1>
        <p class="mt-1 text-sm text-gray-500">
            Überprüfen und validieren Sie importierte Transaktionen bevor sie in der Buchhaltung verwendet werden.
        </p>
    </div>

    <!-- Progress indicator -->
    @if($unvalidatedCount > 0)
        <div class="mb-6 rounded-lg bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-800">
                        <strong>{{ $unvalidatedCount }}</strong> {{ $unvalidatedCount === 1 ? 'Transaktion' : 'Transaktionen' }} zu überprüfen
                    </p>
                </div>
            </div>
        </div>
    @endif

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

    <!-- AI Error Message -->
    @if($aiError)
        <div class="mb-6 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ $aiError }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($currentTransaction)
        <!-- Two Column Layout: Form + PDF Viewer -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Verification Form -->
            <div class="lg:col-span-1">
                <form wire:submit="approve" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                Transaktionsdetails
                                <span class="text-sm font-normal text-gray-500">(ID: {{ $transaction_id }})</span>
                            </h3>

                            <div class="grid grid-cols-1 gap-6">
                                <!-- Transaction Date (Read-only) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Datum
                                    </label>
                                    <input
                                        type="text"
                                        value="{{ $transaction_date }}"
                                        disabled
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                                    >
                                </div>

                                <!-- Correspondent with Autocomplete -->
                                <div x-data="{
                                    showSuggestions: false,
                                    suggestions: @js($correspondentSuggestions),
                                    get filteredSuggestions() {
                                        const correspondent = $wire.correspondent || '';
                                        if (!correspondent || correspondent.length < 2) {
                                            return [];
                                        }
                                        const query = correspondent.toLowerCase();
                                        return this.suggestions.filter(s =>
                                            s.toLowerCase().includes(query)
                                        ).slice(0, 10);
                                    },
                                    selectSuggestion(suggestion) {
                                        $wire.correspondent = suggestion;
                                        this.showSuggestions = false;
                                    }
                                }"
                                class="relative"
                                @click.away="showSuggestions = false">
                                    <label for="correspondent" class="block text-sm font-medium text-gray-700">
                                        Korrespondent / Händler
                                    </label>
                                    <input
                                        wire:model.live="correspondent"
                                        type="text"
                                        id="correspondent"
                                        @focus="showSuggestions = true"
                                        @input="showSuggestions = true"
                                        autocomplete="off"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >

                                    <!-- Autocomplete Dropdown -->
                                    <div x-show="showSuggestions && filteredSuggestions.length > 0"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        <template x-for="suggestion in filteredSuggestions" :key="suggestion">
                                            <div @click="selectSuggestion(suggestion)"
                                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 hover:text-indigo-900">
                                                <span class="block truncate" x-text="suggestion"></span>
                                            </div>
                                        </template>
                                    </div>

                                    @error('correspondent')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Title -->
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">
                                        Titel
                                    </label>
                                    <input
                                        wire:model="title"
                                        type="text"
                                        id="title"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Beschreibung
                                    </label>
                                    <textarea
                                        wire:model="description"
                                        id="description"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>
                                </div>

                                <!-- Transaction Type -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">
                                        Transaktionstyp <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        wire:model.live="type"
                                        id="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">-- Bitte wählen --</option>
                                        @foreach($transactionTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Amounts Section -->
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="text-base font-medium text-gray-900 mb-4">Beträge</h4>

                                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                        <!-- Gross Amount (Calculated) -->
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                                Bruttobetrag (berechnet) <span class="text-red-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <input
                                                    wire:model="amount"
                                                    type="number"
                                                    step="0.01"
                                                    id="amount"
                                                    readonly
                                                    class="block w-full rounded-md border-gray-300 bg-gray-50 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-gray-500 sm:text-sm">EUR</span>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Wird automatisch aus Nettobetrag + MwSt. berechnet
                                            </p>
                                            @error('amount')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- VAT Rate -->
                                        <div>
                                            <label for="vat_rate" class="block text-sm font-medium text-gray-700">
                                                MwSt.-Satz <span class="text-red-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <input
                                                    wire:model.live="vat_rate"
                                                    type="number"
                                                    step="0.01"
                                                    id="vat_rate"
                                                    class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-gray-500 sm:text-sm">%</span>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Wird automatisch aus Transaktionstyp ermittelt, kann angepasst werden
                                            </p>
                                            @error('vat_rate')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Net Amount -->
                                        <div>
                                            <label for="net_amount" class="block text-sm font-medium text-gray-700">
                                                Nettobetrag <span class="text-red-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <input
                                                    wire:model.live="net_amount"
                                                    type="number"
                                                    step="0.01"
                                                    id="net_amount"
                                                    class="block w-full rounded-md border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-gray-500 sm:text-sm">EUR</span>
                                                </div>
                                            </div>
                                            @error('net_amount')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- VAT Amount -->
                                        <div>
                                            <label for="vat_amount" class="block text-sm font-medium text-gray-700">
                                                MwSt.-Betrag <span class="text-red-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <input
                                                    wire:model="vat_amount"
                                                    type="number"
                                                    step="0.01"
                                                    id="vat_amount"
                                                    readonly
                                                    class="block w-full rounded-md border-gray-300 bg-gray-50 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-gray-500 sm:text-sm">EUR</span>
                                                </div>
                                            </div>
                                            @error('vat_amount')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <p class="mt-3 text-xs text-gray-500">
                                        <strong>Hinweis:</strong> Passen Sie den Nettobetrag an - der Bruttobetrag wird automatisch berechnet.
                                        Der MwSt.-Satz wird aus dem Transaktionstyp ermittelt und kann bei Bedarf angepasst werden.
                                    </p>
                                </div>

                                <!-- Bewirtung Section (only shown when is_bewirtung is true) -->
                                @if($is_bewirtung || $type === 'Bewirtung')
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="bg-amber-50 border border-amber-200 rounded-md p-4 mb-4">
                                            <div class="flex">
                                                <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-amber-800">Bewirtungsbeleg</h3>
                                                    <div class="mt-1 text-xs text-amber-700">
                                                        Für Bewirtungsbelege sind laut Finanzamt folgende Angaben erforderlich:
                                                        <ul class="list-disc ml-5 mt-1">
                                                            <li>Namen der bewirteten Personen</li>
                                                            <li>Betrieblicher Anlass (möglichst genau, allgemeine Angaben wie "Arbeitsgespräch" genügen nicht)</li>
                                                            <li>Ort der Bewirtung (Restaurant/Lokal)</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="text-base font-medium text-gray-900 mb-4">Bewirtungsdetails</h4>

                                        <div class="grid grid-cols-1 gap-6">
                                            <!-- Bewirtete Person -->
                                            <div>
                                                <label for="bewirtete_person" class="block text-sm font-medium text-gray-700">
                                                    Bewirtete Person(en)
                                                </label>
                                                <input
                                                    wire:model="bewirtete_person"
                                                    type="text"
                                                    id="bewirtete_person"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="z.B. Max Mustermann, Geschäftsführer Firma XY"
                                                >
                                                @error('bewirtete_person')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Anlass -->
                                            <div>
                                                <label for="anlass" class="block text-sm font-medium text-gray-700">
                                                    Betrieblicher Anlass <span class="text-red-500">*</span>
                                                </label>
                                                <textarea
                                                    wire:model="anlass"
                                                    id="anlass"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="Seien Sie spezifisch! Beispiel: 'Projektbesprechung Website-Redesign für Kunde ABC' statt nur 'Arbeitsgespräch'"
                                                ></textarea>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Wichtig: Allgemeine Angaben wie "Arbeitsgespräch" sind nicht ausreichend. Beschreiben Sie den konkreten Anlass.
                                                </p>
                                                @error('anlass')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Ort -->
                                            <div>
                                                <label for="ort" class="block text-sm font-medium text-gray-700">
                                                    Ort
                                                </label>
                                                <input
                                                    wire:model="ort"
                                                    type="text"
                                                    id="ort"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="z.B. Restaurant Maximilians, Berlin"
                                                >
                                                @error('ort')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between">
                        <button
                            type="button"
                            wire:click="skip"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Überspringen
                        </button>

                        <button
                            type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Validieren & Nächste
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: PDF Viewer -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg sticky top-6">
                    <div class="px-4 py-5 sm:p-6">
                        @if($paperlessDocumentUrl)
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Beleg
                                        @if($paperlessDocumentTitle)
                                            <span class="text-sm font-normal text-gray-500">({{ $paperlessDocumentTitle }})</span>
                                        @endif
                                    </h3>
                                    <a
                                        href="{{ $paperlessDocumentUrl }}"
                                        target="_blank"
                                        class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        PDF öffnen
                                    </a>
                                </div>

                                <!-- AI Auto-fill Button -->
                                <button
                                    type="button"
                                    wire:click="autoFillFromAI"
                                    wire:loading.attr="disabled"
                                    wire:target="autoFillFromAI"
                                    style="background: linear-gradient(to right, #7c3aed, #4f46e5);"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border-0 rounded-md shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    <svg wire:loading.remove wire:target="autoFillFromAI" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <svg wire:loading wire:target="autoFillFromAI" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="autoFillFromAI">
                                        AI-Extraktion: Felder automatisch ausfüllen
                                    </span>
                                    <span wire:loading wire:target="autoFillFromAI">
                                        Extrahiere Daten...
                                    </span>
                                </button>

                                <p class="mt-2 text-xs text-gray-500 text-center">
                                    Die AI analysiert das Dokument und füllt die Felder automatisch aus.
                                </p>
                            </div>
                            <div class="border border-gray-200 rounded-lg overflow-hidden" style="height: calc(100vh - 250px); min-height: 600px;">
                                <iframe
                                    wire:key="pdf-viewer-{{ $transaction_id }}-{{ $paperlessDocumentId }}"
                                    src="{{ $paperlessDocumentUrl }}"
                                    style="width: 100%; height: 100%;"
                                    frameborder="0"
                                    title="Paperless Dokument"
                                ></iframe>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Kein Beleg verknüpft</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Diese Transaktion hat keinen verknüpften Paperless-Beleg.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No transactions to verify -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Keine Transaktionen zu überprüfen</h3>
            <p class="mt-1 text-sm text-gray-500">
                Alle importierten Transaktionen wurden bereits validiert.
            </p>
        </div>
    @endif
</div>
