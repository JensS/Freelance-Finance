<?php

namespace App\Jobs;

use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InstallOllamaModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour - for very large models

    public function __construct(
        public string $modelName,
        public string $cacheKey
    ) {}

    /**
     * Execute the job
     */
    public function handle(): void
    {
        Log::info('🚀 InstallOllamaModel job started', [
            'model' => $this->modelName,
            'cache_key' => $this->cacheKey,
            'job_id' => $this->job?->getJobId(),
            'attempt' => $this->attempts(),
        ]);

        try {
            $aiService = app(AIService::class);

            Log::info('📞 Calling AIService->installOllamaModel()', [
                'model' => $this->modelName,
                'cache_key' => $this->cacheKey,
            ]);

            $result = $aiService->installOllamaModel($this->modelName, $this->cacheKey);

            Log::info('📥 AIService->installOllamaModel() returned', [
                'model' => $this->modelName,
                'result' => $result,
            ]);

            if (! $result['success']) {
                Log::error('❌ Ollama model installation job failed', [
                    'model' => $this->modelName,
                    'error' => $result['error'] ?? 'Unknown error',
                    'cache_key' => $this->cacheKey,
                ]);
            } else {
                Log::info('✅ Ollama model installation job succeeded', [
                    'model' => $this->modelName,
                    'cache_key' => $this->cacheKey,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('💥 Ollama model installation job exception', [
                'model' => $this->modelName,
                'cache_key' => $this->cacheKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update cache with error
            \Cache::put($this->cacheKey, [
                'status' => 'error',
                'progress' => 0,
                'message' => 'Fehler: '.$e->getMessage(),
            ], now()->addMinutes(5));
        }
    }
}
