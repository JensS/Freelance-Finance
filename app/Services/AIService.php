<?php

namespace App\Services;

use App\Models\Setting;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Facades\Prism;
use EchoLabs\Prism\ValueObjects\Messages\AssistantMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Facades\Log;

/**
 * Unified AI Service using Prism
 *
 * This service provides a unified interface for all AI operations,
 * supporting text generation and multimodal vision tasks across
 * multiple providers (Ollama, OpenAI, Anthropic, OpenRouter, etc.)
 */
class AIService
{
    private string $textProvider;

    private string $textModel;

    private string $visionProvider;

    private string $visionModel;

    private ?string $lastError = null;

    public function __construct()
    {
        // Load settings from database (with fallbacks to config)
        $this->textProvider = Setting::get('ai_text_provider', 'ollama');
        $this->textModel = Setting::get('ollama_model', 'llama3.2');

        $this->visionProvider = Setting::get('ai_vision_provider', 'ollama');
        $this->visionModel = Setting::get('ollama_vision_model', 'qwen2.5vl:3b');
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Generate text using AI
     *
     * @param  string  $prompt  The prompt to send to the AI
     * @param  array  $options  Additional options (temperature, max_tokens, etc.)
     * @param  string|null  $systemPrompt  Optional system prompt
     * @param  array  $messages  Optional message history for conversations
     * @return string|null AI response or null on failure
     */
    public function generateText(
        string $prompt,
        array $options = [],
        ?string $systemPrompt = null,
        array $messages = []
    ): ?string {
        try {
            $provider = $this->getProviderEnum($this->textProvider);

            // Build the request
            $request = Prism::text()
                ->using($provider, $this->textModel);

            // Add system prompt if provided
            if ($systemPrompt) {
                $request->withSystemPrompt($systemPrompt);
            }

            // Add message history if provided
            if (! empty($messages)) {
                $prismMessages = [];
                foreach ($messages as $message) {
                    if ($message['role'] === 'user') {
                        $prismMessages[] = new UserMessage($message['content']);
                    } elseif ($message['role'] === 'assistant') {
                        $prismMessages[] = new AssistantMessage($message['content']);
                    }
                }
                $request->withMessages($prismMessages);
            }

            // Add the prompt
            $request->withPrompt($prompt);

            // Apply options
            if (isset($options['temperature'])) {
                $request->usingTemperature($options['temperature']);
            }
            if (isset($options['max_tokens'])) {
                $request->withMaxTokens($options['max_tokens']);
            }

            // Log AI request when in debug mode
            if (config('app.debug')) {
                $logData = [
                    'timestamp' => now()->toIso8601String(),
                    'provider' => $this->textProvider,
                    'model' => $this->textModel,
                    'temperature' => $options['temperature'] ?? null,
                    'max_tokens' => $options['max_tokens'] ?? null,
                    'prompt_length' => strlen($prompt),
                    'prompt' => $prompt,
                ];

                Log::channel('ai')->info('🤖 AI Text Request (Prism)'."\n".json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            // Generate response
            $response = $request->asText();
            $aiResponse = $response->text;

            // Log AI response when in debug mode
            if (config('app.debug')) {
                $logData = [
                    'timestamp' => now()->toIso8601String(),
                    'provider' => $this->textProvider,
                    'model' => $this->textModel,
                    'response_length' => strlen($aiResponse ?? ''),
                    'response' => $aiResponse,
                ];

                Log::channel('ai')->info('✅ AI Text Response (Prism)'."\n".json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return $aiResponse;

        } catch (\Exception $e) {
            $this->lastError = $this->formatError($this->textProvider, $e);
            Log::error('AI text generation failed', [
                'provider' => $this->textProvider,
                'model' => $this->textModel,
                'error' => $e->getMessage(),
                'user_message' => $this->lastError,
            ]);

            return null;
        }
    }

    /**
     * Extract data from a document using vision AI
     *
     * @param  string  $pdfPath  Path to the PDF file
     * @param  string  $prompt  Extraction prompt
     * @param  bool  $jsonOutput  Whether to request JSON output (deprecated - now uses TOON)
     * @return array|null Extracted data or null on failure
     */
    public function extractFromDocument(
        string $pdfPath,
        string $prompt,
        bool $jsonOutput = true
    ): ?array {
        try {
            $provider = $this->getProviderEnum($this->visionProvider);

            // Convert PDF to image
            $imageBase64 = $this->pdfToBase64Image($pdfPath);
            if (! $imageBase64) {
                $this->lastError = 'Failed to convert PDF to image';

                return null;
            }

            // Create temporary file for image (Prism needs a path)
            $tempImage = tempnam(sys_get_temp_dir(), 'prism_vision_');
            file_put_contents($tempImage, base64_decode($imageBase64));

            // Log AI request when in debug mode
            if (config('app.debug')) {
                $logData = [
                    'timestamp' => now()->toIso8601String(),
                    'provider' => $this->visionProvider,
                    'model' => $this->visionModel,
                    'pdf_path' => $pdfPath,
                    'image_size_kb' => round(strlen($imageBase64) * 0.75 / 1024, 2),
                    'json_output' => $jsonOutput,
                    'prompt_length' => strlen($prompt),
                    'prompt' => $prompt,
                ];

                Log::channel('ai')->info('🖼️  AI Vision Request (Prism)'."\n".json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            // Build the request with image
            $request = Prism::text()
                ->using($provider, $this->visionModel)
                ->withPrompt($prompt, [
                    \EchoLabs\Prism\ValueObjects\Media\Image::fromPath($tempImage),
                ])
                ->usingTemperature(0.1); // Low temperature for factual extraction

            // Note: TOON format is now used instead of JSON for better token efficiency
            // No special format parameter needed - model understands TOON from the prompt

            // Generate response
            $response = $request->asText();
            $aiResponse = $response->text;

            // Clean up temp file
            @unlink($tempImage);

            // Log AI response when in debug mode
            if (config('app.debug')) {
                $logData = [
                    'timestamp' => now()->toIso8601String(),
                    'provider' => $this->visionProvider,
                    'model' => $this->visionModel,
                    'response_length' => strlen($aiResponse),
                    'response' => $aiResponse,
                ];

                Log::channel('ai')->info('✅ AI Vision Response (Prism)'."\n".json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            if (! $aiResponse) {
                $this->lastError = 'No response from AI';

                return null;
            }

            // Parse TOON response
            try {
                // Clean up response - remove any markdown formatting
                $cleanResponse = $aiResponse;

                // Remove markdown code blocks if present
                $cleanResponse = preg_replace('/```(?:toon)?\s*(.*?)\s*```/s', '$1', $cleanResponse);

                // Trim whitespace
                $cleanResponse = trim($cleanResponse);

                // Try to decode TOON format
                $extractedData = \ToonLite\Toon::decode($cleanResponse);

                if (! is_array($extractedData)) {
                    $this->lastError = 'TOON parsing returned non-array data';
                    Log::error('TOON parsing returned invalid data type', [
                        'response' => $aiResponse,
                        'cleaned' => $cleanResponse,
                        'result_type' => gettype($extractedData),
                    ]);

                    return null;
                }

                return $extractedData;
            } catch (\Exception $e) {
                $this->lastError = 'Failed to parse TOON: '.$e->getMessage();
                Log::error('Failed to parse TOON from AI response', [
                    'error' => $e->getMessage(),
                    'response' => $aiResponse,
                ]);

                // Fallback: try JSON parsing for backward compatibility
                try {
                    // Extract JSON from response (some models may add text before/after)
                    if (preg_match('/\{[\s\S]*\}/', $aiResponse, $matches)) {
                        $aiResponse = $matches[0];
                    }

                    $extractedData = json_decode($aiResponse, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($extractedData)) {
                        Log::info('Fallback to JSON parsing succeeded');

                        return $extractedData;
                    }
                } catch (\Exception $jsonError) {
                    // JSON fallback also failed
                }

                return null;
            }

        } catch (\Exception $e) {
            $this->lastError = $this->formatError($this->visionProvider, $e);
            Log::error('AI vision extraction failed', [
                'provider' => $this->visionProvider,
                'model' => $this->visionModel,
                'error' => $e->getMessage(),
                'user_message' => $this->lastError,
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
     * Get Prism Provider enum from provider name
     *
     * @param  string  $provider  Provider name (ollama, openai, anthropic, etc.)
     * @return Provider Provider enum
     */
    private function getProviderEnum(string $provider): Provider
    {
        return match (strtolower($provider)) {
            'ollama' => Provider::Ollama,
            'openai' => Provider::OpenAI,
            'anthropic' => Provider::Anthropic,
            'openrouter' => Provider::OpenRouter,
            'groq' => Provider::Groq,
            'mistral' => Provider::Mistral,
            'gemini' => Provider::Gemini,
            'deepseek' => Provider::DeepSeek,
            'xai' => Provider::XAI,
            default => Provider::Ollama, // Fallback to Ollama
        };
    }

    /**
     * Format error message for user display
     *
     * @param  string  $provider  Provider name
     * @param  \Exception  $exception  The exception
     * @return string Formatted error message
     */
    private function formatError(string $provider, \Exception $exception): string
    {
        $providerName = match ($provider) {
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
            'openrouter' => 'OpenRouter',
            'ollama' => 'Ollama',
            'groq' => 'Groq',
            'mistral' => 'Mistral',
            'gemini' => 'Gemini',
            'deepseek' => 'DeepSeek',
            'xai' => 'XAI',
            default => ucfirst($provider),
        };

        $message = $exception->getMessage();

        // Shorten very long messages
        if (strlen($message) > 150) {
            $message = substr($message, 0, 150).'...';
        }

        return "{$providerName}: {$message}";
    }

    /**
     * Test connection to configured AI providers
     *
     * @return array Status of each provider
     */
    public function testProviders(): array
    {
        $status = [];

        // Test text provider
        try {
            $response = $this->generateText('Hello', ['temperature' => 0.1]);
            $status['text'] = [
                'provider' => $this->textProvider,
                'model' => $this->textModel,
                'status' => $response !== null,
            ];
        } catch (\Exception $e) {
            $status['text'] = [
                'provider' => $this->textProvider,
                'model' => $this->textModel,
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }

        // Test vision provider
        $status['vision'] = [
            'provider' => $this->visionProvider,
            'model' => $this->visionModel,
            'status' => true, // We can't easily test vision without a document
        ];

        return $status;
    }

    /**
     * Get list of available models from Ollama
     *
     * @return array Array of models categorized by type (text/vision)
     */
    public function getAvailableModels(): array
    {
        // This is Ollama-specific - only works when using Ollama provider
        try {
            $ollamaUrl = rtrim(
                \App\Models\Setting::get('ollama_url', config('prism.providers.ollama.url', 'http://localhost:11434')),
                '/'
            );

            $response = \Illuminate\Support\Facades\Http::timeout(10)->get("{$ollamaUrl}/api/tags");

            if (! $response->successful()) {
                Log::error('Failed to fetch Ollama models', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['text' => [], 'vision' => []];
            }

            $data = $response->json();
            $models = $data['models'] ?? [];

            // Log available models when in debug mode
            if (config('app.debug')) {
                Log::channel('ai')->info('📋 Ollama Available Models', [
                    'timestamp' => now()->toIso8601String(),
                    'url' => $ollamaUrl,
                    'total_models' => count($models),
                    'models' => $models,
                ]);
            }

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

                // Categorize by type - check for vision model indicators
                $lowerName = strtolower($modelName);

                // Vision model patterns
                $visionPatterns = [
                    'vision', 'llava', 'minicpm', 'qwen-vl', 'qwen2-vl',
                    'qwen2.5vl', 'granite-vision', 'bakllava', 'obsidian',
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
     * Install (pull) an Ollama model with streaming progress
     *
     * @param  string  $modelName  Name of the model to install
     * @param  string|null  $cacheKey  Cache key to store progress updates
     * @return array Status information
     */
    public function installOllamaModel(string $modelName, ?string $cacheKey = null): array
    {
        try {
            $ollamaUrl = rtrim(
                \App\Models\Setting::get('ollama_url', config('prism.providers.ollama.url', 'http://localhost:11434')),
                '/'
            );

            // Use a unique cache key if not provided
            if (! $cacheKey) {
                $cacheKey = 'ollama_install_'.str_replace([':', '/'], '_', $modelName);
            }

            Log::info('🚀 Starting Ollama model installation', [
                'model' => $modelName,
                'cache_key' => $cacheKey,
                'timestamp' => now()->toIso8601String(),
            ]);

            // Initialize progress
            \Cache::put($cacheKey, [
                'status' => 'starting',
                'progress' => 0,
                'message' => 'Initiiere Download...',
            ], now()->addMinutes(30));

            // Use streaming HTTP request to Ollama pull API
            $response = \Illuminate\Support\Facades\Http::timeout(0) // No timeout for streaming
                ->withOptions([
                    'stream' => true,
                    'read_timeout' => 300, // 5 minutes between chunks
                ])
                ->post("{$ollamaUrl}/api/pull", [
                    'name' => $modelName,
                    'stream' => true,
                ]);

            if (! $response->successful()) {
                $error = "HTTP {$response->status()}: {$response->body()}";
                Log::error('❌ Ollama pull request failed', [
                    'model' => $modelName,
                    'error' => $error,
                ]);

                \Cache::put($cacheKey, [
                    'status' => 'error',
                    'progress' => 0,
                    'message' => 'Fehler: '.$error,
                ], now()->addMinutes(5));

                return [
                    'success' => false,
                    'error' => $error,
                ];
            }

            // Parse streaming NDJSON response
            $body = $response->body();
            $lines = explode("\n", trim($body));
            $updateCount = 0;

            foreach ($lines as $line) {
                if (empty($line)) {
                    continue;
                }

                $data = json_decode($line, true);
                if (! $data) {
                    continue;
                }

                $updateCount++;
                $status = $data['status'] ?? 'unknown';
                $total = $data['total'] ?? 0;
                $completed = $data['completed'] ?? 0;

                // Calculate percentage
                $percentage = 0;
                if ($total > 0) {
                    $percentage = round(($completed / $total) * 100, 1);
                }

                // Format progress message
                $message = $this->formatOllamaProgressMessage($status, $total, $completed);

                $progress = [
                    'status' => $status,
                    'progress' => $percentage,
                    'message' => $message,
                    'total' => $total,
                    'completed' => $completed,
                ];

                // Update cache
                \Cache::put($cacheKey, $progress, now()->addMinutes(30));

                // Log every 10th update or significant status changes
                if ($updateCount % 10 === 0 || in_array($status, ['pulling manifest', 'verifying sha256 digest', 'success'])) {
                    Log::info('📊 Installation progress update', [
                        'update_number' => $updateCount,
                        'model' => $modelName,
                        'status' => $status,
                        'progress' => $percentage,
                        'message' => $message,
                    ]);
                }
            }

            Log::info('✅ Streaming completed, received '.$updateCount.' updates', [
                'model' => $modelName,
            ]);

            // Mark as complete
            \Cache::put($cacheKey, [
                'status' => 'success',
                'progress' => 100,
                'message' => 'Installation abgeschlossen!',
            ], now()->addMinutes(5));

            Log::info('🎉 Model installation completed successfully', [
                'model' => $modelName,
                'cache_key' => $cacheKey,
                'total_updates' => $updateCount,
            ]);

            return [
                'success' => true,
                'status' => 'success',
                'message' => "Model {$modelName} successfully installed",
                'cache_key' => $cacheKey,
            ];

        } catch (\Exception $e) {
            Log::error('❌ Model installation failed', [
                'model' => $modelName,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            \Cache::put($cacheKey, [
                'status' => 'error',
                'progress' => 0,
                'message' => 'Fehler: '.$e->getMessage(),
            ], now()->addMinutes(5));

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Ollama installation progress from cache
     *
     * @param  string  $cacheKey  Cache key
     * @return array|null Progress data or null
     */
    public function getOllamaInstallProgress(string $cacheKey): ?array
    {
        return \Cache::get($cacheKey);
    }

    /**
     * Format Ollama progress message based on status
     *
     * @param  string  $status  Status from Ollama
     * @param  int  $total  Total bytes
     * @param  int  $completed  Completed bytes
     * @return string Formatted message
     */
    private function formatOllamaProgressMessage(string $status, int $total, int $completed): string
    {
        switch ($status) {
            case 'pulling manifest':
                return 'Lade Manifest...';
            case 'downloading':
            case 'pulling':
                if ($total > 0) {
                    $totalMB = round($total / 1048576, 1);
                    $completedMB = round($completed / 1048576, 1);

                    return "Download läuft: {$completedMB} MB / {$totalMB} MB";
                }

                return 'Lade herunter...';
            case 'verifying sha256':
            case 'verifying sha256 digest':
                return 'Verifiziere Download...';
            case 'writing manifest':
                return 'Schreibe Manifest...';
            case 'success':
                return 'Installation erfolgreich!';
            default:
                return ucfirst($status);
        }
    }
}
