<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Kunde bearbeiten</h1>
        <p class="mt-1 text-sm text-gray-500">Bearbeiten Sie die Kundendaten</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Grundinformationen</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Firmenname / Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            id="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            E-Mail <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="email"
                            type="email"
                            id="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700">
                            Ansprechpartner
                        </label>
                        <input
                            wire:model="contact_person"
                            type="text"
                            id="contact_person"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('contact_person')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Adresse</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Street -->
                    <div>
                        <label for="street" class="block text-sm font-medium text-gray-700">
                            Straße und Hausnummer <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="street"
                            type="text"
                            id="street"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('street')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City and ZIP -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="zip" class="block text-sm font-medium text-gray-700">
                                Postleitzahl <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="zip"
                                type="text"
                                id="zip"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('zip')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                Stadt <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="city"
                                type="text"
                                id="city"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                            @error('city')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Steuerinformationen</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Tax ID -->
                    <div>
                        <label for="tax_id" class="block text-sm font-medium text-gray-700">
                            Steuernummer
                        </label>
                        <input
                            wire:model="tax_id"
                            type="text"
                            id="tax_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('tax_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- EU VAT ID -->
                    <div>
                        <label for="eu_vat_id" class="block text-sm font-medium text-gray-700">
                            Umsatzsteuer-Identifikationsnummer
                        </label>
                        <input
                            wire:model="eu_vat_id"
                            type="text"
                            id="eu_vat_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="DE123456789"
                        >
                        @error('eu_vat_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Notizen</h3>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Interne Notizen
                    </label>
                    <textarea
                        wire:model="notes"
                        id="notes"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
            <a
                href="{{ route('customers.index') }}"
                class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Abbrechen
            </a>
            <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Änderungen speichern
            </button>
        </div>
    </form>
</div>
