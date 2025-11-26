<!-- Upload Modal -->
@if($showUploadModal)
    <div class="fixed z-50 inset-0 overflow-y-auto" x-data="{ open: true }" x-show="open">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 z-40 bg-gray-500 bg-opacity-75 transition-opacity"
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
            <div class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
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
