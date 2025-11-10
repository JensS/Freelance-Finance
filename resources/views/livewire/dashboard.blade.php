<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Übersicht über Ihre Finanzen</p>
    </div>

    <!-- Overdue Invoices Alert -->
    @if($stats['overdue_invoices'] > 0)
    <div class="mb-6 rounded-md bg-yellow-50 p-4 border border-yellow-200">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    {{ $stats['overdue_invoices'] }} {{ $stats['overdue_invoices'] === 1 ? 'überfällige Rechnung' : 'überfällige Rechnungen' }}
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Sie haben unbezahlte Rechnungen, die das Fälligkeitsdatum überschritten haben.</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-yellow-800 hover:text-yellow-900">
                        Rechnungen ansehen →
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Invoices -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Rechnungen</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_invoices'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Quotes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Angebote</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_quotes'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Kunden</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_customers'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Transactions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Offene Transaktionen</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['pending_transactions'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Month Income -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Einnahmen (Monat)</dt>
                            <dd class="flex items-baseline">
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['current_month_income'], 2, ',', '.') }} €</span>
                                @php
                                    $incomeChange = $stats['last_month_income'] > 0
                                        ? (($stats['current_month_income'] - $stats['last_month_income']) / $stats['last_month_income']) * 100
                                        : 0;
                                @endphp
                                @if($incomeChange != 0)
                                    <span class="ml-2 text-sm font-medium {{ $incomeChange > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $incomeChange > 0 ? '↑' : '↓' }} {{ number_format(abs($incomeChange), 1) }}%
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Month Expenses -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Ausgaben (Monat)</dt>
                            <dd class="flex items-baseline">
                                <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['current_month_expenses'], 2, ',', '.') }} €</span>
                                @php
                                    $expenseChange = $stats['last_month_expenses'] > 0
                                        ? (($stats['current_month_expenses'] - $stats['last_month_expenses']) / $stats['last_month_expenses']) * 100
                                        : 0;
                                @endphp
                                @if($expenseChange != 0)
                                    <span class="ml-2 text-sm font-medium {{ $expenseChange > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $expenseChange > 0 ? '↑' : '↓' }} {{ number_format(abs($expenseChange), 1) }}%
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Year Income -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Einnahmen (Jahr)</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['current_year_income'], 2, ',', '.') }} €</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income vs Expenses Chart -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Einnahmen vs. Ausgaben (Letzte 6 Monate)</h3>
                <div class="h-80">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            initializeChart();
        });

        // Also initialize on first load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeChart);
        } else {
            initializeChart();
        }

        function initializeChart() {
            const ctx = document.getElementById('incomeExpenseChart');
            if (!ctx) return;

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @js($chartData['labels']),
                    datasets: [
                        {
                            label: 'Einnahmen',
                            data: @js($chartData['income']),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Ausgaben',
                            data: @js($chartData['expenses']),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat('de-DE', {
                                        style: 'currency',
                                        currency: 'EUR'
                                    }).format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('de-DE', {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    }).format(value);
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    </script>

    <!-- AI Insights Section -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">KI-Finanzanalyse</h3>
                        <p class="mt-1 text-sm text-gray-500">Erhalten Sie intelligente Einblicke in Ihre Finanzen</p>
                    </div>
                    <button
                        wire:click="generateAiInsights"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="generateAiInsights" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="generateAiInsights">Analyse generieren</span>
                        <span wire:loading wire:target="generateAiInsights">Wird generiert...</span>
                    </button>
                </div>

                @if($aiInsights)
                <div class="mt-4 prose prose-sm max-w-none">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="text-sm text-gray-700 whitespace-pre-line">{{ $aiInsights }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Recent Invoices -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aktuelle Rechnungen</h3>
                @if($recentInvoices->count() > 0)
                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200">
                        @foreach($recentInvoices as $invoice)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $invoice->invoice_number }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $invoice->customer->name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ number_format($invoice->total, 2, ',', '.') }} €
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $invoice->issue_date ? $invoice->issue_date->format($dateFormat) : '' }}
                                    </p>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-4">
                    <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Alle Rechnungen ansehen →
                    </a>
                </div>
                @else
                <p class="text-sm text-gray-500">Noch keine Rechnungen vorhanden.</p>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aktuelle Transaktionen</h3>
                @if($recentTransactions->count() > 0)
                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200">
                        @foreach($recentTransactions as $transaction)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($transaction->amount > 0)
                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    @else
                                    <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ Str::limit($transaction->description, 40) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $transaction->transaction_date ? $transaction->transaction_date->format($dateFormat) : '' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2, ',', '.') }} €
                                    </p>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-4">
                    <a href="{{ route('accounting.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Alle Transaktionen ansehen →
                    </a>
                </div>
                @else
                <p class="text-sm text-gray-500">Noch keine Transaktionen vorhanden.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Schnellzugriff</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('invoices.create') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">Neue Rechnung</p>
                </div>
            </a>

            <a href="{{ route('quotes.create') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Neues Angebot</p>
                </div>
            </a>

            <a href="{{ route('customers.create') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Neuer Kunde</p>
                </div>
            </a>

            <a href="{{ route('accounting.index') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Buchhaltung</p>
                </div>
            </a>
        </div>
    </div>
</div>
