<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private string $baseUrl;

    private string $model;

    public function __construct()
    {
        // Try to get from database settings first, fall back to .env
        $this->baseUrl = rtrim(\App\Models\Setting::get('ollama_url', config('services.ollama.url', 'http://localhost:11434')), '/');
        $this->model = \App\Models\Setting::get('ollama_model', config('services.ollama.model', 'llama3.2'));
    }

    /**
     * Generate AI completion using Ollama
     *
     * @param  string  $prompt  The prompt to send to the AI
     * @param  array  $options  Additional options (temperature, etc.)
     * @return string|null AI response or null on failure
     */
    public function generate(string $prompt, array $options = []): ?string
    {
        try {
            $response = Http::timeout(60)->post($this->baseUrl.'/api/generate', [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => array_merge([
                    'temperature' => 0.7,
                ], $options),
            ]);

            if ($response->successful()) {
                return $response->json('response');
            }

            Log::error('Ollama generation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Ollama generation exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Analyze monthly financial data and provide insights
     *
     * @param  array  $data  Financial data for the month
     * @return string|null AI insights
     */
    public function analyzeMonthlyFinances(array $data): ?string
    {
        $prompt = $this->buildFinancialAnalysisPrompt($data);

        return $this->generate($prompt);
    }

    /**
     * Categorize a transaction using AI
     *
     * @param  string  $description  Transaction description
     * @param  float  $amount  Transaction amount
     * @return string|null Suggested category
     */
    public function categorizeTransaction(string $description, float $amount): ?string
    {
        $prompt = <<<PROMPT
You are a financial categorization assistant for a German freelancer.

Transaction: {$description}
Amount: {$amount} EUR

Based on the description and amount, what is the most appropriate category for this transaction?

Choose from: income, rent, utilities, groceries, transport, insurance, subscription, tax, business_expense, personal_expense, or other.

Respond with ONLY the category name, nothing else.
PROMPT;

        return $this->generate($prompt, ['temperature' => 0.3]);
    }

    /**
     * Get tax optimization suggestions
     *
     * @param  array  $yearData  Annual financial data
     * @return string|null AI suggestions
     */
    public function getTaxOptimizationSuggestions(array $yearData): ?string
    {
        $prompt = <<<PROMPT
You are a tax advisor assistant for German freelancers.

Annual Financial Summary:
- Total Income: {$yearData['total_income']} EUR
- Total Expenses: {$yearData['total_expenses']} EUR
- Business Expenses: {$yearData['business_expenses']} EUR
- Invoices Issued: {$yearData['invoices_count']}
- Average Invoice Amount: {$yearData['avg_invoice']} EUR

Based on German tax law for freelancers (§18 EStG), provide 3-5 specific, actionable tax optimization suggestions for the upcoming year.

Keep suggestions practical and focused on legitimate deductions and strategies.
PROMPT;

        return $this->generate($prompt);
    }

    /**
     * Detect unusual spending patterns
     *
     * @param  array  $transactions  Recent transactions
     * @return string|null AI analysis
     */
    public function detectAnomalies(array $transactions): ?string
    {
        $transactionList = collect($transactions)
            ->map(fn ($t) => "- {$t['date']}: {$t['description']} ({$t['amount']} EUR)")
            ->join("\n");

        $prompt = <<<PROMPT
You are a financial analyst. Review these recent transactions and identify any unusual patterns or anomalies:

{$transactionList}

Identify:
1. Duplicate charges
2. Unusually large expenses
3. Suspicious transactions
4. Potential savings opportunities

Be concise and specific.
PROMPT;

        return $this->generate($prompt);
    }

    /**
     * Build financial analysis prompt
     */
    private function buildFinancialAnalysisPrompt(array $data): string
    {
        return <<<PROMPT
You are a financial advisor for a German freelancer. Analyze this month's financial data:

Income:
- Total Revenue: {$data['revenue']} EUR
- Invoices Paid: {$data['invoices_paid']}
- Average Invoice: {$data['avg_invoice']} EUR

Expenses:
- Total Expenses: {$data['expenses']} EUR
- Business Expenses: {$data['business_expenses']} EUR
- Personal Expenses: {$data['personal_expenses']} EUR

Key Metrics:
- Profit Margin: {$data['profit_margin']}%
- Savings Rate: {$data['savings_rate']}%

Provide:
1. A brief assessment (2-3 sentences)
2. 3 specific recommendations to improve financial health
3. One warning if there are concerning trends

Be concise, practical, and specific to German freelancing.
PROMPT;
    }

    /**
     * Generate invoice payment reminder text
     *
     * @param  array  $invoiceData  Invoice details
     * @return string|null AI-generated reminder text
     */
    public function generatePaymentReminder(array $invoiceData): ?string
    {
        $prompt = <<<PROMPT
Write a professional but friendly payment reminder email in German for this invoice:

Invoice Number: {$invoiceData['number']}
Customer: {$invoiceData['customer']}
Amount: {$invoiceData['amount']} EUR
Due Date: {$invoiceData['due_date']}
Days Overdue: {$invoiceData['days_overdue']}

The email should:
- Be polite and professional
- Reference the invoice details
- Request payment
- Offer to help with any questions
- Be 3-4 sentences maximum

Provide ONLY the email body text, no subject line.
PROMPT;

        return $this->generate($prompt);
    }

    /**
     * Test Ollama connection
     *
     * @return bool Connection status
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl.'/api/tags');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Ollama connection test failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get list of available models from Ollama
     *
     * @return array Array of models with name and type
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl.'/api/tags');

            if (! $response->successful()) {
                Log::error('Failed to fetch Ollama models', [
                    'status' => $response->status(),
                ]);

                return [];
            }

            $data = $response->json();
            $models = $data['models'] ?? [];

            $result = [
                'text' => [],
                'vision' => [],
            ];

            foreach ($models as $model) {
                $modelName = $model['name'] ?? null;

                if (! $modelName) {
                    continue;
                }

                $modelData = [
                    'name' => $modelName,
                    'size' => $model['size'] ?? null,
                    'modified' => $model['modified_at'] ?? null,
                ];

                // Categorize models by type - check for vision model indicators
                $lowerName = strtolower($modelName);

                // Vision model patterns
                $visionPatterns = [
                    'vision',      // llama3.2-vision, granite-3.2-vision
                    'llava',       // llava, llava-phi3
                    'minicpm',     // minicpm-v
                    'qwen-vl',     // qwen-vl, qwen2-vl
                    'qwen2-vl',    // qwen2-vl variants
                    'granite-vision', // granite vision variants
                    'bakllava',    // bakllava
                    'obsidian',    // obsidian (vision model)
                ];

                $isVisionModel = false;
                foreach ($visionPatterns as $pattern) {
                    if (str_contains($lowerName, $pattern)) {
                        $isVisionModel = true;
                        break;
                    }
                }

                if ($isVisionModel) {
                    $result['vision'][] = $modelData;
                } else {
                    $result['text'][] = $modelData;
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to fetch Ollama models', ['error' => $e->getMessage()]);

            return ['text' => [], 'vision' => []];
        }
    }

    /**
     * Install (pull) a model from Ollama
     *
     * @param  string  $modelName  Name of the model to install
     * @return array Status information
     */
    public function installModel(string $modelName): array
    {
        try {
            // Trigger the model pull - this is a streaming endpoint that downloads in background
            // We just need to verify the request was accepted
            $response = Http::timeout(10)->post($this->baseUrl.'/api/pull', [
                'name' => $modelName,
            ]);

            if (! $response->successful()) {
                $statusCode = $response->status();
                $body = $response->body();

                Log::error('Model installation request failed', [
                    'model' => $modelName,
                    'status' => $statusCode,
                    'response' => $body,
                ]);

                return [
                    'success' => false,
                    'error' => "Failed to start model installation (HTTP {$statusCode}). Check Ollama logs.",
                ];
            }

            // Request accepted - model is now downloading in the background
            return [
                'success' => true,
                'status' => 'downloading',
                'message' => "Model {$modelName} installation started successfully",
            ];

        } catch (\Exception $e) {
            Log::error('Model installation failed', [
                'model' => $modelName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
