<?php

namespace App\Livewire\Accounting;

use App\Models\BankTransaction;
use App\Models\CashReceipt;
use App\Models\Invoice;
use App\Services\BankStatementParser;
use App\Services\DocumentParser;
use App\Services\InvoiceMatchingService;
use App\Services\PaperlessService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Buchhaltung')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $bankStatement;

    public string $search = '';

    public ?string $categoryFilter = null;

    public ?bool $validatedFilter = null;

    public bool $showUploadModal = false;

    public bool $showPaperlessModal = false;

    public array $paperlessDocuments = [];

    public array $selectedDocuments = [];

    public bool $isLoadingPaperless = false;

    public array $allPaperlessTags = [];

    public string $importMonth;

    public function mount()
    {
        $this->importMonth = now()->format('Y-m');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleUploadModal()
    {
        $this->showUploadModal = ! $this->showUploadModal;
        $this->bankStatement = null;
    }

    public function uploadBankStatement()
    {
        $this->validate([
            'bankStatement' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        try {
            $parser = app(BankStatementParser::class);
            $path = $this->bankStatement->getRealPath();

            $result = $parser->parseAndImport($path, skipDuplicates: true);

            session()->flash('success', sprintf(
                '%d Transaktionen importiert, %d übersprungen (Duplikate), %d Fehler',
                $result['imported'],
                $result['skipped'],
                $result['errors']
            ));

            $this->showUploadModal = false;
            $this->bankStatement = null;

        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Importieren: '.$e->getMessage());
        }
    }

    public function markAsValidated(int $transactionId, bool $validated)
    {
        $transaction = BankTransaction::findOrFail($transactionId);
        $transaction->update(['is_validated' => $validated]);

        session()->flash('success', 'Transaktion aktualisiert.');
    }

    public function updateNote(int $transactionId, string $note)
    {
        $transaction = BankTransaction::findOrFail($transactionId);
        $transaction->update(['notes' => $note ?: null]);
    }

    public function findMatches(int $transactionId)
    {
        $transaction = BankTransaction::findOrFail($transactionId);
        $matcher = app(InvoiceMatchingService::class);

        $matches = $matcher->findMatchingInvoices($transaction);

        if (empty($matches)) {
            session()->flash('info', 'Keine passenden Rechnungen gefunden.');
        } else {
            session()->flash('success', sprintf('%d mögliche Übereinstimmung(en) gefunden.', count($matches)));
        }

        // Store matches in session for display
        session()->put('transaction_matches_'.$transactionId, $matches);
    }

    public function linkToInvoice(int $transactionId, int $invoiceId)
    {
        $transaction = BankTransaction::findOrFail($transactionId);
        $invoice = Invoice::findOrFail($invoiceId);
        $matcher = app(InvoiceMatchingService::class);

        if ($matcher->linkTransactionToInvoice($transaction, $invoice)) {
            session()->flash('success', 'Transaktion erfolgreich mit Rechnung verknüpft.');
        } else {
            session()->flash('error', 'Fehler beim Verknüpfen der Transaktion.');
        }
    }

    public function togglePaperlessModal()
    {
        $this->showPaperlessModal = ! $this->showPaperlessModal;

        if ($this->showPaperlessModal) {
            $this->loadPaperlessDocuments();
        }
    }

    public function loadPaperlessDocuments()
    {
        $this->isLoadingPaperless = true;
        $this->paperlessDocuments = [];

        try {
            $paperless = app(PaperlessService::class);

            // Pre-load all tags to avoid N+1 query problem in the view
            $this->allPaperlessTags = $paperless->getTags();

            $startDate = now()->parse($this->importMonth)->startOfMonth();
            $endDate = now()->parse($this->importMonth)->endOfMonth();

            // Get expense documents from Paperless for the selected month
            $documents = $paperless->getExpenseDocuments([
                'date_after' => $startDate->format('Y-m-d'),
                'date_before' => $endDate->format('Y-m-d'),
            ]);

            // Filter out documents already imported (check all time, not just selected month)
            $importedDocumentIds = BankTransaction::whereNotNull('matched_paperless_document_id')
                ->pluck('matched_paperless_document_id')
                ->toArray();

            $cashReceiptIds = CashReceipt::whereNotNull('paperless_document_id')
                ->pluck('paperless_document_id')
                ->toArray();

            $alreadyImported = array_merge($importedDocumentIds, $cashReceiptIds);

            $this->paperlessDocuments = array_filter($documents, function ($doc) use ($alreadyImported) {
                return ! in_array($doc['id'], $alreadyImported);
            });

            $this->paperlessDocuments = array_values($this->paperlessDocuments); // Re-index array

            if (empty($this->paperlessDocuments)) {
                session()->flash('info', 'Keine neuen Belege in Paperless gefunden.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Laden der Paperless-Dokumente: '.$e->getMessage());
        }

        $this->isLoadingPaperless = false;
    }

    public function toggleDocumentSelection(int $documentId)
    {
        if (in_array($documentId, $this->selectedDocuments)) {
            $this->selectedDocuments = array_values(array_filter(
                $this->selectedDocuments,
                fn ($id) => $id !== $documentId
            ));
        } else {
            $this->selectedDocuments[] = $documentId;
        }
    }

    public function importSelectedDocuments()
    {
        if (empty($this->selectedDocuments)) {
            session()->flash('error', 'Bitte wählen Sie mindestens ein Dokument aus.');

            return;
        }

        $paperless = app(PaperlessService::class);
        $parser = app(DocumentParser::class);

        $imported = 0;
        $errors = 0;

        foreach ($this->selectedDocuments as $docId) {
            try {
                // Download document from Paperless
                $pdfContent = $paperless->downloadDocument($docId);

                if (! $pdfContent) {
                    $errors++;

                    continue;
                }

                // Save to temp file
                $tempPath = storage_path('app/temp/paperless_import_'.$docId.'.pdf');
                if (! file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }
                file_put_contents($tempPath, $pdfContent);

                // Parse document
                $parsedData = $parser->parseDocument($tempPath);

                // Get document metadata from Paperless
                $docMeta = $paperless->getDocument($docId);

                // Create expense record based on tags
                $tags = $docMeta['tags'] ?? [];
                $tagNames = array_map(function ($tagId) {
                    foreach ($this->allPaperlessTags as $tag) {
                        if ($tag['id'] === $tagId) {
                            return $tag['name'];
                        }
                    }

                    return null;
                }, $tags);

                // Determine if this is a cash receipt or bank transaction
                $isCashReceipt = in_array('Barbeleg', $tagNames);

                if ($isCashReceipt) {
                    // Create cash receipt
                    $cashReceipt = CashReceipt::create([
                        'receipt_date' => $parsedData['date'] ?? now(),
                        'correspondent' => $parsedData['correspondent'] ?? '',
                        'description' => $docMeta['title'] ?? 'Imported from Paperless',
                        'amount' => abs($parsedData['total'] ?? 0),
                        'category' => $parsedData['category'] ?? 'Sonstiges',
                        'note' => $parsedData['notes'] ?? null,
                        'paperless_document_id' => $docId,
                    ]);

                    // Calculate and save net/gross breakdown
                    $cashReceipt->calculateNetGross();
                    $cashReceipt->save();
                } else {
                    // Create bank transaction (expense)
                    $transaction = BankTransaction::create([
                        'transaction_date' => $parsedData['date'] ?? now(),
                        'correspondent' => $parsedData['correspondent'] ?? '',
                        'title' => $docMeta['title'] ?? '',
                        'description' => $docMeta['title'] ?? 'Imported from Paperless',
                        'type' => $parsedData['type'] ?? 'Geschäftsausgabe 19%',
                        'amount' => -abs($parsedData['total'] ?? 0), // Negative for expenses
                        'category' => $parsedData['category'] ?? 'Sonstiges',
                        'note' => $parsedData['notes'] ?? null,
                        'is_validated' => false,
                        'is_business_expense' => true,
                        'matched_paperless_document_id' => $docId,
                    ]);

                    // Calculate and save net/gross breakdown
                    $transaction->calculateNetGross();
                    $transaction->save();
                }

                $imported++;

                // Clean up temp file
                unlink($tempPath);

            } catch (\Exception $e) {
                \Log::error('Paperless import error', [
                    'document_id' => $docId,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        session()->flash('success', sprintf(
            '%d Belege importiert, %d Fehler',
            $imported,
            $errors
        ));

        $this->selectedDocuments = [];
        $this->showPaperlessModal = false;
        $this->loadPaperlessDocuments();
    }

    public function render()
    {
        $transactions = BankTransaction::query()
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%');
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->validatedFilter !== null, function ($query) {
                $query->where('is_validated', $this->validatedFilter);
            })
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        $categories = BankTransaction::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        $stats = [
            'total_transactions' => BankTransaction::count(),
            'validated' => BankTransaction::where('is_validated', true)->count(),
            'pending' => BankTransaction::where('is_validated', false)->count(),
            'total_income' => BankTransaction::where('amount', '>', 0)->sum('amount'),
            'total_expenses' => BankTransaction::where('amount', '<', 0)->sum('amount'),
        ];

        return view('livewire.accounting.index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
