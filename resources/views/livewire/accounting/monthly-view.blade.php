<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Success/Error Messages --}}
        @if($success)
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-medium text-green-800">{{ $success }}</p>
                    <button wire:click="$set('success', '')" class="ml-auto">
                        <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if($error)
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-medium text-red-800">{{ $error }}</p>
                    <button wire:click="$set('error', '')" class="ml-auto">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Month Selector --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Monatliche Buchhaltung</h2>

                <div class="flex items-center space-x-4" x-data="{
                    selectedYear: {{ $selectedYear }},
                    selectedMonth: {{ $selectedMonth }},
                    navigate() {
                        window.location.href = '/accounting/month/' + this.selectedYear + '/' + this.selectedMonth;
                    }
                }">
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Jahr</label>
                        <select x-model="selectedYear" @change="navigate()" id="year" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @for($year = now()->year + 1; $year >= 2020; $year--)
                                <option value="{{ $year }}" @if($year == $selectedYear) selected @endif>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700">Monat</label>
                        <select x-model="selectedMonth" @change="navigate()" id="month" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @if($m == $selectedMonth) selected @endif>{{ \Carbon\Carbon::create(null, $m, 1)->locale('de')->monthName }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex gap-4">
                <div class="flex-1 bg-gray-50 px-4 py-4 rounded-lg">
                    <div class="text-xs font-medium text-gray-500 truncate">Transaktionen</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $transactionCount }}</div>
                </div>
                <div class="flex-1 bg-gray-50 px-4 py-4 rounded-lg">
                    <div class="text-xs font-medium text-gray-500 truncate">Rechnungen (Eingang)</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $documentCount }}</div>
                </div>
                <div class="flex-1 bg-gray-50 px-4 py-4 rounded-lg">
                    <div class="text-xs font-medium text-gray-500 truncate">Rechnungen (Ausgang)</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $invoiceCount }}</div>
                </div>
                <div class="flex-1 bg-gray-50 px-4 py-4 rounded-lg">
                    <div class="text-xs font-medium text-gray-500 truncate">Verknüpft</div>
                    <div class="mt-1 text-2xl font-semibold text-green-600">{{ $linkedCount }} / {{ $transactionCount }}</div>
                </div>
            </div>
        </div>

        {{-- Bank Statement Upload --}}
        @if(!$hasBankStatement)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
                <div class="flex">
                    <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-amber-800">Kein Kontoauszug vorhanden</h3>
                        <p class="mt-2 text-sm text-amber-700">Für {{ $monthName }} {{ $selectedYear }} wurde noch kein Kontoauszug hochgeladen.</p>

                        <div class="mt-4">
                            <input type="file" wire:model="bankStatementFile" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('bankStatementFile') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        @if($bankStatementFile)
                            <div class="mt-4">
                                <button wire:click="uploadBankStatement" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span wire:loading.remove wire:target="uploadBankStatement">Kontoauszug hochladen</span>
                                    <span wire:loading wire:target="uploadBankStatement">Wird hochgeladen...</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Bank Transactions --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Banktransaktionen (aus Kontoauszug)</h3>
                @if(!empty($transactions))
                    <button wire:click="openAiMatchingModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Connect with AI
                    </button>
                @endif
            </div>

            @if(empty($transactions))
                <p class="text-gray-500 text-center py-8">Keine Transaktionen für diesen Monat.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Datum</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Korrespondent</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Betrag</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Typ</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Verknüpftes Dokument</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Aktionen</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($transactions as $transaction)
                                <tr class="@if($transaction['paperless_document_id']) bg-green-50 @endif">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($transaction['transaction_date'])->format('d.m.Y') }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-900">
                                        {{ $transaction['correspondent'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                        {{ number_format($transaction['amount'], 2, ',', '.') }} €
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 @if(str_contains($transaction['type'], 'Einkommen')) bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                                            {{ $transaction['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        @if($transaction['paperless_document_id'])
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="text-gray-900">{{ Str::limit($transaction['paperless_document_title'], 40) }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">Nicht verknüpft</span>
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        @if($transaction['paperless_document_id'])
                                            <button wire:click="unlinkDocument({{ $transaction['id'] }})" class="text-red-600 hover:text-red-900 mr-4">
                                                Trennen
                                            </button>
                                        @else
                                            <button wire:click="openLinkingModal({{ $transaction['id'] }})" class="text-indigo-600 hover:text-indigo-900 mr-4">
                                                Verknüpfen
                                            </button>
                                        @endif
                                        <button wire:click="deleteTransaction({{ $transaction['id'] }})" wire:confirm="Wirklich löschen?" class="text-red-600 hover:text-red-900">
                                            Löschen
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Paperless Documents --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Eingangsrechnungen (Paperless)</h3>

            @if(empty($paperlessDocuments))
                <p class="text-gray-500 text-center py-8">Keine Dokumente für diesen Monat gefunden.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Titel</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Korrespondent</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Datum</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($paperlessDocuments as $doc)
                                <tr class="@if($doc['is_linked']) bg-green-50 @endif">
                                    <td class="py-4 pl-4 pr-3 text-sm text-gray-900">
                                        {{ $doc['title'] }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        {{ $doc['correspondent'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($doc['created'])->format('d.m.Y') }}
                                    </td>
                                    <td class="px-3 py-4 text-sm">
                                        @if($doc['is_linked'])
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                Verknüpft
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                Nicht verknüpft
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Outgoing Invoices --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ausgangsrechnungen</h3>

            @if(empty($invoices))
                <p class="text-gray-500 text-center py-8">Keine Rechnungen für diesen Monat.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Rechnungsnummer</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kunde</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Datum</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Betrag</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Typ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                        {{ $invoice['invoice_number'] }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-900">
                                        {{ $invoice['customer_name'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('d.m.Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                        {{ number_format($invoice['total_gross'], 2, ',', '.') }} €
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $invoice['type'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Linking Modal --}}
    @if($showLinkingModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeLinkingModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                    {{-- Loading Overlay --}}
                    @if($linkingInProgress)
                        <div class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-10 rounded-lg">
                            <div class="text-center">
                                <svg class="animate-spin h-10 w-10 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="mt-3 text-sm font-medium text-gray-900">Wird verknüpft...</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="mt-3">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Dokument verknüpfen
                            </h3>

                            {{-- Use the PaperlessDocumentLinker component --}}
                            @livewire('components.paperless-document-linker', [
                                'contextData' => [
                                    'correspondent' => $linkingTransaction['correspondent'] ?? '',
                                    'amount' => $linkingTransaction['amount'] ?? 0,
                                    'date' => $linkingTransaction['transaction_date'] ?? null,
                                ]
                            ], key('linker-' . ($linkingTransaction['id'] ?? 'new')))
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button wire:click="linkDocument"
                                type="button"
                                wire:loading.attr="disabled"
                                wire:target="linkDocument"
                                @if(!$selectedDocumentId) disabled @endif
                                class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="linkDocument">Verknüpfen</span>
                            <span wire:loading wire:target="linkDocument" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Wird verknüpft...
                            </span>
                        </button>
                        <button wire:click="closeLinkingModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Abbrechen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- AI Matching Modal --}}
    @if($showAiMatchingModal && $aiMatchingTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" x-data="{ selectedDocId: @entangle('aiSelectedDocumentId') }">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAiMatchingModal"></div>

                <div class="relative bg-white rounded-lg shadow-xl transform transition-all w-full max-w-7xl max-h-[90vh] flex flex-col">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                AI-gestützte Dokumentenzuordnung
                            </h3>
                            <button wire:click="closeAiMatchingModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-hidden" style="display: flex;">
                        {{-- Left side: Transaction details and suggestions --}}
                        <div style="width: 40%; flex-shrink: 0;" class="p-6 border-r border-gray-200 overflow-y-auto">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Transaktion</h4>

                            {{-- Compact 2-row grid layout for transaction details --}}
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">Datum:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ \Carbon\Carbon::parse($aiMatchingTransaction['transaction_date'])->format('d.m.Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">Betrag:</span>
                                        <span class="text-sm font-semibold text-gray-900 ml-1">{{ number_format($aiMatchingTransaction['amount'], 2, ',', '.') }} €</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-xs font-medium text-gray-500">Korrespondent:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $aiMatchingTransaction['correspondent'] }}</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-xs font-medium text-gray-500">Typ:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $aiMatchingTransaction['type'] }}</span>
                                    </div>
                                    @if($aiMatchingTransaction['title'])
                                        <div class="col-span-2">
                                            <span class="text-xs font-medium text-gray-500">Beschreibung:</span>
                                            <span class="text-sm text-gray-900 ml-1">{{ $aiMatchingTransaction['title'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Suche verfeinern</h4>
                                <div class="flex gap-2">
                                    <input type="text" wire:model="searchKeyword" placeholder="Suchbegriff..."
                                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <button wire:click="getSuggestedDocuments"
                                            class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Suchen
                                    </button>
                                </div>
                            </div>

                            {{-- AI Suggestions section with auto-load on init --}}
                            <div wire:init="getSuggestedDocuments">
                                @if($aiProcessing)
                                    <div class="mt-6 text-center py-8">
                                        <svg class="animate-spin h-10 w-10 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="mt-3 text-sm font-medium text-gray-700">AI analysiert Dokumente...</p>
                                        <p class="mt-1 text-xs text-gray-500">Suche nach passenden Rechnungen</p>
                                        <p class="mt-3 text-xs text-orange-600 font-medium">
                                            <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Du kannst jederzeit "Überspringen" oder "Abbrechen" klicken
                                        </p>
                                    </div>
                                @endif

                                @if(!$aiProcessing && empty($aiSuggestedDocuments))
                                    <div class="mt-6 text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Keine passenden Dokumente gefunden</p>
                                        <p class="text-xs text-gray-400 mt-1">Versuche eine Suche mit Stichworten</p>
                                    </div>
                                @endif

                                @if(!$aiProcessing && !empty($aiSuggestedDocuments))
                                    <div class="mt-6">
                                        <h4 class="text-sm font-medium text-gray-900 mb-3">AI-Vorschläge ({{ count($aiSuggestedDocuments) }})</h4>
                                        <div class="space-y-2 max-h-96 overflow-y-auto">
                                            @foreach($aiSuggestedDocuments as $index => $doc)
                                                <button wire:click="$set('aiSelectedDocumentId', {{ $doc['id'] }})"
                                                        class="w-full text-left p-3 rounded-md border transition-colors {{ $aiSelectedDocumentId == $doc['id'] ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                                                    @if($index === 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mb-1">
                                                            Beste Übereinstimmung
                                                        </span>
                                                    @endif
                                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $doc['title'] }}</div>
                                                    <div class="text-xs text-gray-500 mt-1">{{ $doc['correspondent_name'] ?? 'Unbekannt' }}</div>
                                                    <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($doc['created'])->format('d.m.Y') }}</div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right side: PDF preview (60%) --}}
                        <div style="width: 60%; flex-shrink: 0;" class="flex flex-col">
                            @if($aiSelectedDocumentId)
                                <div class="flex-1 p-4 bg-gray-50">
                                    <iframe src="{{ route('paperless.preview', ['documentId' => $aiSelectedDocumentId]) }}"
                                            class="w-full h-full rounded-lg border border-gray-300 bg-white"></iframe>
                                </div>
                            @else
                                <div class="flex-1 flex items-center justify-center bg-gray-50">
                                    <div class="text-center">
                                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="mt-4 text-sm text-gray-500">Wähle ein Dokument zur Vorschau</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Loading Overlay --}}
                    @if($linkingInProgress)
                        <div class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-10">
                            <div class="text-center">
                                <svg class="animate-spin h-12 w-12 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="mt-4 text-base font-medium text-gray-900">Dokument wird verknüpft...</p>
                                <p class="mt-1 text-sm text-gray-500">Lade nächste Transaktion</p>
                            </div>
                        </div>
                    @endif

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <button wire:click="skipAiTransaction" type="button"
                                @if($linkingInProgress) disabled @endif
                                class="inline-flex items-center px-4 py-2 text-sm font-medium {{ $aiProcessing ? 'text-orange-600 hover:text-orange-700' : 'text-gray-700 hover:text-gray-900' }} disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($aiProcessing)
                                    {{-- X icon during processing to indicate cancellation --}}
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                @endif
                            </svg>
                            {{ $aiProcessing ? 'Abbrechen & Überspringen' : 'Überspringen' }}
                        </button>
                        <div class="flex gap-3">
                            <button wire:click="closeAiMatchingModal" type="button"
                                    @if($linkingInProgress) disabled @endif
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium {{ $aiProcessing ? 'text-orange-600 border-orange-300 bg-orange-50 hover:bg-orange-100' : 'text-gray-700 bg-white hover:bg-gray-50' }} disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ $aiProcessing ? 'Analyse stoppen' : 'Abbrechen' }}
                            </button>
                            <button wire:click="linkAiDocument"
                                    x-bind:disabled="!selectedDocId || {{ $linkingInProgress ? 'true' : 'false' }}"
                                    type="button"
                                    wire:loading.attr="disabled"
                                    wire:target="linkAiDocument"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="linkAiDocument">Verknüpfen</span>
                                <span wire:loading wire:target="linkAiDocument" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Wird verknüpft...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
