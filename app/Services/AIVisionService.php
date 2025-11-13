<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Unified AI Vision Service
 *
 * Supports multiple AI providers with automatic fallback:
 * - Ollama (local)
 * - OpenAI (GPT-4o, GPT-4 Turbo, GPT-4o-mini)
 * - Anthropic (Claude 3.5 Sonnet, Claude 3 Opus, Claude 3 Haiku)
 * - OpenRouter (proxy to various models)
 */
class AIVisionService
{
    private string $primaryProvider;
    private string $fallbackProvider;

    public function __construct()
    {
        $this->primaryProvider = Setting::get('ai_provider', 'ollama');
        $this->fallbackProvider = Setting::get('ai_fallback_provider', 'none');
    }

    /**
     * Extract receipt data from PDF
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @return array|null Extracted data or null on failure
     */
    public function extractReceiptData(string $pdfPath): ?array
    {
        // Try primary provider
        $result = $this->tryProvider($this->primaryProvider, 'receipt', $pdfPath);

        if ($result) {
            return $result;
        }

        // Try fallback if configured
        if ($this->fallbackProvider !== 'none') {
            Log::info('Primary provider failed, trying fallback', [
                'primary' => $this->primaryProvider,
                'fallback' => $this->fallbackProvider,
            ]);

            $result = $this->tryProvider($this->fallbackProvider, 'receipt', $pdfPath);

            if ($result) {
                return $result;
            }
        }

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
        // Try primary provider
        $result = $this->tryProvider($this->primaryProvider, 'invoice', $pdfPath);

        if ($result) {
            return $result;
        }

        // Try fallback if configured
        if ($this->fallbackProvider !== 'none') {
            Log::info('Primary provider failed, trying fallback', [
                'primary' => $this->primaryProvider,
                'fallback' => $this->fallbackProvider,
            ]);

            $result = $this->tryProvider($this->fallbackProvider, 'invoice', $pdfPath);

            if ($result) {
                return $result;
            }
        }

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
        // Try primary provider
        $result = $this->tryProvider($this->primaryProvider, 'quote', $pdfPath);

        if ($result) {
            return $result;
        }

        // Try fallback if configured
        if ($this->fallbackProvider !== 'none') {
            Log::info('Primary provider failed, trying fallback', [
                'primary' => $this->primaryProvider,
                'fallback' => $this->fallbackProvider,
            ]);

            $result = $this->tryProvider($this->fallbackProvider, 'quote', $pdfPath);

            if ($result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Try a specific provider for extraction
     *
     * @param  string  $provider  Provider name (ollama, openai, anthropic, openrouter)
     * @param  string  $type  Document type (receipt, invoice, quote)
     * @param  string  $pdfPath  Path to PDF file
     * @return array|null Extracted data or null on failure
     */
    private function tryProvider(string $provider, string $type, string $pdfPath): ?array
    {
        try {
            switch ($provider) {
                case 'ollama':
                    return $this->extractWithOllama($type, $pdfPath);
                case 'openai':
                    return $this->extractWithOpenAI($type, $pdfPath);
                case 'anthropic':
                    return $this->extractWithAnthropic($type, $pdfPath);
                case 'openrouter':
                    return $this->extractWithOpenRouter($type, $pdfPath);
                default:
                    Log::warning('Unknown AI provider', ['provider' => $provider]);
                    return null;
            }
        } catch (\Exception $e) {
            Log::error('AI extraction failed', [
                'provider' => $provider,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract using Ollama (local)
     */
    private function extractWithOllama(string $type, string $pdfPath): ?array
    {
        $ollamaService = app(OllamaVisionService::class);

        switch ($type) {
            case 'receipt':
                return $ollamaService->extractReceiptData($pdfPath);
            case 'invoice':
                return $ollamaService->extractInvoiceData($pdfPath);
            case 'quote':
                return $ollamaService->extractQuoteData($pdfPath);
            default:
                return null;
        }
    }

    /**
     * Extract using OpenAI GPT-4o/GPT-4 Turbo
     */
    private function extractWithOpenAI(string $type, string $pdfPath): ?array
    {
        $apiKey = Setting::get('openai_api_key');
        $model = Setting::get('openai_model', 'gpt-4o');

        if (empty($apiKey)) {
            Log::warning('OpenAI API key not configured');
            return null;
        }

        // Convert PDF to base64 image
        $imageBase64 = $this->pdfToBase64Image($pdfPath);
        if (! $imageBase64) {
            return null;
        }

        // Get appropriate prompt
        $prompt = $this->getPromptForType($type);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:image/jpeg;base64,' . $imageBase64,
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0.1,
            'response_format' => ['type' => 'json_object'],
        ]);

        if (! $response->successful()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? null;

        if (! $content) {
            return null;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse OpenAI JSON response', [
                'error' => json_last_error_msg(),
                'content' => $content,
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Extract using Anthropic Claude
     */
    private function extractWithAnthropic(string $type, string $pdfPath): ?array
    {
        $apiKey = Setting::get('anthropic_api_key');
        $model = Setting::get('anthropic_model', 'claude-3-5-sonnet-20241022');

        if (empty($apiKey)) {
            Log::warning('Anthropic API key not configured');
            return null;
        }

        // Convert PDF to base64 image
        $imageBase64 = $this->pdfToBase64Image($pdfPath);
        if (! $imageBase64) {
            return null;
        }

        // Get appropriate prompt
        $prompt = $this->getPromptForType($type);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 4096,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => 'image/jpeg',
                                'data' => $imageBase64,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt . "\n\nIMPORTANT: Return ONLY valid JSON, no additional text or explanation.",
                        ],
                    ],
                ],
            ],
            'temperature' => 0.1,
        ]);

        if (! $response->successful()) {
            Log::error('Anthropic API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $result = $response->json();
        $content = $result['content'][0]['text'] ?? null;

        if (! $content) {
            return null;
        }

        // Extract JSON from response (Claude sometimes adds text before/after)
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $content = $matches[0];
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse Anthropic JSON response', [
                'error' => json_last_error_msg(),
                'content' => $content,
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Extract using OpenRouter
     */
    private function extractWithOpenRouter(string $type, string $pdfPath): ?array
    {
        $apiKey = Setting::get('openrouter_api_key');
        $model = Setting::get('openrouter_model', 'anthropic/claude-3.5-sonnet');

        if (empty($apiKey)) {
            Log::warning('OpenRouter API key not configured');
            return null;
        }

        // Convert PDF to base64 image
        $imageBase64 = $this->pdfToBase64Image($pdfPath);
        if (! $imageBase64) {
            return null;
        }

        // Get appropriate prompt
        $prompt = $this->getPromptForType($type);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => 'Freelance Finance Hub',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:image/jpeg;base64,' . $imageBase64,
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0.1,
        ]);

        if (! $response->successful()) {
            Log::error('OpenRouter API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? null;

        if (! $content) {
            return null;
        }

        // Extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $content = $matches[0];
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse OpenRouter JSON response', [
                'error' => json_last_error_msg(),
                'content' => $content,
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Get prompt for document type
     */
    private function getPromptForType(string $type): string
    {
        $ollamaService = app(OllamaVisionService::class);

        switch ($type) {
            case 'receipt':
                return $ollamaService->buildExtractionPrompt();
            case 'invoice':
                return $ollamaService->buildInvoiceExtractionPrompt();
            case 'quote':
                return $ollamaService->buildQuoteExtractionPrompt();
            default:
                return '';
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
     * Test connection to configured AI providers
     *
     * @return array Status of each provider
     */
    public function testProviders(): array
    {
        $status = [];

        // Test Ollama
        try {
            $ollamaService = app(OllamaVisionService::class);
            $status['ollama'] = $ollamaService->testConnection();
        } catch (\Exception $e) {
            $status['ollama'] = false;
        }

        // Test OpenAI
        $openaiKey = Setting::get('openai_api_key');
        if ($openaiKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $openaiKey,
                ])->timeout(5)->get('https://api.openai.com/v1/models');
                $status['openai'] = $response->successful();
            } catch (\Exception $e) {
                $status['openai'] = false;
            }
        } else {
            $status['openai'] = false;
        }

        // Test Anthropic
        $anthropicKey = Setting::get('anthropic_api_key');
        if ($anthropicKey) {
            // Anthropic doesn't have a simple test endpoint, so we just check if key is set
            $status['anthropic'] = !empty($anthropicKey);
        } else {
            $status['anthropic'] = false;
        }

        // Test OpenRouter
        $openrouterKey = Setting::get('openrouter_api_key');
        if ($openrouterKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $openrouterKey,
                ])->timeout(5)->get('https://openrouter.ai/api/v1/models');
                $status['openrouter'] = $response->successful();
            } catch (\Exception $e) {
                $status['openrouter'] = false;
            }
        } else {
            $status['openrouter'] = false;
        }

        return $status;
    }
}
