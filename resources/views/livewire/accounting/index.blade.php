<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Buchhaltung</h1>
            <p class="mt-1 text-sm text-gray-500">Verwalten Sie Ihre Banktransaktionen</p>
        </div>
        <div class="flex gap-3">
            <button
                wire:click="togglePaperlessModal"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Aus Paperless importieren
            </button>
            <button
                wire:click="toggleUploadModal"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Kontoauszug hochladen
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session()->has('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-6 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Transaktionen</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_transactions']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Validiert</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['validated']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Einnahmen</dt>
                            <dd class="text-lg font-medium text-green-600">{{ number_format($stats['total_income'], 2, ',', '.') }} €</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Ausgaben</dt>
                            <dd class="text-lg font-medium text-red-600">{{ number_format(abs($stats['total_expenses']), 2, ',', '.') }} €</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Suche</label>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Beschreibung oder Notizen durchsuchen..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Kategorie</label>
                <select
                    wire:model.live="categoryFilter"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">Alle Kategorien</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
                    wire:model.live="validatedFilter"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">Alle</option>
                    <option value="1">Validiert</option>
                    <option value="0">Nicht validiert</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Datum
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Beschreibung
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kategorie
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Betrag
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Validiert
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aktionen
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format($dateFormat) }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="text-gray-900">{{ $transaction->description }}</div>
                            @if($transaction->notes)
                                <div class="text-xs text-gray-500 mt-1">
                                    <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                    {{ $transaction->notes }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($transaction->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($transaction->category) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2, ',', '.') }} €
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input
                                type="checkbox"
                                wire:change="markAsValidated({{ $transaction->id }}, $event.target.checked)"
                                {{ $transaction->is_validated ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            >
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    onclick="document.getElementById('notes-{{ $transaction->id }}').classList.toggle('hidden')"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Notiz
                                </button>
                                @if($transaction->amount > 0 && !$transaction->invoice_id)
                                    <button
                                        wire:click="findMatches({{ $transaction->id }})"
                                        class="text-green-600 hover:text-green-900"
                                        title="Rechnung suchen"
                                    >
                                        Rechnung
                                    </button>
                                @endif
                                @if($transaction->invoice_id)
                                    <span class="text-xs text-green-700">
                                        ✓ Verknüpft
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr id="notes-{{ $transaction->id }}" class="hidden bg-gray-50">
                        <td colspan="6" class="px-6 py-4">
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model="transactions.{{ $loop->index }}.notes"
                                    wire:change="updateNote({{ $transaction->id }}, $event.target.value)"
                                    placeholder="Notiz hinzufügen..."
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    value="{{ $transaction->notes }}"
                                >
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            Keine Transaktionen gefunden.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Upload Modal -->
    @if($showUploadModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" x-data="{ open: true }" x-show="open">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     x-show="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     wire:click="toggleUploadModal">
                </div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     x-show="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop>
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                    Kontoauszug hochladen
                                </h3>
                                
                                <div class="mt-4">
                                    <div class="max-w-lg flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors"
                                         x-data="{ isDragging: false }"
                                         x-on:dragover.prevent="isDragging = true"
                                         x-on:dragleave.prevent="isDragging = false"
                                         x-on:drop.prevent="isDragging = false">
                                        
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="bank-statement-file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>PDF-Datei auswählen</span>
                                                    <input id="bank-statement-file" type="file" class="sr-only" wire:model="bankStatement" accept="application/pdf">
                                                </label>
                                                <p class="pl-1">oder per Drag & Drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PDF-Datei, maximal 10 MB
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden file input for Livewire -->
                                    <input
                                        type="file"
                                        wire:model="bankStatement"
                                        accept="application/pdf"
                                        class="hidden"
                                        id="bank-statement-hidden"
                                    >
                                    
                                    @error('bankStatement')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    
                                    <!-- Show selected file name -->
                                    @if($bankStatement)
                                        <div class="mt-2 p-2 bg-green-50 rounded-md">
                                            <p class="text-sm text-green-800">
                                                Ausgewählt: {{ $bankStatement->getClientOriginalName() }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            wire:click="uploadBankStatement"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove>Importieren</span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Wird verarbeitet...
                            </span>
                        </button>
                        <button
                            type="button"
                            wire:click="toggleUploadModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Abbrechen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Paperless Import Modal -->
    @if($showPaperlessModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="togglePaperlessModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Belege aus Paperless importieren
                                </h3>

                                <div class="mb-4 p-4 bg-gray-50 rounded-lg border">
                                    <label for="import_month" class="block text-sm font-medium text-gray-700">Monat für den Import auswählen</label>
                                    <div class="mt-1 flex items-center gap-x-3">
                                        <select id="import_month" wire:model="importMonth" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            @for ($i = 0; $i < 12; $i++)
                                                @php
                                                    $month = now()->subMonths($i);
                                                @endphp
                                                <option value="{{ $month->format('Y-m') }}">{{ $month->format('F Y') }}</option>
                                            @endfor
                                        </select>
                                        <button wire:click="loadPaperlessDocuments" wire:loading.attr="disabled" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                            Dokumente laden
                                        </button>
                                    </div>
                                </div>

                                @if($isLoadingPaperless)
                                    <div class="text-center py-8">
                                        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Lade Dokumente...</p>
                                    </div>
                                @elseif(empty($paperlessDocuments))
                                    <div class="text-center py-8">
                                        <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600">Keine neuen Belege gefunden</p>
                                        <p class="text-xs text-gray-500 mt-1">Alle Belege aus den letzten 60 Tagen wurden bereits importiert.</p>
                                    </div>
                                @else
                                    <div class="mb-4 text-sm text-gray-600">
                                        <p>{{ count($paperlessDocuments) }} neue Belege gefunden (letzte 60 Tage)</p>
                                    </div>

                                    <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50 sticky top-0">
                                                <tr>
                                                    <th class="px-4 py-3 text-left">
                                                        <input type="checkbox"
                                                               @if(count($selectedDocuments) === count($paperlessDocuments))
                                                                   checked
                                                               @endif
                                                               wire:click="$set('selectedDocuments', {{ count($selectedDocuments) === count($paperlessDocuments) ? '[]' : json_encode(array_column($paperlessDocuments, 'id')) }})"
                                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titel</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tags</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($paperlessDocuments as $doc)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3">
                                                            <input type="checkbox"
                                                                   wire:click="toggleDocumentSelection({{ $doc['id'] }})"
                                                                   @if(in_array($doc['id'], $selectedDocuments)) checked @endif
                                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $doc['title'] ?? 'Unbenannt' }}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($doc['created'])->format($dateFormat) }}</td>
                                                        <td class="px-4 py-3">
                                                            @if(isset($doc['tags']) && !empty($doc['tags']))
                                                                <div class="flex flex-wrap gap-1">
                                                                    @foreach($doc['tags'] as $tagId)
                                                                        @php
                                                                            $tagName = collect($allPaperlessTags)->firstWhere('id', $tagId)['name'] ?? '';
                                                                        @endphp
                                                                        @if($tagName)
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                                                {{ $tagName }}
                                                                            </span>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if(!empty($paperlessDocuments))
                            <button
                                type="button"
                                wire:click="importSelectedDocuments"
                                wire:loading.attr="disabled"
                                @if(empty($selectedDocuments)) disabled @endif
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove>{{ count($selectedDocuments) }} Beleg(e) importieren</span>
                                <span wire:loading>
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Wird importiert...
                                </span>
                            </button>
                        @endif
                        <button
                            type="button"
                            wire:click="togglePaperlessModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Schließen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
