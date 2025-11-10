<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MonthlyReportService
{
    /**
     * Generate monthly report data
     */
    public function generateReportData(int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Invoices data
        $invoices = Invoice::with('customer')
            ->whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->orderBy('issue_date', 'asc')
            ->get();

        $invoiceStats = [
            'count' => $invoices->count(),
            'total' => $invoices->sum('total'),
            'vat_amount' => $invoices->sum('vat_amount'),
            'subtotal' => $invoices->sum('subtotal'),
            'paid_count' => $invoices->whereNotNull('paperless_document_id')->count(),
        ];

        // Transactions data
        $transactions = BankTransaction::whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->orderBy('transaction_date', 'asc')
            ->get();

        $income = $transactions->where('amount', '>', 0);
        $expenses = $transactions->where('amount', '<', 0);
        $businessExpenses = $expenses->where('is_business_expense', true);
        $personalExpenses = $expenses->where('is_business_expense', false);

        $transactionStats = [
            'total_income' => $income->sum('amount'),
            'total_expenses' => abs($expenses->sum('amount')),
            'business_expenses' => abs($businessExpenses->sum('amount')),
            'personal_expenses' => abs($personalExpenses->sum('amount')),
            'income_count' => $income->count(),
            'expense_count' => $expenses->count(),
            'validated_count' => $transactions->where('is_validated', true)->count(),
            'unvalidated_count' => $transactions->where('is_validated', false)->count(),
        ];

        // Financial summary
        $profit = $invoiceStats['total'] - $transactionStats['business_expenses'];
        $profitMargin = $invoiceStats['total'] > 0
            ? ($profit / $invoiceStats['total']) * 100
            : 0;

        $summary = [
            'period' => $startDate->format('F Y'),
            'period_de' => $startDate->locale('de')->isoFormat('MMMM YYYY'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_revenue' => $invoiceStats['total'],
            'total_vat' => $invoiceStats['vat_amount'],
            'net_revenue' => $invoiceStats['subtotal'],
            'business_expenses' => $transactionStats['business_expenses'],
            'profit' => $profit,
            'profit_margin' => $profitMargin,
        ];

        // Category breakdown for expenses
        $expensesByCategory = $expenses->groupBy('category')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => abs($group->sum('amount')),
            ];
        })->sortByDesc('total');

        return [
            'summary' => $summary,
            'invoices' => $invoices,
            'invoice_stats' => $invoiceStats,
            'transactions' => $transactions,
            'transaction_stats' => $transactionStats,
            'income' => $income,
            'expenses' => $expenses,
            'business_expenses' => $businessExpenses,
            'personal_expenses' => $personalExpenses,
            'expenses_by_category' => $expensesByCategory,
        ];
    }

    /**
     * Generate PDF for monthly report
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(int $year, int $month)
    {
        $data = $this->generateReportData($year, $month);

        return Pdf::loadView('pdfs.monthly-report', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Get available report periods (last 24 months)
     */
    public function getAvailablePeriods(): array
    {
        $periods = [];
        $current = Carbon::now();

        for ($i = 0; $i < 24; $i++) {
            $date = $current->copy()->subMonths($i);
            $periods[] = [
                'year' => $date->year,
                'month' => $date->month,
                'label' => $date->locale('de')->isoFormat('MMMM YYYY'),
                'value' => $date->format('Y-m'),
            ];
        }

        return $periods;
    }
}
