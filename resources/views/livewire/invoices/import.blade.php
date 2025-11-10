<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Rechnungen Importieren</h1>
            <p class="mt-1 text-sm text-gray-500">Importieren Sie Rechnungen aus PDF-Dateien</p>
        </div>
        <a
            href="{{ route('invoices.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Zurück
        </a>
    </div>

    <!-- Success Messages -->
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

    <!-- Error Messages -->
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

    <!-- File Upload Area -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-2">PDF-Dateien auswählen</h2>
                <p class="text-sm text-gray-500">Wählen Sie eine oder mehrere PDF-Dateien mit Rechnungen zum Importieren aus.</p>
            </div>

            <!-- File Upload Drop Zone -->
            <div
                x-data="{ isDragging: false }"
                x-on:dragover.prevent="isDragging = true"
                x-on:dragleave.prevent="isDragging = false"
                x-on:drop.prevent="isDragging = false; $wire.upload('files', $event.dataTransfer.files)"
                class="border-2 border-dashed rounded-lg p-6 text-center"
                :class="{ 'border-indigo-500 bg-indigo-50': isDragging, 'border-gray-300': !isDragging }"
            >
                <div class="space-y-2">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div class="text-sm text-gray-600">
                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <span>PDF-Dateien auswählen</span>
                            <input id="file-upload" name="file-upload" type="file" class="sr-only" wire:model="files" multiple accept=".pdf">
                        </label>
                        <p class="pl-1">oder hierher ziehen</p>
                    </div>
                    <p class="text-xs text-gray-500">PDF bis zu 10MB</p>
                </div>
            </div>

            @error('files.*')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Selected Files -->
            @if(count($files) > 0)
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Ausgewählte Dateien</h3>
                    <div class="space-y-2">
                        @foreach($files as $index => $file)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-900">{{ $file->getClientOriginalName() }}</span>
                                    <span class="text-xs text-gray-500 ml-2">({{ number_format($file->getSize() / 1024 / 1024, 2) }} MB)</span>
                                </div>
                                <button
                                    wire:click="removeFile({{ $index }})"
                                    class="text-red-600 hover:text-red-800"
                                    title="Datei entfernen"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Import Button -->
            @if(count($files) > 0)
                <div class="mt-6 flex justify-end">
                    <button
                        wire:click="import"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <span wire:loading.remove>Rechnungen importieren</span>
                        <span wire:loading>Importiere...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Import Results -->
    @if(count($importResults) > 0)
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import-Ergebnisse</h3>
                <div class="space-y-3">
                    @foreach($importResults as $result)
                        <div class="flex items-center justify-between p-4 rounded-lg @if($result['status'] === 'success') bg-green-50 @else bg-red-50 @endif">
                            <div class="flex items-center">
                                @if($result['status'] === 'success')
                                    <svg class="h-5 w-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['filename'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $result['message'] }}</p>
                                    @if(isset($result['invoice_number']))
                                        <p class="text-xs text-gray-500 mt-1">Rechnungsnr.: {{ $result['invoice_number'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>