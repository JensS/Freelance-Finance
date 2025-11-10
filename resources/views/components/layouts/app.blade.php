<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}">
                                @php
                                    $logoPath = \App\Models\Setting::get('company_logo_path');
                                @endphp
                                @if ($logoPath)
                                    <img class="h-8 w-auto" src="{{ Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="{{ config('app.name') }}">
                                @else
                                    <span class="text-xl font-bold text-gray-900">{{ config('app.name') }}</span>
                                @endif
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('invoices.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('invoices.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Rechnungen
                            </a>
                            <a href="{{ route('quotes.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('quotes.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Angebote
                            </a>
                            <a href="{{ route('customers.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('customers.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Kunden
                            </a>
                            <a href="{{ route('accounting.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('accounting.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Buchhaltung
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('reports.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Berichte
                            </a>
                            <a href="{{ route('settings.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('settings.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Einstellungen
                            </a>
                        </div>
                    </div>

                    <!-- Logout -->
                    <div class="flex items-center">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                Abmelden
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
