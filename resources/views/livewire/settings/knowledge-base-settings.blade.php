<div class="space-y-6">
    {{-- Success Message --}}
    @if($success)
        <div class="rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ $success }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button wire:click="$set('success', '')" type="button" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Section Toggle --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button
                wire:click="switchSection('receipt_sources')"
                class="@if($activeSection === 'receipt_sources') border-green-500 text-green-600 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Belegquellen
            </button>
            <button
                wire:click="switchSection('note_templates')"
                class="@if($activeSection === 'note_templates') border-green-500 text-green-600 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium"
            >
                Notiz-Vorlagen
            </button>
        </nav>
    </div>

    {{-- Content Section --}}
    <div>
        @if($activeSection === 'receipt_sources')
            {{-- Receipt Sources Section --}}
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Belegquellen</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Dokumentieren Sie, wo Sie Belege für bestimmte Ausgaben finden (z.B. Portale, E-Mails, APIs).
                        </p>
                    </div>
                    <button
                        wire:click="openModal('{{ \App\Models\KnowledgeBaseEntry::TYPE_RECEIPT_SOURCE }}')"
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700"
                    >
                        Neue Belegquelle
                    </button>
                </div>

                @if(empty($entries))
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Keine Belegquellen</h3>
                        <p class="mt-1 text-sm text-gray-500">Fügen Sie Ihre erste Belegquelle hinzu.</p>
                    </div>
                @else
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Titel</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">URL</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">E-Mail Absender</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kategorie</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Aktionen</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($entries as $entry)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                            {{ $entry['title'] }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if(!empty($entry['data']['url']))
                                                <a href="{{ $entry['data']['url'] }}" target="_blank" class="text-green-600 hover:text-green-900">
                                                    {{ Str::limit($entry['data']['url'], 40) }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $entry['data']['email_sender'] ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if(!empty($entry['category']))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $entry['category'] }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <button
                                                wire:click="toggleActive({{ $entry['id'] }})"
                                                type="button"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $entry['is_active'] ? 'bg-green-600' : 'bg-gray-200' }}"
                                            >
                                                <span class="translate-x-0 inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $entry['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                            </button>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button wire:click="editEntry({{ $entry['id'] }})" class="text-green-600 hover:text-green-900 mr-4">
                                                Bearbeiten
                                            </button>
                                            <button
                                                wire:click="deleteEntry({{ $entry['id'] }})"
                                                wire:confirm="Möchten Sie diesen Eintrag wirklich löschen?"
                                                class="text-red-600 hover:text-red-900"
                                            >
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
        @else
            {{-- Note Templates Section --}}
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Notiz-Vorlagen</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Beispiele für Buchhaltungs-Notizen, die Sie in der Vergangenheit verwendet haben.
                        </p>
                    </div>
                    <button
                        wire:click="openModal('{{ \App\Models\KnowledgeBaseEntry::TYPE_NOTE_TEMPLATE }}')"
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700"
                    >
                        Neue Vorlage
                    </button>
                </div>

                @if(empty($entries))
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Keine Notiz-Vorlagen</h3>
                        <p class="mt-1 text-sm text-gray-500">Fügen Sie Ihre erste Notiz-Vorlage hinzu.</p>
                    </div>
                @else
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Titel</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Beispiel-Notiz</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Verwendung</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Aktionen</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($entries as $entry)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                            {{ $entry['title'] }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <div class="max-w-md truncate">
                                                {{ $entry['data']['example_note'] ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <div class="max-w-xs truncate">
                                                {{ $entry['data']['usage_context'] ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <button
                                                wire:click="toggleActive({{ $entry['id'] }})"
                                                type="button"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $entry['is_active'] ? 'bg-green-600' : 'bg-gray-200' }}"
                                            >
                                                <span class="translate-x-0 inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $entry['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                            </button>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button wire:click="editEntry({{ $entry['id'] }})" class="text-green-600 hover:text-green-900 mr-4">
                                                Bearbeiten
                                            </button>
                                            <button
                                                wire:click="deleteEntry({{ $entry['id'] }})"
                                                wire:confirm="Möchten Sie diesen Eintrag wirklich löschen?"
                                                class="text-red-600 hover:text-red-900"
                                            >
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
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10">
                    <form wire:submit.prevent="saveEntry">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                        @if($editingEntry)
                                            Eintrag bearbeiten
                                        @else
                                            @if($entryType === \App\Models\KnowledgeBaseEntry::TYPE_RECEIPT_SOURCE)
                                                Neue Belegquelle
                                            @else
                                                Neue Notiz-Vorlage
                                            @endif
                                        @endif
                                    </h3>
                                </div>

                                {{-- Common Fields --}}
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Titel *</label>
                                    <input
                                        type="text"
                                        id="title"
                                        wire:model="title"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                        required
                                    >
                                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Beschreibung</label>
                                    <textarea
                                        id="description"
                                        wire:model="description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                    ></textarea>
                                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700">Kategorie</label>
                                    <input
                                        type="text"
                                        id="category"
                                        wire:model="category"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                        placeholder="z.B. Subscriptions, Cloud Services, etc."
                                    >
                                    @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Receipt Source Specific Fields --}}
                                @if($entryType === \App\Models\KnowledgeBaseEntry::TYPE_RECEIPT_SOURCE)
                                    <div class="border-t border-gray-200 pt-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-4">Belegquellen-Details</h4>

                                        <div class="space-y-4">
                                            <div>
                                                <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                                                <input
                                                    type="url"
                                                    id="url"
                                                    wire:model="url"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="https://..."
                                                >
                                            </div>

                                            <div>
                                                <label for="navigation" class="block text-sm font-medium text-gray-700">Navigation / Zugriff</label>
                                                <textarea
                                                    id="navigation"
                                                    wire:model="navigation"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="Wie erreiche ich die Belege? z.B. Dashboard > Rechnungen"
                                                ></textarea>
                                            </div>

                                            <div>
                                                <label for="invoiceFormat" class="block text-sm font-medium text-gray-700">Rechnungsformat</label>
                                                <input
                                                    type="text"
                                                    id="invoiceFormat"
                                                    wire:model="invoiceFormat"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="z.B. PDF Download, E-Mail Anhang"
                                                >
                                            </div>

                                            <div>
                                                <label for="emailSender" class="block text-sm font-medium text-gray-700">E-Mail Absender</label>
                                                <input
                                                    type="email"
                                                    id="emailSender"
                                                    wire:model="emailSender"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="noreply@example.com"
                                                >
                                            </div>

                                            <div>
                                                <label for="emailSubjectPattern" class="block text-sm font-medium text-gray-700">E-Mail Betreff-Muster</label>
                                                <input
                                                    type="text"
                                                    id="emailSubjectPattern"
                                                    wire:model="emailSubjectPattern"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="z.B. 'Rechnung', 'Invoice', etc."
                                                >
                                            </div>

                                            <div>
                                                <label for="bankTransactionPattern" class="block text-sm font-medium text-gray-700">Kontoauszug-Muster</label>
                                                <input
                                                    type="text"
                                                    id="bankTransactionPattern"
                                                    wire:model="bankTransactionPattern"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="Wie erscheint der Anbieter im Kontoauszug?"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Note Template Specific Fields --}}
                                @if($entryType === \App\Models\KnowledgeBaseEntry::TYPE_NOTE_TEMPLATE)
                                    <div class="border-t border-gray-200 pt-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-4">Vorlagen-Details</h4>

                                        <div class="space-y-4">
                                            <div>
                                                <label for="exampleNote" class="block text-sm font-medium text-gray-700">Beispiel-Notiz</label>
                                                <textarea
                                                    id="exampleNote"
                                                    wire:model="exampleNote"
                                                    rows="3"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm font-mono text-xs"
                                                    placeholder="Beispiel: 'Adobe Stock Abo - 10 Bilder/Monat für Kundenprojekte'"
                                                ></textarea>
                                            </div>

                                            <div>
                                                <label for="usageContext" class="block text-sm font-medium text-gray-700">Verwendungskontext</label>
                                                <textarea
                                                    id="usageContext"
                                                    wire:model="usageContext"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                                                    placeholder="Wann/wofür wird diese Art von Notiz verwendet?"
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Speichern
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Abbrechen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
