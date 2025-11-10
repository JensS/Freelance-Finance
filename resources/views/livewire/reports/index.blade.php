<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Berichte</h1>
        <p class="mt-1 text-sm text-gray-500">Erstellen Sie monatliche Finanzberichte für Ihren Steuerberater</p>
    </div>

    <!-- Report Generator Card -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Monatsbericht generieren</h3>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Period Selection -->
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700">Zeitraum</label>
                    <select
                        wire:model="selectedPeriod"
                        id="period"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="sm:col-span-2 flex items-end gap-3">
                    <button
                        wire:click="generatePreview"
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Vorschau
                    </button>

                    <button
                        wire:click="downloadPdf"
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PDF herunterladen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Preview -->
    @if($showPreview && $reportData)
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">
                Vorschau: Monatsbericht {{ $reportData['summary']['period_de'] }}
            </h3>

            <!-- Summary Section -->
            <div class="mb-8 bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Zusammenfassung</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Gesamtumsatz (brutto)</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($reportData['summary']['total_revenue'], 2, ',', '.') }} €</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Umsatzsteuer</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($reportData['summary']['total_tax'], 2, ',', '.') }} €</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Nettoumsatz</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($reportData['summary']['net_revenue'], 2, ',', '.') }} €</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Betriebsausgaben</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($reportData['summary']['business_expenses'], 2, ',', '.') }} €</p>
                    </div>
                    <div class="sm:col-span-2 border-t border-indigo-300 pt-4 mt-2">
                        <p class="text-sm text-gray-600">Gewinn</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($reportData['summary']['profit'], 2, ',', '.') }} €</p>
                        <p class="text-sm text-gray-500 mt-1">Gewinnmarge: {{ number_format($reportData['summary']['profit_margin'], 1, ',', '.') }}%</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Income vs Expenses Chart -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Einnahmen vs. Ausgaben</h4>
                    <div class="h-64">
                        <canvas id="incomeExpenseReportChart"></canvas>
                    </div>
                </div>

                <!-- Expenses by Category Chart -->
                @if($reportData['expenses_by_category']->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Ausgaben nach Kategorie</h4>
                    <div class="h-64">
                        <canvas id="expensesCategoryChart"></canvas>
                    </div>
                </div>
                @endif
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('livewire:navigated', function() {
                    initializeReportCharts();
                });

                // Also initialize on preview generation
                Livewire.on('previewGenerated', () => {
                    setTimeout(initializeReportCharts, 100);
                });

                // Initialize on first load if preview is shown
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeReportCharts);
                } else {
                    initializeReportCharts();
                }

                function initializeReportCharts() {
                    // Income vs Expenses Chart
                    const incomeExpenseCtx = document.getElementById('incomeExpenseReportChart');
                    if (incomeExpenseCtx) {
                        const existingChart1 = Chart.getChart(incomeExpenseCtx);
                        if (existingChart1) existingChart1.destroy();

                        new Chart(incomeExpenseCtx, {
                            type: 'bar',
                            data: {
                                labels: ['Einnahmen', 'Ausgaben', 'Gewinn'],
                                datasets: [{
                                    label: 'Betrag (€)',
                                    data: [
                                        @js($reportData['summary']['total_revenue']),
                                        @js($reportData['summary']['business_expenses']),
                                        @js($reportData['summary']['profit'])
                                    ],
                                    backgroundColor: [
                                        'rgba(34, 197, 94, 0.7)',
                                        'rgba(239, 68, 68, 0.7)',
                                        'rgba(59, 130, 246, 0.7)'
                                    ],
                                    borderColor: [
                                        'rgb(34, 197, 94)',
                                        'rgb(239, 68, 68)',
                                        'rgb(59, 130, 246)'
                                    ],
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return new Intl.NumberFormat('de-DE', {
                                                    style: 'currency',
                                                    currency: 'EUR'
                                                }).format(context.parsed.y);
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
                                }
                            }
                        });
                    }

                    // Expenses by Category Chart
                    const expensesCategoryCtx = document.getElementById('expensesCategoryChart');
                    if (expensesCategoryCtx) {
                        const existingChart2 = Chart.getChart(expensesCategoryCtx);
                        if (existingChart2) existingChart2.destroy();

                        const categoryData = @json($this->getExpenseChartData());

                        // Generate distinct colors for each category
                        const colors = generateColors(categoryData.labels.length);

                        new Chart(expensesCategoryCtx, {
                            type: 'doughnut',
                            data: {
                                labels: categoryData.labels,
                                datasets: [{
                                    data: categoryData.values,
                                    backgroundColor: colors.background,
                                    borderColor: colors.border,
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            boxWidth: 12,
                                            padding: 10,
                                            font: {
                                                size: 11
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = new Intl.NumberFormat('de-DE', {
                                                    style: 'currency',
                                                    currency: 'EUR'
                                                }).format(context.parsed);
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                                return `${label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                function generateColors(count) {
                    const baseColors = [
                        { bg: 'rgba(239, 68, 68, 0.7)', border: 'rgb(239, 68, 68)' },
                        { bg: 'rgba(59, 130, 246, 0.7)', border: 'rgb(59, 130, 246)' },
                        { bg: 'rgba(245, 158, 11, 0.7)', border: 'rgb(245, 158, 11)' },
                        { bg: 'rgba(16, 185, 129, 0.7)', border: 'rgb(16, 185, 129)' },
                        { bg: 'rgba(139, 92, 246, 0.7)', border: 'rgb(139, 92, 246)' },
                        { bg: 'rgba(236, 72, 153, 0.7)', border: 'rgb(236, 72, 153)' },
                        { bg: 'rgba(20, 184, 166, 0.7)', border: 'rgb(20, 184, 166)' },
                        { bg: 'rgba(251, 146, 60, 0.7)', border: 'rgb(251, 146, 60)' }
                    ];

                    const background = [];
                    const border = [];

                    for (let i = 0; i < count; i++) {
                        const color = baseColors[i % baseColors.length];
                        background.push(color.bg);
                        border.push(color.border);
                    }

                    return { background, border };
                }
            </script>

            <!-- Invoice Statistics -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-900 mb-3">Rechnungsstatistik</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Anzahl Rechnungen</p>
                        <p class="text-xl font-semibold text-gray-900">{{ $reportData['invoice_stats']['count'] }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Durchschnittsbetrag</p>
                        <p class="text-xl font-semibold text-gray-900">
                            {{ $reportData['invoice_stats']['count'] > 0 ? number_format($reportData['invoice_stats']['total'] / $reportData['invoice_stats']['count'], 2, ',', '.') : '0,00' }} €
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Gesamtsumme</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($reportData['invoice_stats']['total'], 2, ',', '.') }} €</p>
                    </div>
                </div>

                @if($reportData['invoices']->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rechnungsnr.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kunde</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Betrag</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData['invoices'] as $invoice)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $invoice->issue_date ? $invoice->issue_date->format($dateFormat) : '' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $invoice->customer->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Transaction Statistics -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-900 mb-3">Transaktionsstatistik</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Einnahmen</p>
                        <p class="text-xl font-semibold text-green-600">{{ number_format($reportData['transaction_stats']['total_income'], 2, ',', '.') }} €</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $reportData['transaction_stats']['income_count'] }} Transaktionen</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Ausgaben gesamt</p>
                        <p class="text-xl font-semibold text-red-600">{{ number_format($reportData['transaction_stats']['total_expenses'], 2, ',', '.') }} €</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $reportData['transaction_stats']['expense_count'] }} Transaktionen</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Betrieblich</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($reportData['transaction_stats']['business_expenses'], 2, ',', '.') }} €</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Privat</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format($reportData['transaction_stats']['personal_expenses'], 2, ',', '.') }} €</p>
                    </div>
                </div>
            </div>

            <!-- Expenses by Category -->
            @if($reportData['expenses_by_category']->count() > 0)
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-900 mb-3">Ausgaben nach Kategorie</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategorie</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Anzahl</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Betrag</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData['expenses_by_category'] as $category => $data)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category ?: 'Unkategorisiert' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center">{{ $data['count'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($data['total'], 2, ',', '.') }} €</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Information Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Hinweise zur Verwendung</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Der Monatsbericht enthält alle Rechnungen und Transaktionen des ausgewählten Zeitraums</li>
                        <li>Betriebsausgaben werden separat von privaten Ausgaben ausgewiesen</li>
                        <li>Der Bericht kann direkt an Ihren Steuerberater weitergeleitet werden</li>
                        <li>Stellen Sie sicher, dass alle Transaktionen validiert und kategorisiert sind</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
