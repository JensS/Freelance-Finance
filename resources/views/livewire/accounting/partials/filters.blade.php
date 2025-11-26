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
