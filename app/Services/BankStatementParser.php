<?php

namespace App\Services;

use App\Models\BankTransaction;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class BankStatementParser
{
    private PdfParser $parser;

    public function __construct()
    {
        $this->parser = new PdfParser;
    }

    /**
     * Parse a bank statement PDF and extract transactions
     *
     * @param  string  $filePath  Path to the PDF file
     * @return array Parsed transactions
     */
    public function parsePdf(string $filePath): array
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();

            // Detect bank type and use appropriate parser
            if ($this->isSolarisBank($text)) {
                return $this->parseSolarisBankStatement($text);
            }

            // Add more bank parsers as needed
            // elseif ($this->isSparkasse($text)) {
            //     return $this->parseSparkasseStatement($text);
            // }

            // Fallback to generic parser
            return $this->parseGenericStatement($text);

        } catch (\Exception $e) {
            Log::error('Failed to parse bank statement PDF', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if the statement is from SolarisBank
     */
    private function isSolarisBank(string $text): bool
    {
        return str_contains($text, 'Solaris') ||
               str_contains($text, 'solarisBank') ||
               str_contains($text, 'SOBKDEBBXXX');
    }

    /**
     * Parse SolarisBank statement format
     */
    private function parseSolarisBankStatement(string $text): array
    {
        $transactions = [];
        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {
            // SolarisBank format: DATE DESCRIPTION AMOUNT
            // Example: "01.01.2024 SEPA-Lastschrift -150,00 EUR"
            if (preg_match('/^(\d{2}\.\d{2}\.\d{4})\s+(.+?)\s+([-+]?\d+[.,]\d{2})\s*€?/', $line, $matches)) {
                $date = \Carbon\Carbon::createFromFormat('d.m.Y', $matches[1]);
                $description = trim($matches[2]);
                $amount = (float) str_replace(',', '.', str_replace('.', '', $matches[3]));

                $transactions[] = [
                    'date' => $date->format('Y-m-d'),
                    'description' => $description,
                    'amount' => $amount,
                    'currency' => 'EUR',
                    'raw_data' => $line,
                ];
            }
        }

        return $transactions;
    }

    /**
     * Generic statement parser (fallback)
     */
    private function parseGenericStatement(string $text): array
    {
        $transactions = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Try to match common date and amount patterns
            if (preg_match('/(\d{2}[.\/]\d{2}[.\/]\d{4}).*?([-+]?\d+[.,]\d{2})/', $line, $matches)) {
                try {
                    // Try multiple date formats
                    $dateStr = $matches[1];
                    $date = null;

                    if (str_contains($dateStr, '.')) {
                        $date = \Carbon\Carbon::createFromFormat('d.m.Y', $dateStr);
                    } elseif (str_contains($dateStr, '/')) {
                        $date = \Carbon\Carbon::createFromFormat('d/m/Y', $dateStr);
                    }

                    if ($date) {
                        $amount = (float) str_replace(',', '.', str_replace('.', '', $matches[2]));

                        $transactions[] = [
                            'date' => $date->format('Y-m-d'),
                            'description' => trim($line),
                            'amount' => $amount,
                            'currency' => 'EUR',
                            'raw_data' => $line,
                        ];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $transactions;
    }

    /**
     * Import transactions from parsed data
     *
     * @param  array  $transactions  Parsed transaction data
     * @param  bool  $skipDuplicates  Skip transactions that already exist
     * @return array Statistics about the import
     */
    public function importTransactions(array $transactions, bool $skipDuplicates = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($transactions as $transactionData) {
            try {
                // Check for duplicates based on date, amount, and description
                if ($skipDuplicates) {
                    $exists = BankTransaction::where('transaction_date', $transactionData['date'])
                        ->where('amount', $transactionData['amount'])
                        ->where('description', 'like', '%'.substr($transactionData['description'], 0, 50).'%')
                        ->exists();

                    if ($exists) {
                        $skipped++;

                        continue;
                    }
                }

                $transaction = BankTransaction::create([
                    'transaction_date' => $transactionData['date'],
                    'correspondent' => $transactionData['correspondent'] ?? '',
                    'title' => $transactionData['title'] ?? '',
                    'description' => $transactionData['description'],
                    'type' => $transactionData['type'] ?? 'Nicht kategorisiert',
                    'amount' => $transactionData['amount'],
                    'currency' => $transactionData['currency'] ?? 'EUR',
                    'category' => $this->guessCategory($transactionData),
                    'is_business_expense' => $this->isBusinessExpense($transactionData),
                    'raw_data' => $transactionData['raw_data'] ?? null,
                ]);

                // Calculate and save net/gross breakdown
                $transaction->calculateNetGross();
                $transaction->save();

                $imported++;

            } catch (\Exception $e) {
                Log::error('Failed to import transaction', [
                    'transaction' => $transactionData,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => count($transactions),
        ];
    }

    /**
     * Guess the transaction category based on description
     */
    private function guessCategory(array $transaction): ?string
    {
        $description = strtolower($transaction['description']);

        $patterns = [
            'income' => ['gehalt', 'lohn', 'honorar', 'rechnung', 'invoice', 'payment received'],
            'rent' => ['miete', 'rent', 'kaution'],
            'utilities' => ['strom', 'gas', 'wasser', 'internet', 'telefon', 'handy', 'electricity'],
            'groceries' => ['rewe', 'edeka', 'aldi', 'lidl', 'supermarkt', 'grocery'],
            'transport' => ['bvg', 'db ', 'deutsche bahn', 'uber', 'taxi', 'tankstelle'],
            'insurance' => ['versicherung', 'insurance', 'krankenversicherung'],
            'subscription' => ['netflix', 'spotify', 'amazon prime', 'google', 'apple'],
            'tax' => ['finanzamt', 'steuer', 'tax'],
        ];

        foreach ($patterns as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * Determine if a transaction is likely a business expense
     */
    private function isBusinessExpense(array $transaction): bool
    {
        $description = strtolower($transaction['description']);
        $amount = $transaction['amount'];

        // Negative amounts (expenses)
        if ($amount >= 0) {
            return false;
        }

        // Business-related keywords
        $businessKeywords = [
            'hosting', 'domain', 'software', 'saas', 'cloud',
            'google workspace', 'microsoft', 'adobe', 'aws',
            'coworking', 'büro', 'office',
            'fortbildung', 'training', 'kurs', 'workshop',
            'fachbuch', 'professional book',
        ];

        foreach ($businessKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse and import bank statement in one go
     *
     * @param  string  $filePath  Path to the PDF file
     * @param  bool  $skipDuplicates  Skip duplicate transactions
     * @return array Import statistics
     */
    public function parseAndImport(string $filePath, bool $skipDuplicates = true): array
    {
        $transactions = $this->parsePdf($filePath);

        if (empty($transactions)) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => 0,
                'total' => 0,
                'message' => 'No transactions found in PDF',
            ];
        }

        return $this->importTransactions($transactions, $skipDuplicates);
    }
}
