<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceMatchingService
{
    private PaperlessService $paperless;

    public function __construct(PaperlessService $paperless)
    {
        $this->paperless = $paperless;
    }

    /**
     * Find matching invoices for a bank transaction
     *
     * @return array Array of potential matches with scores
     */
    public function findMatchingInvoices(BankTransaction $transaction): array
    {
        $matches = [];

        // Only match positive amounts (income)
        if ($transaction->amount <= 0) {
            return $matches;
        }

        // Search local database first
        $localMatches = $this->searchLocalInvoices($transaction);
        $matches = array_merge($matches, $localMatches);

        // Search Paperless for additional matches
        $paperlessMatches = $this->searchPaperlessInvoices($transaction);
        $matches = array_merge($matches, $paperlessMatches);

        // Sort by match score (descending)
        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $matches;
    }

    /**
     * Search local database for matching invoices
     */
    private function searchLocalInvoices(BankTransaction $transaction): array
    {
        $matches = [];
        $amount = $transaction->amount;
        $date = \Carbon\Carbon::parse($transaction->transaction_date);

        // Search invoices with similar amounts within +/- 30 days
        $invoices = Invoice::with('customer')
            ->whereBetween('total', [$amount - 1, $amount + 1])
            ->whereBetween('issue_date', [
                $date->copy()->subDays(30),
                $date->copy()->addDays(30),
            ])
            ->get();

        foreach ($invoices as $invoice) {
            $score = $this->calculateMatchScore($transaction, $invoice);

            if ($score > 0) {
                $matches[] = [
                    'type' => 'local',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer' => $invoice->customer->name,
                    'amount' => $invoice->total,
                    'date' => $invoice->issue_date->format('d.m.Y'),
                    'score' => $score,
                ];
            }
        }

        return $matches;
    }

    /**
     * Search Paperless for matching invoice documents
     */
    private function searchPaperlessInvoices(BankTransaction $transaction): array
    {
        $matches = [];

        try {
            // Extract potential customer/company names from transaction description
            $searchTerms = $this->extractSearchTerms($transaction->description);

            foreach ($searchTerms as $term) {
                $documents = $this->paperless->searchDocuments($term);

                foreach ($documents as $document) {
                    // Check if document title contains "Rechnung" or "Invoice"
                    $title = $document['title'] ?? '';
                    if (! preg_match('/(rechnung|invoice)/i', $title)) {
                        continue;
                    }

                    $score = $this->calculatePaperlessMatchScore($transaction, $document);

                    if ($score > 30) {  // Minimum score threshold
                        $matches[] = [
                            'type' => 'paperless',
                            'document_id' => $document['id'],
                            'title' => $title,
                            'date' => $document['created'] ?? null,
                            'score' => $score,
                            'paperless_url' => config('services.paperless.url').'/documents/'.$document['id'],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Paperless search failed during invoice matching', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $matches;
    }

    /**
     * Calculate match score between transaction and local invoice
     */
    private function calculateMatchScore(BankTransaction $transaction, Invoice $invoice): int
    {
        $score = 0;

        // Exact amount match: +50 points
        if (abs($transaction->amount - $invoice->total) < 0.01) {
            $score += 50;
        }
        // Similar amount (within €1): +30 points
        elseif (abs($transaction->amount - $invoice->total) < 1) {
            $score += 30;
        }
        // Similar amount (within €5): +15 points
        elseif (abs($transaction->amount - $invoice->total) < 5) {
            $score += 15;
        }

        // Date proximity: up to +30 points
        $dateDiff = abs(
            $transaction->transaction_date->diffInDays($invoice->issue_date)
        );
        if ($dateDiff <= 3) {
            $score += 30;
        } elseif ($dateDiff <= 7) {
            $score += 20;
        } elseif ($dateDiff <= 14) {
            $score += 10;
        } elseif ($dateDiff <= 30) {
            $score += 5;
        }

        // Customer name in description: +20 points
        if (str_contains(
            strtolower($transaction->description),
            strtolower($invoice->customer->name)
        )) {
            $score += 20;
        }

        return $score;
    }

    /**
     * Calculate match score for Paperless document
     */
    private function calculatePaperlessMatchScore(
        BankTransaction $transaction,
        array $document
    ): int {
        $score = 0;
        $description = strtolower($transaction->description);
        $title = strtolower($document['title'] ?? '');

        // Extract words from both strings
        $descWords = preg_split('/\s+/', $description);
        $titleWords = preg_split('/\s+/', $title);

        // Count matching words: +5 points per match
        $matchingWords = array_intersect($descWords, $titleWords);
        $score += count($matchingWords) * 5;

        // Title contains "Rechnung": +10 points
        if (str_contains($title, 'rechnung') || str_contains($title, 'invoice')) {
            $score += 10;
        }

        // Date proximity if available: up to +20 points
        if (isset($document['created'])) {
            $docDate = \Carbon\Carbon::parse($document['created']);
            $dateDiff = abs($transaction->transaction_date->diffInDays($docDate));

            if ($dateDiff <= 7) {
                $score += 20;
            } elseif ($dateDiff <= 14) {
                $score += 10;
            } elseif ($dateDiff <= 30) {
                $score += 5;
            }
        }

        return $score;
    }

    /**
     * Extract potential search terms from transaction description
     */
    private function extractSearchTerms(string $description): array
    {
        // Remove common banking terms
        $cleanDesc = preg_replace(
            '/(SEPA|Lastschrift|Überweisung|Transfer|Payment|EUR)/i',
            '',
            $description
        );

        // Split into words and filter
        $words = preg_split('/\s+/', trim($cleanDesc));
        $terms = [];

        foreach ($words as $word) {
            // Only use words with 3+ characters
            if (strlen($word) >= 3) {
                $terms[] = $word;
            }
        }

        // Also try the full cleaned description
        if (! empty(trim($cleanDesc))) {
            $terms[] = trim($cleanDesc);
        }

        return array_unique($terms);
    }

    /**
     * Automatically link transaction to invoice
     */
    public function linkTransactionToInvoice(
        BankTransaction $transaction,
        Invoice $invoice
    ): bool {
        try {
            $transaction->update([
                'invoice_id' => $invoice->id,
                'is_validated' => true,
                'notes' => ($transaction->notes ? $transaction->notes.' | ' : '')
                    ."Verknüpft mit Rechnung {$invoice->invoice_number}",
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to link transaction to invoice', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Find and suggest matches for unvalidated transactions
     *
     * @param  int  $limit  Maximum number of transactions to process
     * @return array Summary of matches found
     */
    public function findMatchesForUnvalidatedTransactions(int $limit = 50): array
    {
        $summary = [
            'processed' => 0,
            'matches_found' => 0,
            'auto_linked' => 0,
        ];

        $transactions = BankTransaction::where('is_validated', false)
            ->where('amount', '>', 0)  // Only income
            ->whereNull('invoice_id')
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get();

        foreach ($transactions as $transaction) {
            $matches = $this->findMatchingInvoices($transaction);
            $summary['processed']++;

            if (! empty($matches)) {
                $summary['matches_found']++;

                // Auto-link if there's a high-confidence match (score >= 80)
                $bestMatch = $matches[0];
                if ($bestMatch['score'] >= 80 && $bestMatch['type'] === 'local') {
                    $invoice = Invoice::find($bestMatch['invoice_id']);
                    if ($invoice && $this->linkTransactionToInvoice($transaction, $invoice)) {
                        $summary['auto_linked']++;
                    }
                }
            }
        }

        return $summary;
    }
}
