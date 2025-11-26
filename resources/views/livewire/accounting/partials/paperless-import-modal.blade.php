<!-- Paperless Import Modal -->
@if($showPaperlessModal)
    <div class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 z-40 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="togglePaperlessModal"></div>

            <div class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
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
                                    <button wire:click="loadPaperlessDocuments" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="loadPaperlessDocuments">Dokumente laden</span>
                                        <span wire:loading wire:target="loadPaperlessDocuments" class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Lädt...
                                        </span>
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
