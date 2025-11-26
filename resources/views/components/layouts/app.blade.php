<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
                            @if(auth()->user()->isOwner())
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
                            @endif
                            <a href="{{ route('accounting.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('accounting.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Buchhaltung
                            </a>
                            <a href="{{ route('transactions.verify-imports') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('transactions.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Überprüfung
                                @php
                                    $unvalidatedCount = \App\Models\BankTransaction::where('is_validated', false)->count();
                                @endphp
                                @if($unvalidatedCount > 0)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $unvalidatedCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('reports.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                Berichte
                            </a>
                            @if(auth()->user()->isOwner())
                                <a href="{{ route('settings.index') }}"
                                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('settings.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium">
                                    Einstellungen
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="flex items-center" x-data="{ open: false }">
                        <div class="relative ml-3">
                            <div>
                                <button @click="open = !open" type="button" class="flex items-center max-w-xs rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium text-sm">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
                                    </div>
                                </button>
                            </div>

                            <!-- Dropdown menu -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 role="menu"
                                 aria-orientation="vertical"
                                 aria-labelledby="user-menu-button"
                                 tabindex="-1"
                                 style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email }}</p>
                                    <p class="text-xs text-indigo-600 mt-1 font-medium">{{ auth()->user()->role->label() }}</p>
                                </div>
                                <div class="py-1">
                                    @if(config('app.debug'))
                                        <a href="{{ route('log-viewer.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Debug Log
                                        </a>
                                    @endif
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Abmelden
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
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
