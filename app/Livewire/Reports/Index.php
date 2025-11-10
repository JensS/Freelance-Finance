<?php

namespace App\Livewire\Reports;

use App\Services\MonthlyReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Berichte')]
class Index extends Component
{
    public string $selectedPeriod = '';

    public ?array $reportData = null;

    public bool $showPreview = false;

    public function mount()
    {
        // Default to current month
        $this->selectedPeriod = Carbon::now()->format('Y-m');
    }

    public function generatePreview()
    {
        $this->validate([
            'selectedPeriod' => 'required|string',
        ]);

        [$year, $month] = explode('-', $this->selectedPeriod);

        $reportService = app(MonthlyReportService::class);
        $this->reportData = $reportService->generateReportData((int) $year, (int) $month);
        $this->showPreview = true;
    }

    /**
     * Get chart data for expenses by category
     */
    public function getExpenseChartData(): array
    {
        if (! $this->reportData || ! isset($this->reportData['expenses_by_category'])) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];

        foreach ($this->reportData['expenses_by_category'] as $category => $data) {
            $labels[] = $category ?: 'Unkategorisiert';
            $values[] = round($data['total'], 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function downloadPdf()
    {
        $this->validate([
            'selectedPeriod' => 'required|string',
        ]);

        [$year, $month] = explode('-', $this->selectedPeriod);

        $reportService = app(MonthlyReportService::class);
        $pdf = $reportService->generatePdf((int) $year, (int) $month);

        $filename = sprintf(
            'Monatsbericht_%s-%s.pdf',
            $year,
            str_pad($month, 2, '0', STR_PAD_LEFT)
        );

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function render()
    {
        $reportService = app(MonthlyReportService::class);
        $availablePeriods = $reportService->getAvailablePeriods();

        return view('livewire.reports.index', [
            'availablePeriods' => $availablePeriods,
        ]);
    }
}
