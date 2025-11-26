<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Unified AI Vision Service (Legacy Wrapper)
 *
 * This service is now a thin wrapper around the new AIService
 * which uses Prism for unified AI provider access.
 *
 * Supports multiple AI providers:
 * - Ollama (local)
 * - OpenAI (GPT-4o, GPT-4 Turbo, GPT-4o-mini)
 * - Anthropic (Claude 3.5 Sonnet, Claude 3 Opus, Claude 3 Haiku)
 * - OpenRouter (proxy to various models)
 *
 * @deprecated Use AIService directly for new code
 */
class AIVisionService
{
    private AIService $aiService;

    private ?string $lastError = null;

    public function __construct()
    {
        $this->aiService = app(AIService::class);
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError ?? $this->aiService->getLastError();
    }

    /**
     * Extract receipt data from PDF
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractReceiptData(string $pdfPath): ?array
    {
        // Get prompt from AIPromptService
        $promptService = app(AIPromptService::class);
        $correspondents = $this->getKnownCorrespondents();
        $prompt = $promptService->buildReceiptExtractionPrompt($correspondents);

        // Use new AIService
        $result = $this->aiService->extractFromDocument($pdfPath, $prompt, true);

        if ($result) {
            // Normalize the data (add is_meal and adjust description for Bewirtung)
            return $this->normalizeReceiptData($result);
        }

        $this->lastError = $this->aiService->getLastError();

        return null;
    }

    /**
     * Extract invoice data from PDF
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractInvoiceData(string $pdfPath): ?array
    {
        // Get prompt from AIPromptService
        $promptService = app(AIPromptService::class);
        $correspondents = $this->getKnownCorrespondents();
        $prompt = $promptService->buildInvoiceExtractionPrompt($correspondents);

        // Use new AIService
        $result = $this->aiService->extractFromDocument($pdfPath, $prompt, true);

        if ($result) {
            return $this->normalizeInvoiceData($result);
        }

        $this->lastError = $this->aiService->getLastError();

        return null;
    }

    /**
     * Extract quote data from PDF
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractQuoteData(string $pdfPath): ?array
    {
        // Get prompt from AIPromptService
        $promptService = app(AIPromptService::class);
        $correspondents = $this->getKnownCorrespondents();
        $prompt = $promptService->buildQuoteExtractionPrompt($correspondents);

        // Use new AIService
        $result = $this->aiService->extractFromDocument($pdfPath, $prompt, true);

        if ($result) {
            return $this->normalizeQuoteData($result);
        }

        $this->lastError = $this->aiService->getLastError();

        return null;
    }

    /**
     * Normalize receipt data to match expected format
     */
    private function normalizeReceiptData(array $data): array
    {
        $description = $data['description'] ?? null;
        $isBewirtung = $data['is_bewirtung'] ?? false;

        // Add "Bewirtung" note if it's a meal/restaurant expense
        if ($isBewirtung && $description) {
            $description = trim($description).' [Bewirtung]';
        } elseif ($isBewirtung && ! $description) {
            $description = 'Bewirtung';
        }

        return [
            'date' => $data['date'] ?? null,
            'correspondent' => $data['correspondent'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $description,
            'amount' => isset($data['amount_gross']) ? (float) $data['amount_gross'] : null,
            'net_amount' => isset($data['amount_net']) ? (float) $data['amount_net'] : null,
            'vat_rate' => isset($data['vat_rate']) ? (float) $data['vat_rate'] : null,
            'vat_amount' => isset($data['vat_amount']) ? (float) $data['vat_amount'] : null,
            'type' => $data['transaction_type'] ?? null,
            'is_bewirtung' => $isBewirtung,
            'bewirtete_person' => $data['bewirtete_person'] ?? null,
            'anlass' => $data['anlass'] ?? null,
            'ort' => $data['ort'] ?? null,
            'confidence' => $data['confidence'] ?? null,
        ];
    }

    /**
     * Normalize invoice data to match expected format
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
     * Normalize quote data to match expected format
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
     * Get known correspondents from Paperless (excluding own company)
     *
     * @return array List of correspondent names
     */
    private function getKnownCorrespondents(): array
    {
        try {
            $paperlessService = app(\App\Services\PaperlessService::class);

            return $paperlessService->getCorrespondentNamesForAI();
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Paperless correspondents', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Test connection to configured AI providers
     *
     * @return array Status of each provider
     */
    public function testProviders(): array
    {
        return $this->aiService->testProviders();
    }
}
