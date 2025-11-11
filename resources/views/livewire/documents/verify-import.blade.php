<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        @if($documentType === 'invoice')
                            Rechnung verifizieren
                        @elseif($documentType === 'quote')
                            Angebot verifizieren
                        @else
                            Dokument verifizieren
                        @endif
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Bitte überprüfen Sie die automatisch erkannten Daten und korrigieren Sie diese bei Bedarf.
                    </p>
                </div>

                <form wire:submit.prevent="confirmImport">
                    <!-- Customer Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kunde</h3>

                        @if($parsedData['existing_customer'] ?? null)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium text-green-800">Bestehender Kunde gefunden!</span>
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium text-yellow-800">Kein bestehender Kunde gefunden - wird als neuer Kunde angelegt</span>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bestehenden Kunden auswählen (optional)</label>
                                <select wire:model.live="customerId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Neuen Kunden anlegen --</option>
                                    @foreach($availableCustomers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Firmenname *</label>
                                <input type="text" wire:model="customerName" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @error('customerName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">E-Mail</label>
                                <input type="email" wire:model="customerEmail" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('customerEmail') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Steuernummer</label>
                                <input type="text" wire:model="customerTaxNumber" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Straße</label>
                                <input type="text" wire:model="customerStreet" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PLZ</label>
                                <input type="text" wire:model="customerZip" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stadt</label>
                                <input type="text" wire:model="customerCity" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Document Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            @if($documentType === 'invoice')
                                Rechnungsdetails
                            @elseif($documentType === 'quote')
                                Angebotsdetails
                            @endif
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Datum *</label>
                                <input type="date" wire:model="issueDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @error('issueDate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($documentType === 'invoice')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fälligkeitsdatum</label>
                                    <input type="date" wire:model="dueDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('dueDate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @elseif($documentType === 'quote')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gültig bis</label>
                                    <input type="date" wire:model="validUntil" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('validUntil') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Project Details (for project invoices) -->
                    @if($documentType === 'invoice')
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <input type="checkbox" wire:model.live="isProjectInvoice" id="isProjectInvoice" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <label for="isProjectInvoice" class="ml-2 text-sm font-medium text-gray-700">Projektrechnung</label>
                            </div>

                            @if($isProjectInvoice)
                                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Projektname</label>
                                        <input type="text" wire:model="projectName" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Leistungszeitraum von</label>
                                        <input type="date" wire:model="servicePeriodStart" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Leistungszeitraum bis</label>
                                        <input type="date" wire:model="servicePeriodEnd" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Leistungsort</label>
                                        <input type="text" wire:model="serviceLocation" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Financial Summary -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Finanzielle Übersicht</h3>

                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-700">Zwischensumme (netto):</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($subtotal, 2, ',', '.') }} €</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-700">MwSt. ({{ $vatRate }}%):</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($vatAmount, 2, ',', '.') }} €</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-200">
                                <span class="text-base font-semibold text-gray-900">Gesamtbetrag (brutto):</span>
                                <span class="text-base font-bold text-blue-600">{{ number_format($total, 2, ',', '.') }} €</span>
                            </div>
                        </div>

                        @if(count($items) > 0)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Positionen ({{ count($items) }}):</p>
                                <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Beschreibung</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Menge</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Preis</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Summe</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($items as $item)
                                                <tr>
                                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $item['description'] ?? '-' }}</td>
                                                    <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ $item['quantity'] ?? 1 }}</td>
                                                    <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ number_format($item['unit_price'] ?? 0, 2, ',', '.') }} €</td>
                                                    <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ number_format($item['total'] ?? 0, 2, ',', '.') }} €</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Notes -->
                    @if($notes)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notizen</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700">{{ $notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" wire:click="cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Abbrechen
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Import bestätigen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
