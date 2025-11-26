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
            // Check if file exists
            if (! file_exists($filePath)) {
                Log::error('Bank statement PDF file not found', [
                    'file' => $filePath,
                ]);

                return [];
            }

            Log::info('Parsing bank statement PDF', [
                'file' => $filePath,
                'file_size' => filesize($filePath),
            ]);

            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();

            Log::info('Extracted text from PDF', [
                'text_length' => strlen($text),
            ]);

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
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Parse German-formatted amount string to float
     * Handles formats like: -8.456,00 or +1.234,56 or 123,45
     */
    private function parseAmount(string $amountStr): float
    {
        // Remove thousands separators (dots)
        $cleaned = str_replace('.', '', $amountStr);
        // Replace decimal comma with dot
        $cleaned = str_replace(',', '.', $cleaned);

        // Convert to float
        return (float) $cleaned;
    }

    /**
     * Check if the statement is from SolarisBank/Kontist
     */
    private function isSolarisBank(string $text): bool
    {
        return str_contains($text, 'Kontist') ||
               str_contains($text, 'Solaris') ||
               str_contains($text, 'solarisBank') ||
               str_contains($text, 'SOBKDEBBXXX');
    }

    /**
     * Parse SolarisBank/Kontist statement format
     */
    private function parseSolarisBankStatement(string $text): array
    {
        $transactions = [];
        $lines = explode("\n", $text);

        Log::info('Parsing Kontist statement', ['total_lines' => count($lines)]);

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            // Match date format DD.MM.YY (e.g., "01.09.25")
            if (preg_match('/^(\d{2}\.\d{2}\.\d{2})\s+(.+)$/', $line, $matches)) {
                $dateStr = $matches[1];
                $restOfLine = trim($matches[2]);

                // Parse date with 2-digit year
                try {
                    $date = \Carbon\Carbon::createFromFormat('d.m.y', $dateStr);
                } catch (\Exception $e) {
                    Log::warning('Failed to parse date', ['date' => $dateStr]);

                    continue;
                }

                // Look ahead for correspondent name and type
                $correspondent = '';
                $type = '';
                $title = '';
                $amount = null;

                // Extract correspondent from the rest of the line
                // It might contain the amount at the end
                // Match amounts with optional thousands separators: -1.234,56 or +1234,56
                // ONLY match if it has an explicit +/- sign
                if (preg_match('/^(.+?)\s+([-+]\d{1,3}(?:\.\d{3})*,\d{2})\s*EUR$/', $restOfLine, $amountMatch)) {
                    // Amount is on the same line
                    $correspondent = trim($amountMatch[1]);
                    $amountStr = $amountMatch[2];
                    $amount = $this->parseAmount($amountStr);
                } else {
                    // Correspondent name is the rest of the line
                    $correspondent = $restOfLine;
                }

                // Look at next lines for type and amount if not found yet
                $j = $i + 1;
                while ($j < count($lines) && $j < $i + 10) {
                    $nextLine = trim($lines[$j]);

                    // Check if this is a type line (contains transaction category)
                    if (preg_match('/^(Geschäftsausgabe|Einkommen|Privat|Reverse Charge|Steuerzahlung|Umsatzsteuerstattung|Nicht kategorisiert|Bewirtung)/', $nextLine)) {
                        $type = $nextLine;
                        $j++;

                        continue;
                    }

                    // Check if this line contains amount (with optional thousands separators)
                    // ONLY match amounts with explicit +/- sign (the actual transaction amount)
                    // Amounts without signs are just reference amounts in descriptions, not the real amount
                    if ($amount === null && preg_match('/([-+]\d{1,3}(?:\.\d{3})*,\d{2})\s*EUR/', $nextLine, $amountMatch)) {
                        $amountStr = $amountMatch[1];
                        $amount = $this->parseAmount($amountStr);
                    }

                    // Check if this is additional transaction info (Lastschrift, Kartenzahlung, Überweisung, etc.)
                    if (preg_match('/^(Lastschrift|Kartenzahlung|Überweisung|Echtzeitüberweisung)/', $nextLine)) {
                        if (empty($title)) {
                            $title = $nextLine;
                        }
                    }

                    // Stop if we hit the next date line
                    if (preg_match('/^\d{2}\.\d{2}\.\d{2}\s+/', $nextLine)) {
                        break;
                    }

                    $j++;
                }

                // Only add if we found an amount
                if ($amount !== null) {
                    $transaction = [
                        'date' => $date->format('d.m.y'),
                        'correspondent' => $correspondent,
                        'title' => $title,
                        'type' => $type ?: 'Nicht kategorisiert',
                        'amount' => $amount,
                        'currency' => 'EUR',
                        'raw_data' => $line,
                    ];

                    $transactions[] = $transaction;

                    Log::info('Parsed transaction', $transaction);
                }

                // Skip the lines we already processed
                $i = $j - 1;
            }
        }

        Log::info('Parsed transactions count', ['count' => count($transactions)]);

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
