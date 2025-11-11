<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentParser
{
    private PdfParser $parser;

    public function __construct()
    {
        $this->parser = new PdfParser;
    }

    /**
     * Parse a document PDF and extract invoice/quote data
     *
     * @param  string  $filePath  Path to the PDF file
     * @return array Parsed document data
     */
    public function parseDocument(string $filePath): array
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();

            // Detect document type
            $documentType = $this->detectDocumentType($text);

            if ($documentType === 'invoice') {
                return $this->parseInvoice($text);
            } elseif ($documentType === 'quote') {
                return $this->parseQuote($text);
            } else {
                // Try to parse as generic document
                return $this->parseGenericDocument($text);
            }

        } catch (\Exception $e) {
            Log::error('Failed to parse document PDF', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'Failed to parse PDF: '.$e->getMessage()];
        }
    }

    /**
     * Detect if document is invoice or quote
     */
    private function detectDocumentType(string $text): string
    {
        $lowerText = strtolower($text);

        // Check for invoice indicators
        $invoiceIndicators = ['rechnung', 'invoice', 'bill', 'faktura'];
        foreach ($invoiceIndicators as $indicator) {
            if (str_contains($lowerText, $indicator)) {
                return 'invoice';
            }
        }

        // Check for quote indicators
        $quoteIndicators = ['angebot', 'quote', 'kostenvoranschlag', 'estimate', 'proposal'];
        foreach ($quoteIndicators as $indicator) {
            if (str_contains($lowerText, $indicator)) {
                return 'quote';
            }
        }

        return 'unknown';
    }

    /**
     * Parse invoice document
     */
    private function parseInvoice(string $text): array
    {
        $data = [
            'type' => 'invoice',
            'invoice_number' => $this->extractInvoiceNumber($text),
            'customer_data' => $this->extractCustomerData($text),
            'issue_date' => $this->extractIssueDate($text),
            'due_date' => $this->extractDueDate($text),
            'service_period_start' => $this->extractServicePeriodStart($text),
            'service_period_end' => $this->extractServicePeriodEnd($text),
            'service_location' => $this->extractServiceLocation($text),
            'project_name' => $this->extractProjectName($text),
            'items' => $this->extractItems($text),
            'subtotal' => $this->extractSubtotal($text),
            'vat_rate' => $this->extractVatRate($text),
            'vat_amount' => $this->extractVatAmount($text),
            'total' => $this->extractTotal($text),
            'notes' => $this->extractNotes($text),
        ];

        // Determine if this is a project invoice
        $data['is_project_invoice'] = ! empty($data['project_name']) ||
                                    ! empty($data['service_period_start']) ||
                                    ! empty($data['service_location']);

        return $data;
    }

    /**
     * Parse quote document
     */
    private function parseQuote(string $text): array
    {
        return [
            'type' => 'quote',
            'quote_number' => $this->extractQuoteNumber($text),
            'customer_data' => $this->extractCustomerData($text),
            'issue_date' => $this->extractIssueDate($text),
            'valid_until' => $this->extractValidUntil($text),
            'project_name' => $this->extractProjectName($text),
            'items' => $this->extractItems($text),
            'subtotal' => $this->extractSubtotal($text),
            'vat_rate' => $this->extractVatRate($text),
            'vat_amount' => $this->extractVatAmount($text),
            'total' => $this->extractTotal($text),
            'notes' => $this->extractNotes($text),
        ];
    }

    /**
     * Parse generic document (fallback)
     */
    private function parseGenericDocument(string $text): array
    {
        return [
            'type' => 'unknown',
            'document_number' => $this->extractDocumentNumber($text),
            'customer_data' => $this->extractCustomerData($text),
            'issue_date' => $this->extractIssueDate($text),
            'total' => $this->extractTotal($text),
            'raw_text' => substr($text, 0, 1000), // First 1000 chars for reference
        ];
    }

    /**
     * Extract invoice number
     */
    private function extractInvoiceNumber(string $text): ?string
    {
        // Common invoice number patterns
        $patterns = [
            '/Rechnungsnummer[:\s]+([A-Z0-9-]+)/i',
            '/Rechnung Nr\.[:\s]+([A-Z0-9-]+)/i',
            '/Invoice No\.[:\s]+([A-Z0-9-]+)/i',
            '/Invoice Number[:\s]+([A-Z0-9-]+)/i',
            '/Nr\.[:\s]+([A-Z0-9-]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract quote number
     */
    private function extractQuoteNumber(string $text): ?string
    {
        // Common quote number patterns
        $patterns = [
            '/Angebotsnummer[:\s]+([A-Z0-9-]+)/i',
            '/Angebot Nr\.[:\s]+([A-Z0-9-]+)/i',
            '/Quote No\.[:\s]+([A-Z0-9-]+)/i',
            '/Quote Number[:\s]+([A-Z0-9-]+)/i',
            '/Kostenvoranschlag[:\s]+([A-Z0-9-]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract generic document number
     */
    private function extractDocumentNumber(string $text): ?string
    {
        // Generic document number patterns
        $patterns = [
            '/Dokument Nr\.[:\s]+([A-Z0-9-]+)/i',
            '/Number[:\s]+([A-Z0-9-]+)/i',
            '/Nr\.[:\s]+([A-Z0-9-]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract customer data
     */
    private function extractCustomerData(string $text): array
    {
        $customer = [
            'name' => null,
            'street' => null,
            'city' => null,
            'zip' => null,
            'tax_number' => null,
        ];

        // Extract company name (look for patterns like "Rechnung an: [Company Name]")
        $patterns = [
            '/Rechnung an[:\s]+([^
]+)/i',
            '/Angebot an[:\s]+([^
]+)/i',
            '/Kunde[:\s]+([^
]+)/i',
            '/Customer[:\s]+([^
]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $customer['name'] = trim($matches[1]);
                break;
            }
        }

        // Extract address (simplified - look for common patterns)
        if (preg_match('/([A-Z][a-z]+(?:\s[a-z]+)*)\s+(\d{4,5})\s+([A-Z][a-z]+(?:\s[a-z]+)*)/', $text, $matches)) {
            $customer['city'] = $matches[3];
            $customer['zip'] = $matches[2];
        }

        // Extract tax number
        if (preg_match('/(Steuernummer|Tax ID|USt-IdNr)[.:\s]+([A-Z0-9-]+)/i', $text, $matches)) {
            $customer['tax_number'] = trim($matches[2]);
        }

        return $customer;
    }

    /**
     * Extract issue date
     */
    private function extractIssueDate(string $text): ?string
    {
        $patterns = [
            '/Rechnungsdatum[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Datum[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Date[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/(\d{2}\.\d{2}\.\d{4})/', // Generic date pattern
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $date = Carbon::createFromFormat('d.m.Y', trim($matches[1]));

                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Extract due date
     */
    private function extractDueDate(string $text): ?string
    {
        $patterns = [
            '/Fällig am[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Due Date[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Zahlbar bis[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $date = Carbon::createFromFormat('d.m.Y', trim($matches[1]));

                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Extract valid until date for quotes
     */
    private function extractValidUntil(string $text): ?string
    {
        $patterns = [
            '/Gültig bis[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Valid until[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Angebotsfrist[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $date = Carbon::createFromFormat('d.m.Y', trim($matches[1]));

                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Extract service period start
     */
    private function extractServicePeriodStart(string $text): ?string
    {
        $patterns = [
            '/Leistungszeitraum von[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Service Period from[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/(\d{2}\.\d{2}\.\d{4})\s*-\s*\d{2}\.\d{2}\.\d{4}/', // Date range
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $date = Carbon::createFromFormat('d.m.Y', trim($matches[1]));

                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Extract service period end
     */
    private function extractServicePeriodEnd(string $text): ?string
    {
        $patterns = [
            '/Leistungszeitraum.*bis[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/Service Period.*to[:\s]+(\d{2}\.\d{2}\.\d{4})/i',
            '/\d{2}\.\d{2}\.\d{4}\s*-\s*(\d{2}\.\d{2}\.\d{4})/', // Date range
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $date = Carbon::createFromFormat('d.m.Y', trim($matches[1]));

                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Extract service location
     */
    private function extractServiceLocation(string $text): ?string
    {
        $patterns = [
            '/Leistungsort[:\s]+([^
]+)/i',
            '/Service Location[:\s]+([^
]+)/i',
            '/Ort[:\s]+([^
]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract project name
     */
    private function extractProjectName(string $text): ?string
    {
        $patterns = [
            '/Projekt[:\s]+([^
]+)/i',
            '/Project[:\s]+([^
]+)/i',
            '/Projektname[:\s]+([^
]+)/i',
            '/Project Name[:\s]+([^
]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract line items
     */
    private function extractItems(string $text): array
    {
        $items = [];

        // Look for item tables or lists
        // This is a simplified implementation - real invoices might need more sophisticated parsing
        $patterns = [
            // Pattern for: Description | Quantity | Price | Total
            '/([A-Za-z\s]+?)\s+(\d+(?:[.,]\d+)?)\s+([\d,]+(?:[.,]\d+)?)\s+([\d,]+(?:[.,]\d+)?)/',
            // Pattern for: Description | Price
            '/([A-Za-z\s]+?)\s+([\d,]+(?:[.,]\d+)?)/',
        ];

        // Try to find table-like structures
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);

            // Try first pattern (with quantity)
            if (preg_match($patterns[0], $line, $matches)) {
                $items[] = [
                    'description' => trim($matches[1]),
                    'quantity' => (float) str_replace(',', '.', $matches[2]),
                    'unit_price' => (float) str_replace([',', '.'], ['', '.'], $matches[3]),
                    'total' => (float) str_replace([',', '.'], ['', '.'], $matches[4]),
                ];
            }
            // Try second pattern (just description and price)
            elseif (preg_match($patterns[1], $line, $matches) && ! preg_match('/\d{4}/', $line)) {
                $price = (float) str_replace([',', '.'], ['', '.'], $matches[2]);
                $items[] = [
                    'description' => trim($matches[1]),
                    'quantity' => 1,
                    'unit_price' => $price,
                    'total' => $price,
                ];
            }
        }

        return $items;
    }

    /**
     * Extract subtotal
     */
    private function extractSubtotal(string $text): ?float
    {
        $patterns = [
            '/Zwischensumme[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Subtotal[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Nettobetrag[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Net Amount[:\s]+([\d,]+(?:[.,]\d+)?)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return (float) str_replace([',', '.'], ['', '.'], $matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract VAT rate
     */
    private function extractVatRate(string $text): ?float
    {
        $patterns = [
            '/MwSt\.\s*(\d+(?:[.,]\d+)?)\s*%/i',
            '/VAT\s*(\d+(?:[.,]\d+)?)\s*%/i',
            '/(\d+(?:[.,]\d+)?)\s*%\s*MwSt\./i',
            '/(\d+(?:[.,]\d+)?)\s*%\s*VAT/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return (float) str_replace(',', '.', $matches[1]);
            }
        }

        return 19.0; // Default German VAT rate
    }

    /**
     * Extract VAT amount
     */
    private function extractVatAmount(string $text): ?float
    {
        $patterns = [
            '/MwSt\.[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/VAT[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Umsatzsteuer[:\s]+([\d,]+(?:[.,]\d+)?)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return (float) str_replace([',', '.'], ['', '.'], $matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract total amount
     */
    private function extractTotal(string $text): ?float
    {
        $patterns = [
            '/Gesamtbetrag[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Gesamt[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Total[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Summe[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/Betrag[:\s]+([\d,]+(?:[.,]\d+)?)/i',
            '/(\d+[.,]\d{2})\s*€/i', // Amount followed by euro symbol
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return (float) str_replace([',', '.'], ['', '.'], $matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract notes/comments
     */
    private function extractNotes(string $text): ?string
    {
        $patterns = [
            '/Bemerkung[:\s]+([^
]+)/i',
            '/Hinweis[:\s]+([^
]+)/i',
            '/Notiz[:\s]+([^
]+)/i',
            '/Notes[:\s]+([^
]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Import parsed document data
     */
    public function importDocument(array $data, string $documentType = 'invoice'): array
    {
        try {
            if ($documentType === 'invoice') {
                return $this->importInvoice($data);
            } elseif ($documentType === 'quote') {
                return $this->importQuote($data);
            } else {
                return ['error' => 'Unknown document type'];
            }
        } catch (\Exception $e) {
            Log::error('Failed to import document', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return ['error' => 'Import failed: '.$e->getMessage()];
        }
    }

    /**
     * Import invoice data
     */
    private function importInvoice(array $data): array
    {
        // Find or create customer
        $customer = $this->findOrCreateCustomer($data['customer_data']);

        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'type' => $data['is_project_invoice'] ? 'project' : 'general',
            'project_name' => $data['project_name'],
            'service_period_start' => $data['service_period_start'],
            'service_period_end' => $data['service_period_end'],
            'service_location' => $data['service_location'],
            'issue_date' => $data['issue_date'] ?? now()->format('Y-m-d'),
            'due_date' => $data['due_date'] ?? now()->addDays(14)->format('Y-m-d'),
            'items' => $data['items'] ?? [],
            'subtotal' => $data['subtotal'] ?? $this->calculateSubtotal($data['items']),
            'vat_rate' => $data['vat_rate'] ?? 19.0,
            'vat_amount' => $data['vat_amount'] ?? $this->calculateVat($data['items'], $data['vat_rate'] ?? 19.0),
            'total' => $data['total'] ?? $this->calculateTotal($data['items'], $data['vat_rate'] ?? 19.0),
            'notes' => $data['notes'],
        ]);

        return [
            'success' => true,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer' => $customer->name,
        ];
    }

    /**
     * Import quote data
     */
    private function importQuote(array $data): array
    {
        // Find or create customer
        $customer = $this->findOrCreateCustomer($data['customer_data']);

        // Create quote
        $quote = Quote::create([
            'customer_id' => $customer->id,
            'type' => 'general',
            'project_name' => $data['project_name'],
            'issue_date' => $data['issue_date'] ?? now()->format('Y-m-d'),
            'valid_until' => $data['valid_until'] ?? now()->addDays(30)->format('Y-m-d'),
            'items' => $data['items'] ?? [],
            'subtotal' => $data['subtotal'] ?? $this->calculateSubtotal($data['items']),
            'vat_rate' => $data['vat_rate'] ?? 19.0,
            'vat_amount' => $data['vat_amount'] ?? $this->calculateVat($data['items'], $data['vat_rate'] ?? 19.0),
            'total' => $data['total'] ?? $this->calculateTotal($data['items'], $data['vat_rate'] ?? 19.0),
            'notes' => $data['notes'],
        ]);

        return [
            'success' => true,
            'quote_id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'customer' => $customer->name,
        ];
    }

    /**
     * Find or create customer
     */
    private function findOrCreateCustomer(array $customerData): Customer
    {
        // Try to find by name
        if (! empty($customerData['name'])) {
            $customer = Customer::where('name', 'like', '%'.$customerData['name'].'%')->first();
            if ($customer) {
                return $customer;
            }
        }

        // Create new customer
        return Customer::create([
            'name' => $customerData['name'] ?? 'Unbekannter Kunde',
            'address' => [
                'street' => $customerData['street'] ?? null,
                'city' => $customerData['city'] ?? null,
                'zip' => $customerData['zip'] ?? null,
            ],
            'tax_id' => $customerData['tax_number'],
        ]);
    }

    /**
     * Calculate subtotal from items
     */
    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['total'] ?? 0;
        }

        return $subtotal;
    }

    /**
     * Calculate VAT amount
     */
    private function calculateVat(array $items, float $vatRate): float
    {
        $subtotal = $this->calculateSubtotal($items);

        return $subtotal * ($vatRate / 100);
    }

    /**
     * Calculate total amount
     */
    private function calculateTotal(array $items, float $vatRate): float
    {
        $subtotal = $this->calculateSubtotal($items);
        $vat = $this->calculateVat($items, $vatRate);

        return $subtotal + $vat;
    }
}