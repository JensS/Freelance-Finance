<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaVisionService
{
    private string $baseUrl;

    private string $model;

    public function __construct()
    {
        // Use vision-capable model
        // Supported models: llama3.2-vision, llava, qwen2-vl, granite-3.2-vision, minicpm-v
        $this->baseUrl = rtrim(\App\Models\Setting::get('ollama_url', config('services.ollama.url', 'http://localhost:11434')), '/');
        // Default to llama3.2-vision, but allow override
        $this->model = \App\Models\Setting::get('ollama_vision_model', 'llama3.2-vision');
    }

    /**
     * Extract receipt information from a PDF document
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractReceiptData(string $pdfPath): ?array
    {
        try {
            // Convert PDF first page to image
            $imageBase64 = $this->pdfToBase64Image($pdfPath);

            if (! $imageBase64) {
                Log::error('Failed to convert PDF to image', ['path' => $pdfPath]);

                return null;
            }

            // Create structured prompt for data extraction
            $prompt = $this->buildExtractionPrompt();

            // Send to Ollama vision API
            $response = Http::timeout(120)->post($this->baseUrl.'/api/generate', [
                'model' => $this->model,
                'prompt' => $prompt,
                'images' => [$imageBase64],
                'stream' => false,
                'options' => [
                    'temperature' => 0.1, // Low temperature for factual extraction
                ],
                'format' => 'json', // Request JSON output
            ]);

            if (! $response->successful()) {
                Log::error('Ollama vision API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $result = $response->json();

            // Extract the response text which should contain JSON
            $responseText = $result['response'] ?? null;

            if (! $responseText) {
                Log::error('No response text from Ollama');

                return null;
            }

            // Parse JSON response
            $extractedData = json_decode($responseText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse JSON from Ollama response', [
                    'error' => json_last_error_msg(),
                    'response' => $responseText,
                ]);

                return null;
            }

            // Validate and normalize the extracted data
            return $this->normalizeExtractedData($extractedData);

        } catch (\Exception $e) {
            Log::error('Receipt extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Convert PDF first page to base64 encoded image
     *
     * @param  string  $pdfPath  Path to PDF file
     * @return string|null Base64 encoded image or null on failure
     */
    private function pdfToBase64Image(string $pdfPath): ?string
    {
        try {
            $imagick = new \Imagick;

            // Set resolution for good quality
            $imagick->setResolution(200, 200);

            // Read only first page
            $imagick->readImage($pdfPath.'[0]');

            // Convert to RGB (remove alpha channel)
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);

            // Get image as blob
            $imageBlob = $imagick->getImageBlob();

            // Convert to base64
            $base64 = base64_encode($imageBlob);

            // Clean up
            $imagick->clear();
            $imagick->destroy();

            return $base64;

        } catch (\Exception $e) {
            Log::error('PDF to image conversion failed', [
                'error' => $e->getMessage(),
                'path' => $pdfPath,
            ]);

            return null;
        }
    }

    /**
     * Build the extraction prompt with clear JSON schema
     *
     * @return string The prompt
     */
    public function buildExtractionPrompt(): string
    {
        return <<<'PROMPT'
You are analyzing a receipt or invoice image from a German business. Extract the following information and return it as valid JSON.

**Required Fields:**
- **date**: Transaction date in DD.MM.YYYY format (e.g., "15.03.2024")
- **correspondent**: Merchant or company name (the business that issued the receipt)
- **amount_gross**: Total gross amount including VAT as a decimal number (e.g., 119.00)
- **amount_net**: Net amount without VAT as a decimal number (e.g., 100.00)
- **vat_rate**: VAT rate as percentage number (e.g., 19, 7, or 0)
- **vat_amount**: VAT amount as a decimal number (e.g., 19.00)
- **description**: Brief description of what was purchased or the service provided
- **transaction_type**: Classify as one of:
  - "Geschäftsausgabe 19%" (business expense with 19% VAT - standard rate in Germany)
  - "Geschäftsausgabe 7%" (business expense with 7% VAT - reduced rate)
  - "Geschäftsausgabe 0%" (business expense with 0% VAT - international services)
  - "Bewirtung" (business entertainment/meals)
  - "Einkommen 19%" (income with 19% VAT)
  - "Privat" (private/personal expense)

**Important Instructions:**
1. Amounts must be decimal numbers without currency symbols (e.g., 119.00 not "119,00 EUR")
2. German receipts use comma as decimal separator (119,00) - convert to period (119.00)
3. If gross amount is given, calculate net = gross / (1 + vat_rate/100)
4. If VAT rate is not explicitly shown, assume 19% for German receipts
5. For international services or EU B2B, use 0% VAT
6. Set transaction_type based on context: meals/entertainment = "Bewirtung", otherwise use appropriate Geschäftsausgabe rate
7. If any field cannot be determined, set it to null
8. Return ONLY valid JSON, no additional text

**Example Response:**
{
  "date": "15.03.2024",
  "correspondent": "Amazon EU S.à.r.l.",
  "amount_gross": 119.00,
  "amount_net": 100.00,
  "vat_rate": 19,
  "vat_amount": 19.00,
  "description": "Office supplies and computer equipment",
  "transaction_type": "Geschäftsausgabe 19%"
}

Now analyze the receipt image and extract the information:
PROMPT;
    }

    /**
     * Normalize and validate extracted data
     *
     * @param  array  $data  Raw extracted data
     * @return array Normalized data
     */
    private function normalizeExtractedData(array $data): array
    {
        return [
            'date' => $data['date'] ?? null,
            'correspondent' => $data['correspondent'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'amount' => isset($data['amount_gross']) ? (float) $data['amount_gross'] : null,
            'net_amount' => isset($data['amount_net']) ? (float) $data['amount_net'] : null,
            'vat_rate' => isset($data['vat_rate']) ? (float) $data['vat_rate'] : null,
            'vat_amount' => isset($data['vat_amount']) ? (float) $data['vat_amount'] : null,
            'type' => $data['transaction_type'] ?? null,
            'confidence' => $data['confidence'] ?? null,
        ];
    }

    /**
     * Extract invoice information from a PDF document
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractInvoiceData(string $pdfPath): ?array
    {
        try {
            // Convert PDF first page to image
            $imageBase64 = $this->pdfToBase64Image($pdfPath);

            if (! $imageBase64) {
                Log::error('Failed to convert PDF to image', ['path' => $pdfPath]);

                return null;
            }

            // Create structured prompt for invoice extraction
            $prompt = $this->buildInvoiceExtractionPrompt();

            // Send to Ollama vision API
            $response = Http::timeout(120)->post($this->baseUrl.'/api/generate', [
                'model' => $this->model,
                'prompt' => $prompt,
                'images' => [$imageBase64],
                'stream' => false,
                'options' => [
                    'temperature' => 0.1, // Low temperature for factual extraction
                ],
                'format' => 'json', // Request JSON output
            ]);

            if (! $response->successful()) {
                Log::error('Ollama vision API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $result = $response->json();
            $responseText = $result['response'] ?? null;

            if (! $responseText) {
                Log::error('No response text from Ollama');

                return null;
            }

            // Parse JSON response
            $extractedData = json_decode($responseText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse JSON from Ollama response', [
                    'error' => json_last_error_msg(),
                    'response' => $responseText,
                ]);

                return null;
            }

            return $this->normalizeInvoiceData($extractedData);

        } catch (\Exception $e) {
            Log::error('Invoice extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Extract quote information from a PDF document
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractQuoteData(string $pdfPath): ?array
    {
        try {
            // Convert PDF first page to image
            $imageBase64 = $this->pdfToBase64Image($pdfPath);

            if (! $imageBase64) {
                Log::error('Failed to convert PDF to image', ['path' => $pdfPath]);

                return null;
            }

            // Create structured prompt for quote extraction
            $prompt = $this->buildQuoteExtractionPrompt();

            // Send to Ollama vision API
            $response = Http::timeout(120)->post($this->baseUrl.'/api/generate', [
                'model' => $this->model,
                'prompt' => $prompt,
                'images' => [$imageBase64],
                'stream' => false,
                'options' => [
                    'temperature' => 0.1, // Low temperature for factual extraction
                ],
                'format' => 'json', // Request JSON output
            ]);

            if (! $response->successful()) {
                Log::error('Ollama vision API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $result = $response->json();
            $responseText = $result['response'] ?? null;

            if (! $responseText) {
                Log::error('No response text from Ollama');

                return null;
            }

            // Parse JSON response
            $extractedData = json_decode($responseText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse JSON from Ollama response', [
                    'error' => json_last_error_msg(),
                    'response' => $responseText,
                ]);

                return null;
            }

            return $this->normalizeQuoteData($extractedData);

        } catch (\Exception $e) {
            Log::error('Quote extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Build the invoice extraction prompt with clear JSON schema
     *
     * @return string The prompt
     */
    public function buildInvoiceExtractionPrompt(): string
    {
        return <<<'PROMPT'
You are analyzing an invoice (Rechnung) image from a German freelancer. Extract the following information and return it as valid JSON.

**Required Fields:**
- **invoice_number**: The invoice/receipt number (e.g., "503", "RE-2025-001")
- **customer_name**: The customer/client company name
- **customer_address**: Full customer address including street, postal code, and city
- **issue_date**: Invoice issue date in DD.MM.YYYY or DD.MM.YY format (e.g., "12.08.25")
- **due_date**: Payment due date in DD.MM.YYYY or DD.MM.YY format (if mentioned, otherwise null)
- **project_name**: Project name or description if mentioned
- **service_period_start**: Service period start date in DD.MM.YYYY format (e.g., "4.8.25", if mentioned)
- **service_period_end**: Service period end date in DD.MM.YYYY format (e.g., "9.8.25", if mentioned)
- **service_location**: Service location/place (e.g., "Frankfurt", "Berlin", if mentioned)
- **items**: Array of line items, each with:
  - **description**: Item description (keep German umlauts and special characters)
  - **quantity**: Quantity as number (e.g., 3, 1, 2.5)
  - **unit_price**: Unit price as decimal number (e.g., 2000.00)
  - **total**: Total price for this line as decimal number (e.g., 6000.00)
- **subtotal**: Net total amount (Nettobetrag) as decimal number
- **vat_rate**: VAT percentage (e.g., 19, 7, 0)
- **vat_amount**: VAT amount (MwSt) as decimal number
- **total**: Gross total amount (Gesamtbetrag) as decimal number
- **notes**: Any additional notes, payment terms, or special instructions

**Important Instructions:**
1. Amounts must be decimal numbers without currency symbols (e.g., 10251.85 not "10.251,85 €")
2. German invoices use comma as decimal separator (10.251,85) - convert to period (10251.85)
3. German invoices use period as thousands separator (10.251,85) - remove it (10251.85)
4. Dates should be in DD.MM.YYYY or DD.MM.YY format as shown in the document
5. Keep all German text, umlauts (ä, ö, ü, ß) and special characters intact in descriptions
6. Extract ALL line items from the invoice table
7. If a field cannot be determined, set it to null
8. Return ONLY valid JSON, no additional text

**Example Response:**
{
  "invoice_number": "503",
  "customer_name": "Sahler Werbung GmbH & Co. KG",
  "customer_address": "Berliner Allee 2, 40212 Düsseldorf",
  "issue_date": "12.08.25",
  "due_date": "14.08.25",
  "project_name": "Outerwear / RUSH Kampagne",
  "service_period_start": "4.8.25",
  "service_period_end": "9.8.25",
  "service_location": "Frankfurt",
  "items": [
    {
      "description": "Director creative fee / Gage",
      "quantity": 3,
      "unit_price": 2000.00,
      "total": 6000.00
    },
    {
      "description": "Kameratechnik: A Kamera (Alexa Mini, Objektivsatz, etc.)",
      "quantity": 1,
      "unit_price": 1500.00,
      "total": 1500.00
    }
  ],
  "subtotal": 8615.00,
  "vat_rate": 19,
  "vat_amount": 1636.85,
  "total": 10251.85,
  "notes": "Vielen Dank für die angenehme Zusammenarbeit! Die Rechnungssumme ist fällig mit Zugang dieser Rechnung."
}

Now analyze the invoice image and extract the information:
PROMPT;
    }

    /**
     * Build the quote extraction prompt with clear JSON schema
     *
     * @return string The prompt
     */
    public function buildQuoteExtractionPrompt(): string
    {
        return <<<'PROMPT'
You are analyzing a quote/proposal (Angebot) image from a German freelancer. Extract the following information and return it as valid JSON.

**Required Fields:**
- **quote_number**: The quote/proposal number (e.g., "2025-P&C-Sustainability-Video-v1", "Q-2025-001")
- **customer_name**: The customer/client company name
- **customer_address**: Full customer address including street, postal code, and city
- **issue_date**: Quote issue date in DD.MM.YYYY or DD.MM.YY format (e.g., "21.01.25")
- **valid_until**: Quote validity/expiration date in DD.MM.YYYY format (if mentioned, otherwise null)
- **project_name**: Project name or description
- **brief**: Project brief or description (if mentioned as separate section)
- **service_period**: Service period description (e.g., "Q1 2025", if mentioned)
- **service_location**: Service location/place (e.g., "Berlin, DE", if mentioned)
- **items**: Array of line items, each with:
  - **description**: Item description (keep German umlauts and special characters)
  - **quantity**: Quantity as number (e.g., 3, 1, 2.5)
  - **unit_price**: Unit price as decimal number (e.g., 2000.00)
  - **total**: Total price for this line as decimal number (e.g., 6000.00)
- **subtotal**: Net total amount (Nettobetrag) as decimal number
- **vat_rate**: VAT percentage (e.g., 19, 7, 0)
- **vat_amount**: VAT amount (MwSt) as decimal number
- **total**: Gross total amount (Gesamtbetrag) as decimal number
- **notes**: Any additional notes or closing remarks

**Important Instructions:**
1. Amounts must be decimal numbers without currency symbols (e.g., 21646.10 not "21.646,10 €")
2. German quotes use comma as decimal separator (21.646,10) - convert to period (21646.10)
3. German quotes use period as thousands separator (21.646,10) - remove it (21646.10)
4. Dates should be in DD.MM.YYYY or DD.MM.YY format as shown in the document
5. Keep all German text, umlauts (ä, ö, ü, ß) and special characters intact in descriptions
6. Extract ALL line items from the quote table
7. If a field cannot be determined, set it to null
8. Return ONLY valid JSON, no additional text

**Example Response:**
{
  "quote_number": "2025-P&C-Sustainability-Video-v1",
  "customer_name": "vbc Agency GmbH",
  "customer_address": "Meinekestr. 12, 10719 Berlin",
  "issue_date": "21.01.25",
  "valid_until": null,
  "project_name": "P&C Sustainability Corporate Video",
  "brief": "90-120 sekündiges Video zum Thema Sustainability...",
  "service_period": "Q1 2025",
  "service_location": "Berlin, DE",
  "items": [
    {
      "description": "Produktion: DP & Director creative fee",
      "quantity": 1,
      "unit_price": 2000.00,
      "total": 2000.00
    },
    {
      "description": "Produktion: Crew: Assistent (1 Drehtag + An- und Abreise)",
      "quantity": 2,
      "unit_price": 450.00,
      "total": 900.00
    }
  ],
  "subtotal": 18190.00,
  "vat_rate": 19,
  "vat_amount": 3456.10,
  "total": 21646.10,
  "notes": "Vielen Dank für das Interesse an einer Zusammenarbeit!"
}

Now analyze the quote image and extract the information:
PROMPT;
    }

    /**
     * Normalize and validate extracted invoice data
     *
     * @param  array  $data  Raw extracted data
     * @return array Normalized data
     */
    private function normalizeInvoiceData(array $data): array
    {
        return [
            'invoice_number' => $data['invoice_number'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'project_name' => $data['project_name'] ?? null,
            'service_period_start' => $data['service_period_start'] ?? null,
            'service_period_end' => $data['service_period_end'] ?? null,
            'service_location' => $data['service_location'] ?? null,
            'items' => $data['items'] ?? [],
            'subtotal' => isset($data['subtotal']) ? (float) $data['subtotal'] : null,
            'vat_rate' => isset($data['vat_rate']) ? (float) $data['vat_rate'] : null,
            'vat_amount' => isset($data['vat_amount']) ? (float) $data['vat_amount'] : null,
            'total' => isset($data['total']) ? (float) $data['total'] : null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    /**
     * Normalize and validate extracted quote data
     *
     * @param  array  $data  Raw extracted data
     * @return array Normalized data
     */
    private function normalizeQuoteData(array $data): array
    {
        return [
            'quote_number' => $data['quote_number'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'project_name' => $data['project_name'] ?? null,
            'brief' => $data['brief'] ?? null,
            'service_period' => $data['service_period'] ?? null,
            'service_location' => $data['service_location'] ?? null,
            'items' => $data['items'] ?? [],
            'subtotal' => isset($data['subtotal']) ? (float) $data['subtotal'] : null,
            'vat_rate' => isset($data['vat_rate']) ? (float) $data['vat_rate'] : null,
            'vat_amount' => isset($data['vat_amount']) ? (float) $data['vat_amount'] : null,
            'total' => isset($data['total']) ? (float) $data['total'] : null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    /**
     * Test Ollama vision connection
     *
     * @return bool Connection status
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl.'/api/tags');

            if (! $response->successful()) {
                return false;
            }

            // Check if vision model is available
            $tags = $response->json()['models'] ?? [];
            foreach ($tags as $tag) {
                if (str_contains($tag['name'] ?? '', 'vision') || str_contains($tag['name'] ?? '', 'llava')) {
                    return true;
                }
            }

            Log::warning('No vision-capable model found in Ollama');

            return false;

        } catch (\Exception $e) {
            Log::error('Ollama vision connection test failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
