<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Rechnungen')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function downloadPdf(int $invoiceId)
    {
        $invoice = Invoice::with('customer')->findOrFail($invoiceId);
        $pdf = $invoice->generatePdf();

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $invoice->getPdfFilename());
    }

    public function uploadToPaperless(int $invoiceId)
    {
        $invoice = Invoice::with('customer')->findOrFail($invoiceId);

        if ($invoice->uploadToPaperless()) {
            session()->flash('success', 'Rechnung erfolgreich zu Paperless hochgeladen.');
        } else {
            session()->flash('error', 'Fehler beim Hochladen zu Paperless. Bitte prüfen Sie Ihre Paperless-Konfiguration.');
        }
    }

    public function deleteInvoice(int $invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);
            $invoiceNumber = $invoice->invoice_number;

            $invoice->delete();

            session()->flash('success', "Rechnung {$invoiceNumber} wurde erfolgreich gelöscht.");
        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Löschen der Rechnung: '.$e->getMessage());
        }
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with('customer')
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhere('project_name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('issue_date', 'desc')
            ->paginate(15);

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
        ]);
    }
}
