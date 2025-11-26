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

    public $is_bewirtung = false;

    public $bewirtete_person;

    public $anlass;

    public $ort;

    public $transactionTypes = [];

    public $unvalidatedCount = 0;

    public $success = '';

    public $aiExtracting = false;

    public $aiError = '';

    public $paperlessDocumentId = null;

    public $paperlessDocumentUrl = null;

    public $paperlessDocumentTitle = null;

    public $correspondentSuggestions = [];

    public function mount()
    {
        $this->transactionTypes = BankTransaction::getTransactionTypes();
        $this->loadCorrespondentSuggestions();
        $this->loadNextTransaction();
    }

    public function loadCorrespondentSuggestions()
    {
        try {
            $paperlessService = app(\App\Services\PaperlessService::class);
            $this->correspondentSuggestions = $paperlessService->getCorrespondentNamesForAI();
        } catch (\Exception $e) {
            \Log::warning('Failed to load correspondent suggestions', ['error' => $e->getMessage()]);
            $this->correspondentSuggestions = [];
        }
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

        // Load Bewirtung data
        $this->is_bewirtung = $this->currentTransaction->is_bewirtung;
        $bewirtungData = $this->currentTransaction->bewirtung_data ?? [];
        $this->bewirtete_person = $bewirtungData['bewirtete_person'] ?? null;
        $this->anlass = $bewirtungData['anlass'] ?? null;
        $this->ort = $bewirtungData['ort'] ?? null;

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
        // Auto-set is_bewirtung when Bewirtung type is selected
        $this->is_bewirtung = $this->type === 'Bewirtung';

        // Extract and set VAT rate from type
        $this->extractVatRateFromType();

        // Recalculate gross from net when type changes
        $this->recalculateFromNet();
    }

    private function extractVatRateFromType()
    {
        // Extract VAT rate from type
        $vatRate = 0;
        if (str_contains($this->type, '0%')) {
            $vatRate = 0;
        } elseif (str_contains($this->type, '7%')) {
            $vatRate = 7;
        } elseif (str_contains($this->type, '19%')) {
            $vatRate = 19;
        } elseif ($this->type === 'Bewirtung') {
            $vatRate = 7; // Bewirtung uses 7% VAT
        } elseif (str_contains($this->type, 'Reverse Charge')) {
            $vatRate = 0;
        }

        $this->vat_rate = $vatRate;
    }

    public function updatedAmount()
    {
        // When gross amount is set (e.g., from AI extraction), calculate net
        // This is a one-time calculation, user edits net afterwards
        if ($this->amount !== null && $this->vat_rate !== null) {
            $this->recalculateNetFromGross();
        }
    }

    private function recalculateNetFromGross()
    {
        if ($this->amount === null) {
            return;
        }

        $amount = (float) $this->amount;
        $vatRate = (float) $this->vat_rate;

        // Calculate net amount (reverse calculation from gross)
        if ($vatRate > 0) {
            $this->net_amount = round($amount / (1 + ($vatRate / 100)), 2);
        } else {
            $this->net_amount = $amount;
        }

        // Calculate VAT amount
        $this->vat_amount = round($amount - (float) $this->net_amount, 2);
    }

    public function updatedNetAmount()
    {
        // Recalculate gross amount and VAT amount when net amount changes
        if ($this->net_amount !== null && $this->vat_rate !== null) {
            $netAmount = (float) $this->net_amount;
            $vatRate = (float) $this->vat_rate;

            // Calculate VAT amount
            $this->vat_amount = round($netAmount * ($vatRate / 100), 2);

            // Calculate gross amount
            $this->amount = round($netAmount + $this->vat_amount, 2);
        }
    }

    public function updatedVatRate()
    {
        // Recalculate amounts when VAT rate changes
        $this->recalculateFromNet();
    }

    private function recalculateFromNet()
    {
        // Calculate gross and VAT from net amount
        if ($this->net_amount !== null && $this->vat_rate !== null) {
            $netAmount = (float) $this->net_amount;
            $vatRate = (float) $this->vat_rate;

            // Calculate VAT amount
            $this->vat_amount = round($netAmount * ($vatRate / 100), 2);

            // Calculate gross amount
            $this->amount = round($netAmount + $this->vat_amount, 2);
        }
    }

    public function approve()
    {
        $rules = [
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'vat_amount' => 'required|numeric',
            'correspondent' => 'required|string|max:255',
        ];

        // Add Bewirtung validation when is_bewirtung is true
        if ($this->is_bewirtung) {
            $rules['anlass'] = 'required|string|max:500';
            $rules['ort'] = 'nullable|string|max:255';
            $rules['bewirtete_person'] = 'nullable|string|max:255';
        }

        $this->validate($rules);

        if (! $this->currentTransaction) {
            return;
        }

        // Prepare Bewirtung data
        $bewirtungData = null;
        if ($this->is_bewirtung) {
            $bewirtungData = [
                'bewirtete_person' => $this->bewirtete_person,
                'anlass' => $this->anlass,
                'ort' => $this->ort,
            ];
        }

        // Update transaction (cast numeric values to ensure proper types)
        $this->currentTransaction->update([
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'net_amount' => (float) $this->net_amount,
            'vat_rate' => (float) $this->vat_rate,
            'vat_amount' => (float) $this->vat_amount,
            'correspondent' => $this->correspondent,
            'title' => $this->title,
            'description' => $this->description,
            'is_bewirtung' => $this->is_bewirtung,
            'bewirtung_data' => $bewirtungData,
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
                // Get specific error message from service
                $errorMessage = $visionService->getLastError();

                if ($errorMessage) {
                    $this->aiError = $errorMessage;
                } else {
                    $this->aiError = 'AI konnte keine Daten aus dem Dokument extrahieren. Bitte manuell ausfüllen.';
                }

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

            // Extract gross amount and VAT rate - calculate net from these
            // Receipts show TOTAL amount (gross), we calculate net
            $grossAmount = isset($extractedData['amount_gross']) ? abs((float) $extractedData['amount_gross']) : null;
            $vatRate = isset($extractedData['vat_rate']) ? abs((float) $extractedData['vat_rate']) : null;

            if ($grossAmount !== null && $vatRate !== null) {
                // Calculate net from gross (source of truth on receipt)
                if ($vatRate > 0) {
                    $calculatedNet = round($grossAmount / (1 + ($vatRate / 100)), 2);
                } else {
                    $calculatedNet = $grossAmount;
                }

                \Log::info('AI extraction: calculated net from gross', [
                    'gross' => $grossAmount,
                    'vat_rate' => $vatRate,
                    'calculated_net' => $calculatedNet,
                ]);

                // Set net and VAT rate - reactive properties will calculate gross and VAT amount
                $this->net_amount = $calculatedNet;
                $this->vat_rate = $vatRate;

                // updatedNetAmount() will automatically calculate:
                // - vat_amount = net * (vat_rate / 100)
                // - amount (gross) = net + vat_amount
            } elseif ($grossAmount !== null) {
                // Only gross provided, assume 19% VAT
                $calculatedNet = round($grossAmount / 1.19, 2);
                $this->net_amount = $calculatedNet;
                $this->vat_rate = 19;
            } elseif ($vatRate !== null) {
                // Only VAT rate provided
                $this->vat_rate = $vatRate;
            }

            // Handle Bewirtung data from AI first (affects transaction type)
            if (isset($extractedData['is_bewirtung']) && $extractedData['is_bewirtung']) {
                $this->is_bewirtung = true;
                $this->bewirtete_person = $extractedData['bewirtete_person'] ?? null;
                $this->anlass = $extractedData['anlass'] ?? null;
                $this->ort = $extractedData['ort'] ?? null;
            }

            // Set transaction type - prioritize AI's suggestion, then Bewirtung, fallback to VAT rate
            if (isset($extractedData['transaction_type']) && ! empty($extractedData['transaction_type'])) {
                $this->type = $extractedData['transaction_type'];
            } elseif ($this->is_bewirtung) {
                // If AI detected Bewirtung but didn't set transaction type
                $this->type = 'Bewirtung';
            } elseif ($this->vat_rate !== null) {
                // Auto-set transaction type based on VAT rate if AI didn't provide one
                $vatRateInt = (int) round($this->vat_rate);
                switch ($vatRateInt) {
                    case 0:
                        $this->type = 'Geschäftsausgabe 0%';
                        break;
                    case 7:
                        $this->type = 'Geschäftsausgabe 7%';
                        break;
                    case 19:
                        $this->type = 'Geschäftsausgabe 19%';
                        break;
                    default:
                        // Fallback to 19% for unexpected rates
                        $this->type = 'Geschäftsausgabe 19%';
                        break;
                }
            }

            $this->success = 'Daten wurden erfolgreich von AI extrahiert! Bitte überprüfen Sie die Werte.';

        } catch (\Exception $e) {
            \Log::error('AI extraction failed', [
                'transaction_id' => $this->currentTransaction->id ?? null,
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
        $this->is_bewirtung = false;
        $this->bewirtete_person = null;
        $this->anlass = null;
        $this->ort = null;
    }

    public function render()
    {
        return view('livewire.transactions.verify-imports');
    }
}
