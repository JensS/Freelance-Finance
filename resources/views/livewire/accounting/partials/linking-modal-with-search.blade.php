{{-- Example: Linking Modal using PaperlessDocumentLinker Component --}}
{{-- This is an example of how to use the reusable PaperlessDocumentLinker component --}}

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
                        ], key('linker-'.$linkingTransaction['id']))
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                        wire:click="linkDocumentFromLinker"
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="linkDocumentFromLinker"
                        class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="linkDocumentFromLinker">Verknüpfen</span>
                        <span wire:loading wire:target="linkDocumentFromLinker" class="inline-flex items-center">
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

    {{-- Listen to events from the linker component --}}
    <script>
        // Listen for document selection
        Livewire.on('documentSelected', (event) => {
            console.log('Document selected:', event.documentId);
            // Store the selected document ID
            @this.set('selectedDocumentIdFromLinker', event.documentId);
        });

        // Listen for document cleared
        Livewire.on('documentCleared', () => {
            @this.set('selectedDocumentIdFromLinker', null);
        });
    </script>
@endif
