<div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
    <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6">
        <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900">
            {{ config('app.name') }}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Buchhaltung für Freiberufler
        </p>
    </div>

    <form wire:submit="login" class="space-y-6">
        <!-- Password Input -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Passwort
            </label>
            <div class="mt-1">
                <input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                    placeholder="Passwort eingeben"
                >
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Error Message -->
        @if($error)
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Submit Button -->
        <div>
            <button
                type="submit"
                class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Anmelden
            </button>
        </div>
    </form>
</div>
