<?php

namespace App\Livewire\Accounting;

use App\Models\BankTransaction;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Monatliche Buchhaltung')]
class MonthlyView extends Component
{
    use WithFileUploads;

    public int $selectedYear;

    public int $selectedMonth;

    public $bankStatementFile;

    public $transactions = [];

    public $invoices = [];

    public $paperlessDocuments = [];

    public $hasBankStatement = false;

    public $success = '';

    public $error = '';

    public $uploadingStatement = false;

    // Linking modal
    public $showLinkingModal = false;

    public $linkingTransaction = null;

    public $availableDocuments = [];

    public $selectedDocumentId = null;

    // AI matching modal
    public $showAiMatchingModal = false;

    public $aiMatchingTransaction = null;

    public $aiSuggestedDocuments = [];

    public $aiSelectedDocumentId = null;

    public $aiProcessing = false;

    public $aiProcessingCancelled = false;

    public $linkingInProgress = false;

    public $searchKeyword = '';

    // Document from linker component
    public $selectedDocumentFromLinker = null;

    // Event listeners for PaperlessDocumentLinker component
    protected $listeners = [
        'documentSelected' => 'handleDocumentSelected',
        'documentCleared' => 'handleDocumentCleared',
    ];

    public function mount($year = null, $month = null)
    {
        // Get year and month from route parameters
        $this->selectedYear = $year ? (int) $year : (int) now()->format('Y');
        $this->selectedMonth = $month ? (int) $month : (int) now()->format('n');

        $this->loadMonthData();
    }

    public function loadMonthData()
    {
        // Clear previous data
        $this->transactions = [];
        $this->invoices = [];
        $this->paperlessDocuments = [];
        $this->hasBankStatement = false;

        $this->loadTransactions();
        $this->loadInvoices();
        $this->loadPaperlessDocuments();
        $this->checkBankStatement();
    }

    private function loadTransactions()
    {
        $this->transactions = BankTransaction::where('year', $this->selectedYear)
            ->where('month', $this->selectedMonth)
            ->orderBy('transaction_date', 'asc')
            ->get()
            ->toArray();
    }

    private function loadInvoices()
    {
        // Load invoices from the selected month
        $startDate = \Carbon\Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $this->invoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->with('customer')
            ->orderBy('issue_date', 'asc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer->name ?? 'Unknown',
                    'invoice_date' => $invoice->issue_date,
                    'total_gross' => $invoice->total ?? 0,
                    'type' => $invoice->type,
                ];
            })
            ->toArray();
    }

    private function loadPaperlessDocuments()
    {
        try {
            $paperlessService = app(\App\Services\PaperlessService::class);

            // Search for documents in this month (±7 day buffer)
            $startDate = \Carbon\Carbon::create($this->selectedYear, $this->selectedMonth, 1)
                ->subDays(7)
                ->format('Y-m-d');

            $endDate = \Carbon\Carbon::create($this->selectedYear, $this->selectedMonth, 1)
                ->endOfMonth()
                ->addDays(7)
                ->format('Y-m-d');

            // Get documents with Eingangsrechnung tag from this period
            $documents = $paperlessService->searchDocumentsByDateRange($startDate, $endDate, 'Eingangsrechnung');

            // Fetch all linked document IDs in a single query to avoid N+1
            $linkedDocumentIds = BankTransaction::where('year', $this->selectedYear)
                ->where('month', $this->selectedMonth)
                ->whereNotNull('paperless_document_id')
                ->pluck('paperless_document_id')
                ->flip(); // Convert to associative array for O(1) lookup

            $this->paperlessDocuments = collect($documents)->map(function ($doc) use ($linkedDocumentIds) {
                return [
                    'id' => $doc['id'],
                    'title' => $doc['title'],
                    'created' => $doc['created'],
                    'correspondent' => $doc['correspondent_name'] ?? 'Unknown',
                    'is_linked' => isset($linkedDocumentIds[$doc['id']]),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to load Paperless documents', ['error' => $e->getMessage()]);
            $this->paperlessDocuments = [];
        }
    }

    private function checkBankStatement()
    {
        // Check if there are any transactions for this month
        $this->hasBankStatement = BankTransaction::where('year', $this->selectedYear)
            ->where('month', $this->selectedMonth)
            ->exists();
    }

    public function uploadBankStatement()
    {
        $this->validate([
            'bankStatementFile' => 'required|mimes:pdf|max:10240', // 10MB max
        ]);

        $this->uploadingStatement = true;
        $this->error = '';

        try {
            // Store the PDF temporarily
            $path = $this->bankStatementFile->store('temp');

            // Use Storage facade to get the full path (works correctly in Docker)
            $fullPath = \Storage::path($path);

            \Log::info('Bank statement upload', [
                'path' => $path,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'storage_exists' => \Storage::exists($path),
            ]);

            // Parse the bank statement
            $bankStatementService = app(\App\Services\BankStatementParser::class);
            $transactions = $bankStatementService->parsePdf($fullPath);

            \Log::info('Parsed transactions from upload', [
                'count' => count($transactions),
            ]);

            if (empty($transactions)) {
                throw new \Exception('Keine Transaktionen im Kontoauszug gefunden.');
            }

            // Save transactions with month/year
            $saved = 0;
            foreach ($transactions as $transaction) {
                $date = \Carbon\Carbon::createFromFormat('d.m.y', $transaction['date']);

                BankTransaction::create([
                    'transaction_date' => $date,
                    'month' => $date->month,
                    'year' => $date->year,
                    'correspondent' => $transaction['correspondent'],
                    'title' => $transaction['title'] ?? '',
                    'description' => '',
                    'type' => $transaction['type'],
                    'amount' => $transaction['amount'],
                    'net_amount' => null,
                    'vat_rate' => null,
                    'vat_amount' => null,
                    'is_validated' => false,
                ]);
                $saved++;
            }

            // Clean up temporary file
            \Storage::delete($path);

            $this->success = "{$saved} Transaktionen wurden importiert.";
            $this->bankStatementFile = null;
            $this->loadMonthData();
        } catch (\Exception $e) {
            $this->error = 'Fehler beim Hochladen: '.$e->getMessage();
            \Log::error('Bank statement upload failed', ['error' => $e->getMessage()]);
        } finally {
            $this->uploadingStatement = false;
        }
    }

    public function openLinkingModal($transactionId)
    {
        $this->linkingTransaction = collect($this->transactions)->firstWhere('id', $transactionId);

        if (! $this->linkingTransaction) {
            return;
        }

        // Get unlinked documents
        $linkedDocumentIds = collect($this->transactions)
            ->whereNotNull('paperless_document_id')
            ->pluck('paperless_document_id')
            ->toArray();

        $this->availableDocuments = collect($this->paperlessDocuments)
            ->reject(fn ($doc) => in_array($doc['id'], $linkedDocumentIds))
            ->values()
            ->toArray();

        $this->selectedDocumentId = $this->linkingTransaction['paperless_document_id'] ?? null;
        $this->showLinkingModal = true;
    }

    public function handleDocumentSelected($documentId, $document)
    {
        $this->selectedDocumentId = $documentId;
        $this->selectedDocumentFromLinker = $document;
    }

    public function handleDocumentCleared()
    {
        $this->selectedDocumentId = null;
        $this->selectedDocumentFromLinker = null;
    }

    public function linkDocument()
    {
        if (! $this->linkingTransaction || ! $this->selectedDocumentId) {
            return;
        }

        $this->linkingInProgress = true;

        try {
            $transaction = BankTransaction::findOrFail($this->linkingTransaction['id']);

            // Use document from linker if available, otherwise fallback to paperlessDocuments
            $document = $this->selectedDocumentFromLinker ?? collect($this->paperlessDocuments)->firstWhere('id', (int) $this->selectedDocumentId);

            $transaction->update([
                'paperless_document_id' => $this->selectedDocumentId,
                'paperless_document_title' => $document['title'] ?? null,
            ]);

            $this->success = 'Dokument erfolgreich verknüpft!';
            $this->closeLinkingModal();
            $this->loadMonthData();
        } catch (\Exception $e) {
            $this->error = 'Fehler beim Verknüpfen: '.$e->getMessage();
        } finally {
            $this->linkingInProgress = false;
        }
    }

    public function unlinkDocument($transactionId)
    {
        try {
            $transaction = BankTransaction::findOrFail($transactionId);
            $transaction->update([
                'paperless_document_id' => null,
                'paperless_document_title' => null,
            ]);

            $this->success = 'Verknüpfung erfolgreich entfernt!';
            $this->loadMonthData();
        } catch (\Exception $e) {
            $this->error = 'Fehler beim Entfernen: '.$e->getMessage();
        }
    }

    public function closeLinkingModal()
    {
        $this->showLinkingModal = false;
        $this->linkingTransaction = null;
        $this->selectedDocumentId = null;
        $this->selectedDocumentFromLinker = null;
        $this->availableDocuments = [];
    }

    public function openAiMatchingModal()
    {
        // Get first unlinked transaction
        $unlinkedTransactions = collect($this->transactions)
            ->whereNull('paperless_document_id')
            ->where('type', '!=', 'Privat') // Skip private transactions
            ->where('type', '!=', 'Einkommen 19%') // Skip income
            ->values();

        if ($unlinkedTransactions->isEmpty()) {
            $this->error = 'Keine Transaktionen zum Verknüpfen verfügbar.';

            return;
        }

        $this->aiMatchingTransaction = $unlinkedTransactions->first();
        $this->aiProcessing = true;
        $this->showAiMatchingModal = true;

        // AI suggestions will be loaded via wire:init in the blade template
        // This allows the modal to open instantly while processing happens in background
    }

    public function getSuggestedDocuments()
    {
        $this->aiProcessing = true;
        $this->aiProcessingCancelled = false;
        $this->aiSuggestedDocuments = [];

        try {
            if (! $this->aiMatchingTransaction) {
                return;
            }

            // Check if cancelled before starting
            if ($this->aiProcessingCancelled) {
                return;
            }

            $paperlessService = app(\App\Services\PaperlessService::class);

            // Search for documents ±10 days from transaction date
            $transactionDate = \Carbon\Carbon::parse($this->aiMatchingTransaction['transaction_date']);
            $startDate = $transactionDate->copy()->subDays(10)->format('Y-m-d');
            $endDate = $transactionDate->copy()->addDays(10)->format('Y-m-d');

            // Search for incoming invoices (Eingangsrechnung)
            $documents = $paperlessService->searchDocumentsByDateRange($startDate, $endDate, 'Eingangsrechnung');

            // Check if cancelled after Paperless search
            if ($this->aiProcessingCancelled) {
                return;
            }

            if (empty($documents) && ! empty($this->searchKeyword)) {
                // Fallback: Search by keyword if provided
                $documents = $paperlessService->searchDocuments($this->searchKeyword);
            }

            // Check if cancelled before AI ranking
            if ($this->aiProcessingCancelled) {
                return;
            }

            if (! empty($documents)) {
                // Use AI to rank/suggest the best match
                $this->aiSuggestedDocuments = $this->rankDocumentsWithAI($documents);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to get AI suggestions', ['error' => $e->getMessage()]);
        } finally {
            $this->aiProcessing = false;
        }
    }

    private function rankDocumentsWithAI(array $documents): array
    {
        try {
            // Check if cancelled before starting AI ranking
            if ($this->aiProcessingCancelled) {
                \Log::info('AI ranking cancelled before starting');

                return collect($documents)->sortByDesc('created')->take(8)->values()->toArray();
            }

            $aiService = app(\App\Services\AIService::class);

            // Prepare transaction context
            $transactionContext = [
                'date' => $this->aiMatchingTransaction['transaction_date'],
                'correspondent' => $this->aiMatchingTransaction['correspondent'],
                'amount' => abs($this->aiMatchingTransaction['amount']),
                'description' => $this->aiMatchingTransaction['title'] ?? '',
            ];

            // Prepare documents list with FULL metadata from Paperless
            $documentsList = collect($documents)->map(function ($doc) {
                return [
                    'id' => $doc['id'],
                    'title' => $doc['title'],
                    'correspondent' => $doc['correspondent_name'] ?? 'Unknown',
                    'created' => $doc['created'],
                    'content' => ! empty($doc['content']) ? substr($doc['content'], 0, 500) : null, // First 500 chars of OCR content
                    'notes' => $doc['notes'] ?? null,
                    'tags' => $doc['tags'] ?? [],
                    'custom_fields' => $doc['custom_fields'] ?? [],
                    'archive_serial_number' => $doc['archive_serial_number'] ?? null,
                ];
            })->toArray();

            $prompt = "You are helping match a bank transaction to the correct invoice document.\n\n";
            $prompt .= "Transaction to match:\n";
            $prompt .= "- Date: {$transactionContext['date']}\n";
            $prompt .= "- Merchant/Correspondent: {$transactionContext['correspondent']}\n";
            $prompt .= '- Amount: €'.number_format($transactionContext['amount'], 2)."\n";
            $prompt .= "- Description: {$transactionContext['description']}\n\n";
            $prompt .= "Available invoice documents:\n\n";

            foreach ($documentsList as $doc) {
                $prompt .= "Document ID: {$doc['id']}\n";
                $prompt .= "  Title: {$doc['title']}\n";
                $prompt .= "  Correspondent: {$doc['correspondent']}\n";
                $prompt .= "  Date: {$doc['created']}\n";

                if (! empty($doc['archive_serial_number'])) {
                    $prompt .= "  Archive Number: {$doc['archive_serial_number']}\n";
                }

                if (! empty($doc['notes'])) {
                    $prompt .= '  Notes: '.substr($doc['notes'], 0, 200)."\n";
                }

                if (! empty($doc['content'])) {
                    $prompt .= '  Content Preview: '.substr($doc['content'], 0, 200)."...\n";
                }

                $prompt .= "\n";
            }

            $prompt .= "Analyze the transaction details (especially merchant name and amount) against each document's metadata.\n";
            $prompt .= "Consider:\n";
            $prompt .= "1. Merchant/correspondent name similarity\n";
            $prompt .= "2. Date proximity (transaction date vs document date)\n";
            $prompt .= "3. Any amount mentions in document content or notes\n";
            $prompt .= "4. Document title relevance\n\n";
            $prompt .= "Rank these documents by likelihood of being the correct invoice for this transaction.\n";
            $prompt .= 'Return ONLY a JSON array of document IDs in order of best match first. Example: [123, 456, 789]';
            $prompt .= "\nDo not include any explanation, just the JSON array.";

            // Note: Once the HTTP request starts, we cannot cancel it mid-flight in PHP
            // But we can check after it completes
            $response = $aiService->generateText($prompt, ['temperature' => 0.3]);

            // Check if cancelled after AI response (request completed)
            if ($this->aiProcessingCancelled) {
                \Log::info('AI ranking cancelled after response received');

                return collect($documents)->sortByDesc('created')->take(8)->values()->toArray();
            }

            // Try to parse the AI response as JSON
            if (preg_match('/\[([\d,\s]+)\]/', $response, $matches)) {
                $rankedIds = json_decode('['.trim($matches[1]).']', true);

                // Reorder documents based on AI ranking
                $ranked = [];
                foreach ($rankedIds as $id) {
                    foreach ($documents as $doc) {
                        if ($doc['id'] == $id) {
                            $ranked[] = $doc;
                            break;
                        }
                    }
                }

                // Add any documents not ranked by AI at the end
                foreach ($documents as $doc) {
                    if (! in_array($doc['id'], $rankedIds)) {
                        $ranked[] = $doc;
                    }
                }

                // Limit to 8 suggestions max
                return array_slice($ranked, 0, 8);
            }

            // Fallback: return documents as-is, limited to 8
            return array_slice($documents, 0, 8);

        } catch (\Exception $e) {
            \Log::error('AI ranking failed', ['error' => $e->getMessage()]);

            // Fallback: return documents sorted by date, limited to 8
            return collect($documents)->sortByDesc('created')->take(8)->values()->toArray();
        }
    }

    public function linkAiDocument()
    {
        if (! $this->aiMatchingTransaction || ! $this->aiSelectedDocumentId) {
            return;
        }

        // Set loading state
        $this->linkingInProgress = true;

        try {
            $transaction = BankTransaction::findOrFail($this->aiMatchingTransaction['id']);

            // Get document details
            $document = collect($this->aiSuggestedDocuments)->firstWhere('id', (int) $this->aiSelectedDocumentId);

            $transaction->update([
                'paperless_document_id' => $this->aiSelectedDocumentId,
                'paperless_document_title' => $document['title'] ?? null,
            ]);

            $this->success = 'Dokument erfolgreich verknüpft!';

            // Check if there are more unlinked transactions
            $unlinkedTransactions = collect($this->transactions)
                ->whereNull('paperless_document_id')
                ->where('type', '!=', 'Privat')
                ->where('type', '!=', 'Einkommen 19%')
                ->values();

            // Reload data to get updated transaction list
            $this->loadMonthData();

            // Get fresh unlinked transactions after reload
            $unlinkedTransactions = collect($this->transactions)
                ->whereNull('paperless_document_id')
                ->where('type', '!=', 'Privat')
                ->where('type', '!=', 'Einkommen 19%')
                ->values();

            if ($unlinkedTransactions->isNotEmpty()) {
                // Move to next unlinked transaction
                $this->aiMatchingTransaction = $unlinkedTransactions->first();
                $this->aiSelectedDocumentId = null;
                $this->aiSuggestedDocuments = [];
                $this->aiProcessing = true;
                $this->getSuggestedDocuments();
            } else {
                // No more transactions to link
                $this->closeAiMatchingModal();
            }
        } catch (\Exception $e) {
            $this->error = 'Fehler beim Verknüpfen: '.$e->getMessage();
            $this->linkingInProgress = false;
        } finally {
            // Reset loading state
            $this->linkingInProgress = false;
        }
    }

    public function skipAiTransaction()
    {
        // Cancel any ongoing AI processing
        $this->aiProcessingCancelled = true;

        // Move to next unlinked transaction
        $currentIndex = collect($this->transactions)->search(function ($t) {
            return $t['id'] === $this->aiMatchingTransaction['id'];
        });

        $unlinkedTransactions = collect($this->transactions)
            ->whereNull('paperless_document_id')
            ->where('type', '!=', 'Privat')
            ->where('type', '!=', 'Einkommen 19%')
            ->values();

        $nextTransaction = $unlinkedTransactions->skip($currentIndex + 1)->first();

        if ($nextTransaction) {
            $this->aiMatchingTransaction = $nextTransaction;
            $this->aiSelectedDocumentId = null;
            $this->aiSuggestedDocuments = [];
            $this->aiProcessing = true;
            $this->aiProcessingCancelled = false; // Reset cancellation for new transaction
            // Trigger AI suggestions loading
            $this->getSuggestedDocuments();
        } else {
            $this->closeAiMatchingModal();
        }
    }

    public function closeAiMatchingModal()
    {
        // Cancel any ongoing AI processing
        $this->aiProcessingCancelled = true;

        $this->showAiMatchingModal = false;
        $this->aiMatchingTransaction = null;
        $this->aiSuggestedDocuments = [];
        $this->aiSelectedDocumentId = null;
        $this->searchKeyword = '';
        $this->aiProcessing = false;
    }

    public function deleteTransaction($transactionId)
    {
        try {
            BankTransaction::findOrFail($transactionId)->delete();
            $this->success = 'Transaktion gelöscht!';
            $this->loadMonthData();
        } catch (\Exception $e) {
            $this->error = 'Fehler beim Löschen: '.$e->getMessage();
        }
    }

    public function getMonthName(): string
    {
        return \Carbon\Carbon::create($this->selectedYear, $this->selectedMonth, 1)->locale('de')->monthName;
    }

    public function render()
    {
        return view('livewire.accounting.monthly-view', [
            'monthName' => $this->getMonthName(),
            'transactionCount' => count($this->transactions),
            'invoiceCount' => count($this->invoices),
            'documentCount' => count($this->paperlessDocuments),
            'linkedCount' => collect($this->transactions)->whereNotNull('paperless_document_id')->count(),
        ]);
    }
}
