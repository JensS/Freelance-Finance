@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Branding Einstellungen</h1>
        <p class="mt-1 text-sm text-gray-500">Verwalten Sie Logo und Schriftarten für PDF-Dokumente</p>
    </div>

    <!-- Success Message -->
    @if($success)
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ $success }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex gap-6">
        <!-- Settings Form (40%) -->
        <div class="w-2/5">
            <form wire:submit="save" class="space-y-6">
        <!-- Company Logo -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Firmenlogo</h3>

                <div>
                    <label for="company_logo" class="block text-sm font-medium text-gray-700">
                        Logo hochladen
                    </label>
                    <div class="mt-1 flex items-center">
                        @if ($company_logo_path)
                            <div class="mr-4 flex-shrink-0">
                                <img src="{{ Storage::url($company_logo_path) }}" alt="Firmenlogo" class="h-12 w-auto">
                            </div>
                        @endif
                        <input
                            wire:model="company_logo"
                            type="file"
                            id="company_logo"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        >
                    </div>
                    @error('company_logo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div wire:loading wire:target="company_logo" class="mt-2 text-sm text-gray-500">Wird hochgeladen...</div>
                    <p class="mt-2 text-sm text-gray-500">Unterstützte Formate: PNG, JPG, SVG. Maximale Größe: 1MB.</p>
                </div>
            </div>
        </div>

        <!-- Heading Font Style -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Überschrift (Heading)</h3>
                <p class="text-sm text-gray-500 mb-4">Wird für große Überschriften verwendet (z.B. "Rechnung", "Angebot")</p>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Font File Upload -->
                    <div>
                        <label for="heading_font_file" class="block text-sm font-medium text-gray-700">
                            Schriftart-Datei (.ttf, .otf)
                        </label>
                        <input
                            wire:model="heading_font_file"
                            type="file"
                            id="heading_font_file"
                            accept=".ttf,.otf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        >
                        @if($heading_font_path)
                            <p class="mt-2 text-sm text-gray-500">Aktuell: {{ basename($heading_font_path) }}</p>
                        @endif
                        @error('heading_font_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="heading_font_file" class="mt-2 text-sm text-gray-500">Wird hochgeladen...</div>
                    </div>

                    <!-- Font Family -->
                    <div>
                        <label for="heading_font_family" class="block text-sm font-medium text-gray-700">
                            Schriftart-Name
                        </label>
                        <input
                            wire:model.live="heading_font_family"
                            type="text"
                            id="heading_font_family"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="z.B. Fira Sans"
                        >
                        @error('heading_font_family')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Font Size, Weight, Style -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="heading_font_size" class="block text-sm font-medium text-gray-700">
                                Schriftgröße
                            </label>
                            <input
                                wire:model.live="heading_font_size"
                                type="text"
                                id="heading_font_size"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="z.B. 24px"
                            >
                            @error('heading_font_size')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="heading_font_weight" class="block text-sm font-medium text-gray-700">
                                Schriftstärke
                            </label>
                            <select
                                wire:model.live="heading_font_weight"
                                id="heading_font_weight"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="normal">Normal</option>
                                <option value="bold">Fett</option>
                            </select>
                            @error('heading_font_weight')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="heading_font_style" class="block text-sm font-medium text-gray-700">
                                Schriftstil
                            </label>
                            <select
                                wire:model.live="heading_font_style"
                                id="heading_font_style"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="normal">Normal</option>
                                <option value="italic">Kursiv</option>
                            </select>
                            @error('heading_font_style')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Font Color -->
                    <div>
                        <label for="heading_font_color" class="block text-sm font-medium text-gray-700">
                            Schriftfarbe
                        </label>
                        <div class="mt-1 flex items-center">
                            <input
                                wire:model.live="heading_font_color"
                                type="color"
                                id="heading_font_color"
                                class="h-10 w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <input
                                wire:model.live="heading_font_color"
                                type="text"
                                class="ml-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="#333333"
                            >
                        </div>
                        @error('heading_font_color')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Small Heading Font Style -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Kleine Überschrift (Small Heading)</h3>
                <p class="text-sm text-gray-500 mb-4">Wird für kleinere Überschriften und hervorgehobene Texte verwendet (z.B. Tabellenkopfzeilen)</p>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Font File Upload -->
                    <div>
                        <label for="small_heading_font_file" class="block text-sm font-medium text-gray-700">
                            Schriftart-Datei (.ttf, .otf)
                        </label>
                        <input
                            wire:model="small_heading_font_file"
                            type="file"
                            id="small_heading_font_file"
                            accept=".ttf,.otf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        >
                        @if($small_heading_font_path)
                            <p class="mt-2 text-sm text-gray-500">Aktuell: {{ basename($small_heading_font_path) }}</p>
                        @endif
                        @error('small_heading_font_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="small_heading_font_file" class="mt-2 text-sm text-gray-500">Wird hochgeladen...</div>
                    </div>

                    <!-- Font Family -->
                    <div>
                        <label for="small_heading_font_family" class="block text-sm font-medium text-gray-700">
                            Schriftart-Name
                        </label>
                        <input
                            wire:model.live="small_heading_font_family"
                            type="text"
                            id="small_heading_font_family"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="z.B. Fira Sans"
                        >
                        @error('small_heading_font_family')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Font Size, Weight, Style -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="small_heading_font_size" class="block text-sm font-medium text-gray-700">
                                Schriftgröße
                            </label>
                            <input
                                wire:model.live="small_heading_font_size"
                                type="text"
                                id="small_heading_font_size"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="z.B. 14px"
                            >
                            @error('small_heading_font_size')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="small_heading_font_weight" class="block text-sm font-medium text-gray-700">
                                Schriftstärke
                            </label>
                            <select
                                wire:model.live="small_heading_font_weight"
                                id="small_heading_font_weight"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="normal">Normal</option>
                                <option value="bold">Fett</option>
                            </select>
                            @error('small_heading_font_weight')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="small_heading_font_style" class="block text-sm font-medium text-gray-700">
                                Schriftstil
                            </label>
                            <select
                                wire:model.live="small_heading_font_style"
                                id="small_heading_font_style"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="normal">Normal</option>
                                <option value="italic">Kursiv</option>
                            </select>
                            @error('small_heading_font_style')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Font Color -->
                    <div>
                        <label for="small_heading_font_color" class="block text-sm font-medium text-gray-700">
                            Schriftfarbe
                        </label>
                        <div class="mt-1 flex items-center">
                            <input
                                wire:model.live="small_heading_font_color"
                                type="color"
                                id="small_heading_font_color"
                                class="h-10 w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <input
                                wire:model.live="small_heading_font_color"
                                type="text"
                                class="ml-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="#333333"
                            >
                        </div>
                        @error('small_heading_font_color')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Body Font Style -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Fließtext (Body)</h3>
                <p class="text-sm text-gray-500 mb-4">Wird für normalen Fließtext verwendet (z.B. Adressen, Beschreibungen)</p>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Font File Upload -->
                    <div>
                        <label for="body_font_file" class="block text-sm font-medium text-gray-700">
                            Schriftart-Datei (.ttf, .otf)
                        </label>
                        <input
                            wire:model="body_font_file"
                            type="file"
                            id="body_font_file"
                            accept=".ttf,.otf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        >
                        @if($body_font_path)
                            <p class="mt-2 text-sm text-gray-500">Aktuell: {{ basename($body_font_path) }}</p>
                        @endif
                        @error('body_font_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="body_font_file" class="mt-2 text-sm text-gray-500">Wird hochgeladen...</div>
                    </div>

                    <!-- Font Family -->
                    <div>
                        <label for="body_font_family" class="block text-sm font-medium text-gray-700">
                            Schriftart-Name
                        </label>
                        <input
                            wire:model.live="body_font_family"
                            type="text"
                            id="body_font_family"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="z.B. Fira Sans"
                        >
                        @error('body_font_family')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Font Size, Weight, Style -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="body_font_size" class="block text-sm font-medium text-gray-700">
                                Schriftgröße
                            </label>
                            <input
                                wire:model.live="body_font_size"
                                type="text"
                                id="body_font_size"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="z.B. 12px"
                            >
                            @error('body_font_size')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="body_font_weight" class="block text-sm font-medium text-gray-700">
                                Schriftstärke
                            </label>
                            <select
                                wire:model.live="body_font_weight"
                                id="body_font_weight"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="normal">Normal</option>
                                <option value="bold">Fett</option>
                            </select>
                            @error('body_font_weight')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="body_font_style" class="block text-sm font-medium text-gray-700">
                                Schriftstil
                            </label>
                            <select
                                wire:model.live="body_font_style"
                                id="body_font_style"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="normal">Normal</option>
                                <option value="italic">Kursiv</option>
                            </select>
                            @error('body_font_style')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Font Color -->
                    <div>
                        <label for="body_font_color" class="block text-sm font-medium text-gray-700">
                            Schriftfarbe
                        </label>
                        <div class="mt-1 flex items-center">
                            <input
                                wire:model.live="body_font_color"
                                type="color"
                                id="body_font_color"
                                class="h-10 w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <input
                                wire:model.live="body_font_color"
                                type="text"
                                class="ml-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="#333333"
                            >
                        </div>
                        @error('body_font_color')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Einstellungen speichern
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview (60%) -->
        <div class="w-3/5">
            <div class="bg-white shadow rounded-lg sticky top-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Live-Vorschau</h3>
                        <a href="{{ route('preview.invoice.pdf') }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">
                            Als PDF öffnen →
                        </a>
                    </div>

                    <!-- Invoice Preview (iframe) -->
                    <div class="border border-gray-300 rounded-lg bg-white overflow-hidden" style="height: calc(100vh - 200px);">
                        <iframe
                            src="{{ route('preview.invoice.html') }}?v={{ $previewKey }}"
                            class="w-full h-full border-0"
                            wire:key="preview-{{ $previewKey }}"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
