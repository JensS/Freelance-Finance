<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Rechnung bearbeiten</h1>
                <p class="mt-1 text-sm text-gray-500">Rechnung #{{ $invoice->invoice_number }}</p>
            </div>
            <div class="text-sm text-gray-500">
                Erstellt am: {{ $invoice->created_at ? $invoice->created_at->format($dateFormat . ' H:i') : '' }}
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Customer Selection -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Kunde</h3>

                @if(!$selectedCustomer)
                    <div class="relative">
                        <input
                            wire:model.live.debounce.300ms="customerSearch"
                            type="text"
                            placeholder="Kundenname oder E-Mail eingeben..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >

                        @if(count($searchResults) > 0)
                            <div class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 max-h-60 overflow-auto">
                                @foreach($searchResults as $result)
                                    <button
                                        type="button"
                                        wire:click="selectCustomer({{ $result['id'] }})"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                    >
                                        <div class="font-medium">{{ $result['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $result['email'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-md">
                        <div>
                            <div class="font-medium">{{ $selectedCustomer->name }}</div>
                            <div class="text-sm text-gray-500">{{ $selectedCustomer->email }}</div>
                        </div>
                        <button
                            type="button"
                            wire:click="clearCustomer"
                            class="text-red-600 hover:text-red-800"
                        >
                            Ändern
                        </button>
                    </div>
                @endif
                @error('customer_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Invoice Type and Details -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Rechnungsdetails</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Invoice Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rechnungstyp
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input
                                    wire:model.live="type"
                                    type="radio"
                                    value="general"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Allgemein</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    wire:model.live="type"
                                    type="radio"
                                    value="project"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Projekt</span>
                            </label>
                        </div>
                    </div>

                    <!-- Project Fields (shown only for project type) -->
                    @if($type === 'project')
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700">
                                Projektname <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="project_name"
                                type="text"
                                id="project_name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('project_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="service_period_start" class="block text-sm font-medium text-gray-700">
                                    Leistungszeitraum von <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="service_period_start"
                                    type="date"
                                    id="service_period_start"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('service_period_start')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="service_period_end" class="block text-sm font-medium text-gray-700">
                                    Leistungszeitraum bis <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model="service_period_end"
                                    type="date"
                                    id="service_period_end"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('service_period_end')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="service_location" class="block text-sm font-medium text-gray-700">
                                Leistungsort
                            </label>
                            <input
                                wire:model="service_location"
                                type="text"
                                id="service_location"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('service_location')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="issue_date" class="block text-sm font-medium text-gray-700">
                                Rechnungsdatum <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="issue_date"
                                type="date"
                                id="issue_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('issue_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">
                                Fälligkeitsdatum <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="due_date"
                                type="date"
                                id="due_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('due_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Positionen</h3>

                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="grid grid-cols-12 gap-4 items-start p-4 bg-gray-50 rounded-md">
                            <!-- Description -->
                            <div class="col-span-5">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Beschreibung
                                </label>
                                <input
                                    wire:model.blur="items.{{ $index }}.description"
                                    type="text"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                                @error("items.{$index}.description")
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantity -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Menge
                                </label>
                                <input
                                    wire:model.blur="items.{{ $index }}.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                                @error("items.{$index}.quantity")
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Unit -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Einheit
                                </label>
                                <select
                                    wire:model="items.{{ $index }}.unit"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                                    <option value="Std">Std</option>
                                    <option value="Tag">Tag</option>
                                    <option value="Pauschal">Pauschal</option>
                                </select>
                            </div>

                            <!-- Unit Price -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Preis (€)
                                </label>
                                <input
                                    wire:model.blur="items.{{ $index }}.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                                @error("items.{$index}.unit_price")
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Delete Button -->
                            <div class="col-span-1 flex items-end justify-center">
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                    @if(count($items) === 1) disabled @endif
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    wire:click="addItem"
                    class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Position hinzufügen
                </button>
            </div>
        </div>

        <!-- Totals and VAT -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Zusammenfassung</h3>

                <div class="space-y-4">
                    <!-- VAT Rate -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="vat_rate" class="block text-sm font-medium text-gray-700">
                                Umsatzsteuersatz (%)
                            </label>
                            <input
                                wire:model.blur="vat_rate"
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
                        </div>
                    </div>

                    <!-- Totals Display -->
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Nettobetrag:</span>
                            <span class="font-medium">{{ number_format($subtotal, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">USt ({{ $vat_rate }}%):</span>
                            <span class="font-medium">{{ number_format($vat_amount, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                            <span>Gesamtbetrag:</span>
                            <span>{{ number_format($total, 2, ',', '.') }} €</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Notizen</h3>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Interne Notizen
                    </label>
                    <textarea
                        wire:model="notes"
                        id="notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    <p class="mt-2 text-sm text-gray-500">Diese Notizen erscheinen nicht auf der Rechnung</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
            <a
                href="{{ route('invoices.index') }}"
                class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Abbrechen
            </a>
            <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Änderungen speichern
            </button>
        </div>
    </form>
</div>
