<?php

namespace App\Livewire\Transactions;

use App\Models\BankTransaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Transaktionen überprüfen')]
class VerifyImports extends Component
{
    public ?BankTransaction $currentTransaction = null;

    public $transaction_id;

    public $transaction_date;

    public $correspondent;

    public $title;

    public $description;

    public $type;

    public $amount;

    public $net_amount;

    public $vat_rate;

    public $vat_amount;

    public $note;

    public $transactionTypes = [];

    public $unvalidatedCount = 0;

    public $success = '';

    public $aiExtracting = false;

    public $aiError = '';

    public $paperlessDocumentId = null;

    public $paperlessDocumentUrl = null;

    public $paperlessDocumentTitle = null;

    public function mount()
    {
        $this->transactionTypes = BankTransaction::getTransactionTypes();
        $this->loadNextTransaction();
    }

    public function loadNextTransaction()
    {
        // Get the next unvalidated transaction
        $this->currentTransaction = BankTransaction::where('is_validated', false)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        // Count remaining unvalidated transactions
        $this->unvalidatedCount = BankTransaction::where('is_validated', false)->count();

        if ($this->currentTransaction) {
            $this->loadTransactionData();
        } else {
            $this->resetForm();
        }

        $this->success = '';
    }

    public function loadTransactionData()
    {
        if (! $this->currentTransaction) {
            return;
        }

        $this->transaction_id = $this->currentTransaction->id;
        $this->transaction_date = $this->currentTransaction->transaction_date->format('d.m.Y');
        $this->correspondent = $this->currentTransaction->correspondent;
        $this->title = $this->currentTransaction->title;
        $this->description = $this->currentTransaction->description;
        $this->type = $this->currentTransaction->type;
        $this->amount = $this->currentTransaction->amount;
        $this->note = $this->currentTransaction->note;

        // Calculate net/gross if not already set
        if ($this->currentTransaction->net_amount === null) {
            $this->currentTransaction->calculateNetGross();
        }

        $this->net_amount = $this->currentTransaction->net_amount;
        $this->vat_rate = $this->currentTransaction->vat_rate;
        $this->vat_amount = $this->currentTransaction->vat_amount;

        // Load Paperless document if available
        $this->loadPaperlessDocument();
    }

    private function loadPaperlessDocument()
    {
        $this->paperlessDocumentId = null;
        $this->paperlessDocumentUrl = null;
        $this->paperlessDocumentTitle = null;

        if (! $this->currentTransaction || ! $this->currentTransaction->matched_paperless_document_id) {
            return;
        }

        try {
            $paperlessService = app(\App\Services\PaperlessService::class);
            $documentId = (int) $this->currentTransaction->matched_paperless_document_id;

            // Get document info
            $document = $paperlessService->getDocument($documentId);

            if ($document) {
                $this->paperlessDocumentId = $documentId;
                $this->paperlessDocumentTitle = $document['title'] ?? 'Dokument #'.$documentId;
                // Use proxy route for authenticated access
                $this->paperlessDocumentUrl = route('paperless.preview', ['documentId' => $documentId]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load Paperless document for transaction', [
                'transaction_id' => $this->currentTransaction->id,
                'document_id' => $this->currentTransaction->matched_paperless_document_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updatedType()
    {
        // Recalculate net/gross when type changes
        $this->recalculateAmounts();
    }

    public function updatedAmount()
    {
        // Recalculate net/gross when amount changes
        $this->recalculateAmounts();
    }

    public function updatedNetAmount()
    {
        // Recalculate VAT amount when net amount changes
        if ($this->net_amount !== null && $this->amount !== null) {
            $this->vat_amount = round($this->amount - $this->net_amount, 2);
        }
    }

    private function recalculateAmounts()
    {
        if ($this->amount === null) {
            return;
        }

        // Extract VAT rate from type
        $vatRate = 0;
        if (str_contains($this->type, '0%')) {
            $vatRate = 0;
        } elseif (str_contains($this->type, '7%')) {
            $vatRate = 7;
        } elseif (str_contains($this->type, '19%')) {
            $vatRate = 19;
        } elseif (str_contains($this->type, 'Reverse Charge')) {
            $vatRate = 0;
        }

        $this->vat_rate = $vatRate;

        // Calculate net amount (reverse calculation from gross)
        if ($vatRate > 0) {
            $this->net_amount = round($this->amount / (1 + ($vatRate / 100)), 2);
        } else {
            $this->net_amount = $this->amount;
        }

        // Calculate VAT amount
        $this->vat_amount = round($this->amount - $this->net_amount, 2);
    }

    public function approve()
    {
        $this->validate([
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'vat_amount' => 'required|numeric',
            'correspondent' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        if (! $this->currentTransaction) {
            return;
        }

        // Update transaction
        $this->currentTransaction->update([
            'type' => $this->type,
            'amount' => $this->amount,
            'net_amount' => $this->net_amount,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'correspondent' => $this->correspondent,
            'title' => $this->title,
            'description' => $this->description,
            'note' => $this->note,
            'is_validated' => true,
            'is_business_expense' => ! str_contains($this->type, 'Privat'),
        ]);

        $this->success = 'Transaktion erfolgreich validiert!';

        // Load next transaction
        $this->loadNextTransaction();
    }

    public function skip()
    {
        // Load next transaction without validating current one
        $this->loadNextTransaction();
    }

    public function autoFillFromAI()
    {
        $this->aiExtracting = true;
        $this->aiError = '';

        try {
            if (! $this->currentTransaction || ! $this->paperlessDocumentId) {
                $this->aiError = 'Kein Paperless-Dokument verfügbar für AI-Extraktion.';
                $this->aiExtracting = false;

                return;
            }

            // Download PDF from Paperless
            $paperlessService = app(\App\Services\PaperlessService::class);
            $pdfContent = $paperlessService->downloadDocument((int) $this->paperlessDocumentId);

            if (! $pdfContent) {
                $this->aiError = 'Fehler beim Herunterladen des Dokuments von Paperless.';
                $this->aiExtracting = false;

                return;
            }

            // Save to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'receipt_');
            file_put_contents($tempFile, $pdfContent);

            // Extract data using AI
            $visionService = app(\App\Services\AIVisionService::class);
            $extractedData = $visionService->extractReceiptData($tempFile);

            // Clean up temp file
            unlink($tempFile);

            if (! $extractedData) {
                $this->aiError = 'AI konnte keine Daten aus dem Dokument extrahieren. Bitte manuell ausfüllen.';
                $this->aiExtracting = false;

                return;
            }

            // Populate form fields with extracted data
            if (isset($extractedData['correspondent'])) {
                $this->correspondent = $extractedData['correspondent'];
            }

            if (isset($extractedData['description'])) {
                $this->description = $extractedData['description'];
            }

            if (isset($extractedData['type'])) {
                $this->type = $extractedData['type'];
            }

            if (isset($extractedData['amount'])) {
                $this->amount = $extractedData['amount'];
            }

            if (isset($extractedData['net_amount'])) {
                $this->net_amount = $extractedData['net_amount'];
            }

            if (isset($extractedData['vat_rate'])) {
                $this->vat_rate = $extractedData['vat_rate'];
            }

            if (isset($extractedData['vat_amount'])) {
                $this->vat_amount = $extractedData['vat_amount'];
            }

            $this->success = 'Daten wurden erfolgreich von AI extrahiert! Bitte überprüfen Sie die Werte.';

        } catch (\Exception $e) {
            \Log::error('AI extraction failed', [
                'transaction_id' => $this->currentTransaction?->id,
                'error' => $e->getMessage(),
            ]);

            $this->aiError = 'Fehler bei der AI-Extraktion: '.$e->getMessage();
        } finally {
            $this->aiExtracting = false;
        }
    }

    private function resetForm()
    {
        $this->transaction_id = null;
        $this->transaction_date = null;
        $this->correspondent = null;
        $this->title = null;
        $this->description = null;
        $this->type = null;
        $this->amount = null;
        $this->net_amount = null;
        $this->vat_rate = null;
        $this->vat_amount = null;
        $this->note = null;
    }

    public function render()
    {
        return view('livewire.transactions.verify-imports');
    }
}
