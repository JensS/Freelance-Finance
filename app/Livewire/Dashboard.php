<?php

namespace App\Livewire;

use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quote;
use App\Services\AIService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public ?string $aiInsights = null;

    public bool $loadingInsights = false;

    public function generateAiInsights()
    {
        $this->loadingInsights = true;

        try {
            $aiService = app(AIService::class);

            // Gather financial data for current month
            $currentMonth = now();
            $revenue = Invoice::whereYear('issue_date', $currentMonth->year)
                ->whereMonth('issue_date', $currentMonth->month)
                ->sum('total');

            $expenses = BankTransaction::whereYear('transaction_date', $currentMonth->year)
                ->whereMonth('transaction_date', $currentMonth->month)
                ->where('amount', '<', 0)
                ->sum('amount');

            $businessExpenses = BankTransaction::whereYear('transaction_date', $currentMonth->year)
                ->whereMonth('transaction_date', $currentMonth->month)
                ->where('amount', '<', 0)
                ->where('is_business_expense', true)
                ->sum('amount');

            $invoicesPaid = Invoice::whereYear('issue_date', $currentMonth->year)
                ->whereMonth('issue_date', $currentMonth->month)
                ->count();

            $avgInvoice = $invoicesPaid > 0 ? $revenue / $invoicesPaid : 0;

            $data = [
                'revenue' => number_format($revenue, 2, '.', ''),
                'expenses' => number_format(abs($expenses), 2, '.', ''),
                'business_expenses' => number_format(abs($businessExpenses), 2, '.', ''),
                'personal_expenses' => number_format(abs($expenses) - abs($businessExpenses), 2, '.', ''),
                'invoices_paid' => $invoicesPaid,
                'avg_invoice' => number_format($avgInvoice, 2, '.', ''),
                'profit_margin' => $revenue > 0 ? number_format((($revenue + $expenses) / $revenue) * 100, 1) : 0,
                'savings_rate' => $revenue > 0 ? number_format((($revenue + $expenses) / $revenue) * 100, 1) : 0,
            ];

            // Build analysis prompt
            $prompt = "Analysiere die folgenden monatlichen Finanzdaten eines Freelancers und gib professionelle Insights:\n\n";
            $prompt .= "Umsatz: €{$data['revenue']}\n";
            $prompt .= "Gesamtausgaben: €{$data['expenses']}\n";
            $prompt .= "Geschäftsausgaben: €{$data['business_expenses']}\n";
            $prompt .= "Privatausgaben: €{$data['personal_expenses']}\n";
            $prompt .= "Rechnungen bezahlt: {$data['invoices_paid']}\n";
            $prompt .= "Durchschnittliche Rechnung: €{$data['avg_invoice']}\n";
            $prompt .= "Gewinnmarge: {$data['profit_margin']}%\n";
            $prompt .= "Sparquote: {$data['savings_rate']}%\n\n";
            $prompt .= "Bitte gib eine kurze Analyse (3-4 Sätze) mit:\n";
            $prompt .= "1. Bewertung der finanziellen Situation\n";
            $prompt .= "2. Auffälligkeiten oder Trends\n";
            $prompt .= "3. Konkrete Handlungsempfehlungen\n";
            $prompt .= "\nAntworte auf Deutsch und in einem professionellen, hilfreichen Ton.";

            $this->aiInsights = $aiService->generateText($prompt, ['temperature' => 0.7]);

            if (! $this->aiInsights) {
                $this->aiInsights = 'KI-Analyse konnte nicht generiert werden. Bitte überprüfen Sie die AI-Konfiguration.';
            }

        } catch (\Exception $e) {
            $this->aiInsights = 'Fehler bei der KI-Analyse: '.$e->getMessage();
        }

        $this->loadingInsights = false;
    }

    public function render()
    {
        $currentMonth = now();
        $lastMonth = now()->subMonth();

        $stats = [
            'total_invoices' => Invoice::count(),
            'total_quotes' => Quote::count(),
            'total_customers' => Customer::count(),
            'pending_transactions' => BankTransaction::where('is_validated', false)->count(),

            'current_month_income' => Invoice::whereYear('issue_date', $currentMonth->year)
                ->whereMonth('issue_date', $currentMonth->month)
                ->sum('total'),

            'last_month_income' => Invoice::whereYear('issue_date', $lastMonth->year)
                ->whereMonth('issue_date', $lastMonth->month)
                ->sum('total'),

            'current_year_income' => Invoice::whereYear('issue_date', $currentMonth->year)->sum('total'),

            'overdue_invoices' => Invoice::where('due_date', '<', now())
                ->whereNull('paperless_document_id')
                ->count(),

            'current_month_expenses' => abs(BankTransaction::whereYear('transaction_date', $currentMonth->year)
                ->whereMonth('transaction_date', $currentMonth->month)
                ->where('amount', '<', 0)
                ->sum('amount')),

            'last_month_expenses' => abs(BankTransaction::whereYear('transaction_date', $lastMonth->year)
                ->whereMonth('transaction_date', $lastMonth->month)
                ->where('amount', '<', 0)
                ->sum('amount')),
        ];

        // Recent invoices
        $recentInvoices = Invoice::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent transactions
        $recentTransactions = BankTransaction::orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        // Prepare chart data for the last 6 months
        $chartData = $this->prepareChartData();

        return view('livewire.dashboard', compact('stats', 'recentInvoices', 'recentTransactions', 'chartData'));
    }

    /**
     * Prepare data for income/expense chart
     */
    private function prepareChartData(): array
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];

        // Get last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $months[] = $monthLabel;

            // Get income for this month
            $income = Invoice::whereYear('issue_date', $date->year)
                ->whereMonth('issue_date', $date->month)
                ->sum('total');
            $incomeData[] = round($income, 2);

            // Get expenses for this month
            $expenses = abs(BankTransaction::whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)
                ->where('amount', '<', 0)
                ->sum('amount'));
            $expenseData[] = round($expenses, 2);
        }

        return [
            'labels' => $months,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }
}
