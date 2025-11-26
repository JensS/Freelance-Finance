<div class="paperless-document-linker" x-data="{ showDropdown: @entangle('showResults') }">
    {{-- Context Information (optional) --}}
    @if($contextData)
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-xs font-medium text-gray-500 mb-1">Kontext:</p>
            @if(isset($contextData['correspondent']))
                <p class="text-sm text-gray-900">
                    <span class="font-medium">Korrespondent:</span> {{ $contextData['correspondent'] }}
                </p>
            @endif
            @if(isset($contextData['amount']))
                <p class="text-sm text-gray-900">
                    <span class="font-medium">Betrag:</span> {{ number_format($contextData['amount'], 2, ',', '.') }} €
                </p>
            @endif
            @if(isset($contextData['date']))
                <p class="text-sm text-gray-900">
                    <span class="font-medium">Datum:</span> {{ \Carbon\Carbon::parse($contextData['date'])->format('d.m.Y') }}
                </p>
            @endif
        </div>
    @endif

    {{-- Search Input --}}
    <div class="relative">
        <label for="paperless-search" class="block text-sm font-medium text-gray-700 mb-2">
            Dokument suchen
        </label>

        <div class="relative">
            <input
                type="text"
                id="paperless-search"
                wire:model.live.debounce.{{ $searchDebounceMs }}ms="searchQuery"
                wire:keydown.enter="search"
                placeholder="Name, Datum oder Inhalt durchsuchen..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10 pr-10"
                @click="showDropdown = true"
            >

            {{-- Search Icon --}}
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Loading Spinner --}}
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center" wire:loading wire:target="searchQuery,search">
                <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            {{-- Clear Button --}}
            @if($searchQuery)
                <button
                    type="button"
                    wire:click="clearSearch"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                    wire:loading.class="hidden"
                    wire:target="searchQuery,search"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Search Results Dropdown --}}
        @if($showResults && !empty($searchResults))
            <div
                class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-96 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                @click.outside="showDropdown = false"
            >
                @foreach($searchResults as $doc)
                    <button
                        type="button"
                        wire:click="selectDocument({{ $doc['id'] }})"
                        class="w-full text-left px-4 py-3 hover:bg-gray-100 transition-colors border-b border-gray-100 last:border-b-0"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $doc['title'] }}
                                </p>
                                @if(!empty($doc['correspondent_name']))
                                    <p class="text-xs text-gray-500 mt-1">
                                        <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $doc['correspondent_name'] }}
                                    </p>
                                @endif
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-xs text-gray-400">
                                        <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($doc['created'])->format('d.m.Y') }}
                                    </p>
                                    @if(!empty($doc['archive_serial_number']))
                                        <p class="text-xs text-gray-400">
                                            <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            #{{ $doc['archive_serial_number'] }}
                                        </p>
                                    @endif
                                </div>
                                {{-- Content preview if available --}}
                                @if(!empty($doc['content']) && strlen($doc['content']) > 50)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        {{ Str::limit($doc['content'], 100) }}
                                    </p>
                                @endif
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- No Results Message --}}
        @if($showResults && empty($searchResults) && !$isSearching && strlen($searchQuery) >= 2)
            <div class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md py-4 px-4 text-center ring-1 ring-black ring-opacity-5">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Keine Dokumente gefunden</p>
                <p class="text-xs text-gray-400">Versuche andere Suchbegriffe</p>
            </div>
        @endif
    </div>

    {{-- Selected Document Display --}}
    @if($selectedDocument)
        <div class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-indigo-900">Ausgewähltes Dokument</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-900 font-medium">{{ $selectedDocument['title'] }}</p>
                    <div class="mt-1 flex items-center gap-3 text-xs text-gray-600">
                        @if(!empty($selectedDocument['correspondent_name']))
                            <span>{{ $selectedDocument['correspondent_name'] }}</span>
                        @endif
                        <span>{{ \Carbon\Carbon::parse($selectedDocument['created'])->format('d.m.Y') }}</span>
                        @if(!empty($selectedDocument['archive_serial_number']))
                            <span>ASN: {{ $selectedDocument['archive_serial_number'] }}</span>
                        @endif
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="clearSelection"
                    class="ml-4 text-indigo-600 hover:text-indigo-800"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Search Hint --}}
    @if(!$selectedDocument && !$searchQuery)
        <div class="mt-2">
            <p class="text-xs text-gray-500">
                <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Suche nach Dokumenttitel, Datum (DD.MM.YYYY) oder Textinhalt
            </p>
        </div>
    @endif
</div>
