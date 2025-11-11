<?php

namespace App\Livewire\Quotes;

use App\Models\Quote;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Angebote')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function downloadPdf(int $quoteId)
    {
        $quote = Quote::with('customer')->findOrFail($quoteId);

        $pdf = $quote->generatePdf();

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $quote->getPdfFilename());
    }

    public function uploadToPaperless(int $quoteId)
    {
        $quote = Quote::with('customer')->findOrFail($quoteId);

        if ($quote->uploadToPaperless()) {
            session()->flash('success', 'Angebot erfolgreich zu Paperless hochgeladen.');
        } else {
            session()->flash('error', 'Fehler beim Hochladen zu Paperless. Bitte prüfen Sie Ihre Paperless-Konfiguration.');
        }
    }

    public function convertToInvoice(int $quoteId)
    {
        $quote = Quote::with('customer')->findOrFail($quoteId);

        if ($quote->isConverted()) {
            session()->flash('error', 'Dieses Angebot wurde bereits in eine Rechnung umgewandelt.');

            return;
        }

        $invoice = $quote->convertToInvoice();

        if ($invoice) {
            session()->flash('success', "Angebot erfolgreich in Rechnung {$invoice->invoice_number} umgewandelt.");

            return redirect()->route('invoices.edit', $invoice->id);
        } else {
            session()->flash('error', 'Fehler beim Umwandeln des Angebots. Bitte versuchen Sie es erneut.');
        }
    }

    public function deleteQuote(int $quoteId)
    {
        try {
            $quote = Quote::findOrFail($quoteId);
            $quoteNumber = $quote->quote_number;

            $quote->delete();

            session()->flash('success', "Angebot {$quoteNumber} wurde erfolgreich gelöscht.");
        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Löschen des Angebots: '.$e->getMessage());
        }
    }

    public function render()
    {
        $quotes = Quote::with('customer')
            ->when($this->search, function ($query) {
                $query->where('quote_number', 'like', '%'.$this->search.'%')
                    ->orWhere('project_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.quotes.index', [
            'quotes' => $quotes,
        ]);
    }
}
