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
        // First try to extract project info from compact format (Name · Zeitraum · Ort)
        $projectInfo = $this->extractCompactProjectInfo($text);

        $data = [
            'type' => 'invoice',
            'invoice_number' => $this->extractInvoiceNumber($text),
            'customer_data' => $this->extractCustomerData($text),
            'issue_date' => $this->extractIssueDate($text),
            'due_date' => $this->extractDueDate($text),
            'service_period_start' => $projectInfo['service_period_start'] ?? $this->extractServicePeriodStart($text),
            'service_period_end' => $projectInfo['service_period_end'] ?? $this->extractServicePeriodEnd($text),
            'service_location' => $projectInfo['service_location'] ?? $this->extractServiceLocation($text),
            'project_name' => $projectInfo['project_name'] ?? $this->extractProjectName($text),
            'items' => $this->extractItems($text),
            'subtotal' => $this->extractSubtotal($text),
            'vat_rate' => $this->extractVatRate($text),
            'vat_amount' => $this->extractVatAmount($text),
            'total' => $this->extractTotal($text),
            'notes' => $this->extractNotes($text),
        ];

        // Try to find matching customer in database
        $data['existing_customer'] = $this->findExistingCustomer($data['customer_data']);

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
        $customerData = $this->extractCustomerData($text);

        return [
            'type' => 'quote',
            'quote_number' => $this->extractQuoteNumber($text),
            'customer_data' => $customerData,
            'issue_date' => $this->extractIssueDate($text),
            'valid_until' => $this->extractValidUntil($text),
            'project_name' => $this->extractProjectName($text),
            'items' => $this->extractItems($text),
            'subtotal' => $this->extractSubtotal($text),
            'vat_rate' => $this->extractVatRate($text),
            'vat_amount' => $this->extractVatAmount($text),
            'total' => $this->extractTotal($text),
            'notes' => $this->extractNotes($text),
            'existing_customer' => $this->findExistingCustomer($customerData),
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
     * Extract project info from compact format: "Project Name · Leistungszeitraum: DD.-DD.MM.YY · Leistungsort: Location"
     */
    private function extractCompactProjectInfo(string $text): array
    {
        $result = [
            'project_name' => null,
            'service_period_start' => null,
            'service_period_end' => null,
            'service_location' => null,
        ];

        // Pattern for compact project info with middot (·) separator
        if (preg_match('/([^·\n]+)\s*·\s*Leistungszeitraum[:\s]*([^·\n]+)\s*·\s*Leistungsort[:\s]*([^·\n]+)/i', $text, $matches)) {
            $result['project_name'] = trim($matches[1]);

            // Parse date range (e.g., "19.-26.5.25" or "01.05.2025-15.05.2025")
            $dateRange = trim($matches[2]);
            $dates = $this->parseDateRange($dateRange);
            if ($dates) {
                $result['service_period_start'] = $dates['start'];
                $result['service_period_end'] = $dates['end'];
            }

            $result['service_location'] = trim($matches[3]);
        }

        return $result;
    }

    /**
     * Parse date range from various formats
     * Supports: "19.-26.5.25", "01.05.2025-15.05.2025", "19.05.2025 - 26.05.2025"
     */
    private function parseDateRange(string $dateRange): ?array
    {
        // Format: "19.-26.5.25" (compact German format)
        if (preg_match('/(\d{1,2})\.?\s*-\s*(\d{1,2})\.(\d{1,2})\.(\d{2,4})/', $dateRange, $matches)) {
            $startDay = $matches[1];
            $endDay = $matches[2];
            $month = $matches[3];
            $year = $matches[4];

            // Convert 2-digit year to 4-digit
            if (strlen($year) === 2) {
                $year = '20'.$year;
            }

            try {
                $start = Carbon::createFromFormat('d.m.Y', "$startDay.$month.$year");
                $end = Carbon::createFromFormat('d.m.Y', "$endDay.$month.$year");

                return [
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to parse compact date range', ['range' => $dateRange, 'error' => $e->getMessage()]);
            }
        }

        // Format: "01.05.2025-15.05.2025" or "01.05.2025 - 15.05.2025"
        if (preg_match('/(\d{2}\.\d{2}\.\d{4})\s*-\s*(\d{2}\.\d{2}\.\d{4})/', $dateRange, $matches)) {
            try {
                $start = Carbon::createFromFormat('d.m.Y', $matches[1]);
                $end = Carbon::createFromFormat('d.m.Y', $matches[2]);

                return [
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to parse standard date range', ['range' => $dateRange, 'error' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * Find existing customer in database by email or name
     */
    private function findExistingCustomer(array $customerData): ?Customer
    {
        // Try to find by email first (most reliable) - but only if email exists
        if (! empty($customerData['email'])) {
            $customer = Customer::where('email', $customerData['email'])->first();
            if ($customer) {
                return $customer;
            }
        }

        // Try to find by name (this is actually very reliable for companies)
        if (! empty($customerData['name'])) {
            // Exact match first
            $customer = Customer::where('name', $customerData['name'])->first();
            if ($customer) {
                return $customer;
            }

            // Try fuzzy match - check if customer name contains the extracted name (or vice versa)
            // Normalize both strings for comparison (remove legal suffixes and extra spaces)
            $searchName = $this->normalizeCompanyName($customerData['name']);

            $customers = Customer::all();
            foreach ($customers as $existingCustomer) {
                $existingName = $this->normalizeCompanyName($existingCustomer->name);

                // Check if names match after normalization
                if ($existingName === $searchName) {
                    return $existingCustomer;
                }

                // Check if one contains the other (for cases like "Company GmbH" vs "Company GmbH & Co. KG")
                if (strlen($searchName) > 5 && strlen($existingName) > 5) {
                    if (stripos($existingName, $searchName) !== false || stripos($searchName, $existingName) !== false) {
                        return $existingCustomer;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Normalize company name for matching (remove legal suffixes and extra spaces)
     */
    private function normalizeCompanyName(string $name): string
    {
        // Convert to lowercase for comparison
        $normalized = mb_strtolower(trim($name));

        // Remove common legal suffixes
        $suffixes = [' gmbh & co. kg', ' gmbh & co kg', ' gmbh', ' ag', ' kg', ' ug', ' e.v.', ' ltd', ' inc', ' llc'];
        foreach ($suffixes as $suffix) {
            if (str_ends_with($normalized, $suffix)) {
                $normalized = trim(substr($normalized, 0, -strlen($suffix)));
            }
        }

        // Remove extra spaces
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }

    /**
     * Extract invoice number
     */
    private function extractInvoiceNumber(string $text): ?string
    {
        // Common invoice number patterns
        $patterns = [
            '/Rechnung\s+(\d+)/i', // "Rechnung 492" format (most specific first)
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

        // Isolate the customer address section (from date to first major section like "Projekt:" or "Beschreibung")
        // This prevents extracting the sender's info from the footer
        $customerSection = $text;
        if (preg_match('/\d{1,2}\.\d{1,2}\.\d{2,4}(.*?)(Projekt:|Beschreibung|Bankverbindung)/is', $text, $sectionMatches)) {
            $customerSection = $sectionMatches[1];
        }

        // Extract email from customer section only
        if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $customerSection, $matches)) {
            $customer['email'] = $matches[0];
        }

        // Extract company name - try multiple approaches
        // Pattern 1: After date line (Berlin, 4.6.25) followed by company name
        if (preg_match('/\d{1,2}\.\d{1,2}\.\d{2,4}\s*\n\s*\n\s*([^\n]+(?:GmbH|AG|KG|UG|e\.V\.|Ltd|Inc)[^\n]*)/i', $text, $matches)) {
            $customer['name'] = trim($matches[1]);
        }

        // Pattern 2: If no company suffix found, try to get first non-empty line after date
        if (! $customer['name'] && preg_match('/\d{1,2}\.\d{1,2}\.\d{2,4}\s*\n\s*\n\s*([^\n]+)\s*\n/i', $text, $matches)) {
            $name = trim($matches[1]);
            // Make sure it's not a section header
            if (! preg_match('/^(Projekt:|Beschreibung|Rechnung|Angebot)/i', $name)) {
                $customer['name'] = $name;
            }
        }

        // Pattern 3: Look for patterns like "Rechnung an: [Company Name]"
        if (! $customer['name']) {
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
        }

        // Extract street address (pattern: word + number)
        if (preg_match('/([A-ZÄÖÜa-zäöüß]+(?:strasse|straße|str\.|weg|platz|allee))\s+(\d+[a-z]?)/i', $customerSection, $matches)) {
            $customer['street'] = trim($matches[0]);
        }

        // Extract ZIP and city (pattern: 5-digit ZIP followed by city name)
        if (preg_match('/(\d{5})\s+([A-ZÄÖÜ][a-zäöüß]+(?:\s[A-ZÄÖÜ][a-zäöüß]+)*)/', $customerSection, $matches)) {
            $customer['zip'] = $matches[1];
            $customer['city'] = $matches[2];
        }

        // Extract tax number (look in full text since it might be in footer)
        if (preg_match('/(Steuernummer|Tax ID|USt-IdNr|VAT No\.)[.:\s]+([A-Z0-9\/\-]+)/i', $text, $matches)) {
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
            '/,\s*(\d{1,2}\.\d{1,2}\.\d{2,4})\s*\n/', // "Berlin, 4.6.25" format
            '/(\d{2}\.\d{2}\.\d{4})/', // Generic date pattern (4-digit year)
            '/(\d{1,2}\.\d{1,2}\.\d{2})/', // Compact German date (2-digit year)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $dateStr = trim($matches[1]);

                    // Try parsing with 4-digit year first
                    if (preg_match('/\d{2}\.\d{2}\.\d{4}/', $dateStr)) {
                        $date = Carbon::createFromFormat('d.m.Y', $dateStr);

                        return $date->format('Y-m-d');
                    }

                    // Try parsing with 2-digit year
                    if (preg_match('/(\d{1,2})\.(\d{1,2})\.(\d{2})/', $dateStr, $dateParts)) {
                        $day = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
                        $month = str_pad($dateParts[2], 2, '0', STR_PAD_LEFT);
                        $year = '20'.$dateParts[3];
                        $date = Carbon::createFromFormat('d.m.Y', "$day.$month.$year");

                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    Log::debug('Failed to parse date', ['date_string' => $dateStr, 'error' => $e->getMessage()]);
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
            '/Nettobetrag[:\s]+([\d.]+,\d+)/i', // German format with thousands separator
            '/Zwischensumme[:\s]+([\d.]+,\d+)/i',
            '/Subtotal[:\s]+([\d.]+,\d+)/i',
            '/Net Amount[:\s]+([\d.]+,\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseGermanNumber($matches[1]);
            }
        }

        return null;
    }

    /**
     * Parse German number format (e.g., "11.607,33" -> 11607.33)
     */
    private function parseGermanNumber(string $number): float
    {
        // Remove currency symbols and whitespace
        $number = trim(str_replace(['€', ' '], '', $number));

        // German format: dot is thousands separator, comma is decimal separator
        $number = str_replace('.', '', $number); // Remove thousands separator
        $number = str_replace(',', '.', $number); // Replace decimal comma with dot

        return (float) $number;
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
            '/MwSt\.[\s\x{00A0}]*\n[\s\x{00A0}]*\d+[,.]?\d*[\s\x{00A0}]*%[\s\x{00A0}]+([\d.]+,\d+)/ui', // "MwSt.\n19,00 % 2.205,39 €" (with line break and non-breaking spaces)
            '/MwSt\.?[\s\x{00A0}]+\d+[,.]?\d*[\s\x{00A0}]*%[\s\x{00A0}]+([\d.]+,\d+)[\s\x{00A0}]*€/ui', // "MwSt. 19,00 % 2.205,39 €" (inline)
            '/MwSt\.[:\s]+([\d.]+,\d+)/i',
            '/VAT[:\s]+([\d.]+,\d+)/i',
            '/Umsatzsteuer[:\s]+([\d.]+,\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseGermanNumber($matches[1]);
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
            '/Gesamtbetrag[:\s]+([\d.]+,\d+)/i',
            '/Gesamt[:\s]+([\d.]+,\d+)/i',
            '/Total[:\s]+([\d.]+,\d+)/i',
            '/Summe[:\s]+([\d.]+,\d+)/i',
            '/Betrag[:\s]+([\d.]+,\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseGermanNumber($matches[1]);
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
     * Import invoice data (after verification)
     */
    private function importInvoice(array $data): array
    {
        // Use existing customer if provided, otherwise find or create
        if (isset($data['existing_customer']) && $data['existing_customer'] instanceof Customer) {
            $customer = $data['existing_customer'];
        } else {
            $customer = $this->findOrCreateCustomer($data['customer_data']);
        }

        // Generate invoice number (required for German tax compliance)
        $invoiceNumber = $data['invoice_number'] ?? Invoice::generateInvoiceNumber();

        // Ensure required dates have defaults
        $issueDate = $data['issue_date'] ?? now()->format('Y-m-d');
        $dueDate = $data['due_date'] ?? Carbon::parse($issueDate)->addDays(14)->format('Y-m-d');

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customer->id,
            'type' => $data['is_project_invoice'] ? 'project' : 'general',
            'project_name' => $data['project_name'],
            'service_period_start' => $data['service_period_start'],
            'service_period_end' => $data['service_period_end'],
            'service_location' => $data['service_location'],
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'items' => $data['items'] ?? [],
            'subtotal' => $data['subtotal'] ?? $this->calculateSubtotal($data['items'] ?? []),
            'vat_rate' => $data['vat_rate'] ?? 19.0,
            'vat_amount' => $data['vat_amount'] ?? $this->calculateVat($data['items'] ?? [], $data['vat_rate'] ?? 19.0),
            'total' => $data['total'] ?? $this->calculateTotal($data['items'] ?? [], $data['vat_rate'] ?? 19.0),
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
     * Import quote data (after verification)
     */
    private function importQuote(array $data): array
    {
        // Use existing customer if provided, otherwise find or create
        if (isset($data['existing_customer']) && $data['existing_customer'] instanceof Customer) {
            $customer = $data['existing_customer'];
        } else {
            $customer = $this->findOrCreateCustomer($data['customer_data']);
        }

        // Generate quote number
        $quoteNumber = $data['quote_number'] ?? Quote::generateQuoteNumber();

        // Ensure required dates have defaults
        $issueDate = $data['issue_date'] ?? now()->format('Y-m-d');
        $validUntil = $data['valid_until'] ?? Carbon::parse($issueDate)->addDays(30)->format('Y-m-d');

        // Create quote
        $quote = Quote::create([
            'quote_number' => $quoteNumber,
            'customer_id' => $customer->id,
            'type' => 'general',
            'project_name' => $data['project_name'],
            'issue_date' => $issueDate,
            'valid_until' => $validUntil,
            'items' => $data['items'] ?? [],
            'subtotal' => $data['subtotal'] ?? $this->calculateSubtotal($data['items'] ?? []),
            'vat_rate' => $data['vat_rate'] ?? 19.0,
            'vat_amount' => $data['vat_amount'] ?? $this->calculateVat($data['items'] ?? [], $data['vat_rate'] ?? 19.0),
            'total' => $data['total'] ?? $this->calculateTotal($data['items'] ?? [], $data['vat_rate'] ?? 19.0),
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
        // Try to find using the enhanced matching logic
        $existingCustomer = $this->findExistingCustomer($customerData);
        if ($existingCustomer) {
            return $existingCustomer;
        }

        // Create new customer with only non-empty values
        $customerFields = [
            'name' => $customerData['name'] ?? 'Unbekannter Kunde',
        ];

        // Only add fields that have actual values
        if (! empty($customerData['email'])) {
            $customerFields['email'] = $customerData['email'];
        }
        if (! empty($customerData['street'])) {
            $customerFields['street'] = $customerData['street'];
        }
        if (! empty($customerData['city'])) {
            $customerFields['city'] = $customerData['city'];
        }
        if (! empty($customerData['zip'])) {
            $customerFields['zip'] = $customerData['zip'];
        }
        if (! empty($customerData['tax_number'])) {
            $customerFields['tax_number'] = $customerData['tax_number'];
        }

        return Customer::create($customerFields);
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