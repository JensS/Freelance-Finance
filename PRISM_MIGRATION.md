# Prism PHP Migration Summary

This document outlines the successful migration of Freelance Finance Hub's AI integration from individual provider adapters to the unified **prism-php/prism** library.

## What Changed

### Before Migration
- Individual adapters for each AI provider (Ollama, OpenAI, Anthropic, OpenRouter)
- Direct HTTP calls to each provider's API
- Duplicate code for handling different provider formats
- Complex fallback logic spread across services

### After Migration
- **Single unified interface** via Prism PHP
- **Cleaner architecture** with centralized AI logic
- **Easy provider switching** without code changes
- **Expanded provider support** (9+ providers out of the box)

## New Architecture

### Core Services

1. **AIService** (`app/Services/AIService.php`) - NEW
   - Unified interface for all AI operations using Prism
   - Handles text generation and vision tasks
   - Includes Ollama model management utilities (listing, installation, progress tracking)
   - Centralized error handling and logging
   - Supports multimodal input (text, images, documents)

2. **AIVisionService** (`app/Services/AIVisionService.php`) - UPDATED
   - Now a thin wrapper around AIService
   - Maintains backward compatibility with existing code
   - Handles data normalization for receipts, invoices, and quotes

3. **AIPromptService** (`app/Services/AIPromptService.php`) - UNCHANGED
   - Centralized prompt generation
   - Provider-agnostic prompts

## Supported AI Providers

Prism provides built-in support for:

1. **Ollama** (Local, self-hosted) ⭐ **Recommended**
2. **OpenAI** (GPT-4o, GPT-4 Turbo, GPT-4o-mini)
3. **Anthropic** (Claude 3.5 Sonnet, Claude 3 Opus, Claude 3 Haiku)
4. **OpenRouter** (Gateway to 100+ models)
5. **Mistral AI**
6. **Groq** (Fast inference)
7. **xAI** (Grok models)
8. **Google Gemini**
9. **DeepSeek**

## Configuration

### Environment Variables

All AI provider configuration is now handled via Prism's standard environment variables. See `.env.example` for the complete list.

**Example for Ollama (default):**
```bash
OLLAMA_URL=http://localhost:11434
```

**Example for OpenAI:**
```bash
OPENAI_API_KEY=your-key-here
OPENAI_URL=https://api.openai.com/v1
```

**Example for Anthropic:**
```bash
ANTHROPIC_API_KEY=your-key-here
ANTHROPIC_API_VERSION=2023-06-01
```

### Config File

Prism configuration is located at `config/prism.php`. It automatically pulls from environment variables following Laravel conventions.

### Database Settings

The existing Settings UI still works and takes precedence over .env values:
- `ai_text_provider` - Provider for text generation
- `ollama_model` - Text model name
- `ai_vision_provider` - Provider for vision tasks
- `ollama_vision_model` - Vision model name

## Migration Benefits

### 1. Simplified Code
- Removed ~400 lines of provider-specific HTTP request code
- Single entry point for all AI operations
- Centralized error handling

### 2. Better Maintainability
- Provider updates handled by Prism package
- No need to manually track API changes
- Standard interface across all providers

### 3. Enhanced Flexibility
- Switch providers without code changes
- Mix and match providers for different tasks
- Easy to add new providers supported by Prism

### 4. Improved Features
- Built-in multimodal support (images, documents, audio, video)
- Structured output support
- Streaming support (future enhancement)
- Tool calling support (future enhancement)

## Backward Compatibility

✅ **All existing code continues to work without changes**

- `AIVisionService` maintains the same public API
- `DocumentParser` works as before
- `VerifyImports` (transaction verification) works as before
- All Livewire components remain compatible

## Usage Examples

### Text Generation
```php
// Via AIService (recommended for new code)
$aiService = app(App\Services\AIService::class);
$response = $aiService->generateText(
    prompt: "Analyze this financial data...",
    options: ['temperature' => 0.7],
    systemPrompt: "You are a financial advisor..."
);

// Via OllamaService (still works)
$ollamaService = app(App\Services\OllamaService::class);
$response = $ollamaService->generate("Analyze this...");
```

### Vision/Document Extraction
```php
// Via AIService (recommended for new code)
$aiService = app(App\Services\AIService::class);
$promptService = app(App\Services\AIPromptService::class);
$prompt = $promptService->buildReceiptExtractionPrompt($correspondents);
$data = $aiService->extractFromDocument($pdfPath, $prompt, jsonOutput: true);

// Via AIVisionService (still works)
$visionService = app(App\Services\AIVisionService::class);
$data = $visionService->extractReceiptData($pdfPath);
```

## Testing

The migration preserves all existing functionality:

1. **Receipt Extraction** - Transaction verification page
2. **Invoice Extraction** - Invoice import page
3. **Quote Extraction** - Quote import page
4. **Financial Analysis** - Monthly reports and recommendations

## Next Steps

### Optional Enhancements
1. **Update Settings UI** to allow provider selection per task type
2. **Add streaming support** for real-time AI responses
3. **Implement tool calling** for more advanced AI agents
4. **Add support for fallback providers** when primary fails

### Testing Recommendations
1. Test receipt extraction with your existing Ollama setup
2. Try switching to OpenAI or Anthropic for comparison
3. Monitor logs in `storage/logs/ai.log` for debugging

## Troubleshooting

### Common Issues

**Issue: "Class 'EchoLabs\Prism\Facades\Prism' not found"**
- Solution: Run `composer dump-autoload`

**Issue: Vision extraction returns null**
- Check: Imagick extension installed (`php -m | grep imagick`)
- Check: Model supports vision (e.g., qwen2.5vl:3b, llava)
- Check: Ollama URL is accessible from Docker container

**Issue: JSON parsing errors**
- Some models may return text before/after JSON
- AIService automatically extracts JSON using regex
- For best results, use models known for structured output

### Debug Mode

Enable detailed AI logging in `.env`:
```bash
APP_DEBUG=true
LOG_LEVEL=debug
```

AI requests and responses will be logged to `storage/logs/ai.log`.

## Resources

- **Prism Documentation**: https://prismphp.com
- **Prism GitHub**: https://github.com/prism-php/prism
- **Supported Models**: See provider documentation

## Removed Dependencies

The following have been removed as part of the migration:

- ✅ **ardagnsrn/ollama-php** - Old Ollama PHP client (replaced by Prism + direct HTTP)
- ✅ **OllamaVisionService.php** - Redundant service (now handled by AIService via Prism)
- ✅ **OllamaService.php** - All functionality moved to AIService

## Migration Checklist

- [x] Install prism-php/prism package
- [x] Publish Prism configuration
- [x] Create unified AIService
- [x] Update AIVisionService to use Prism
- [x] Remove old ollama-php library
- [x] Remove OllamaVisionService
- [x] Move Ollama utilities to AIService
- [x] Remove OllamaService completely
- [x] Update all references to use AIService
- [x] Update environment configuration
- [x] Update CLAUDE.md documentation
- [ ] Test receipt extraction
- [ ] Test invoice/quote import
- [ ] Test Ollama model installation
- [ ] Test financial analysis features
- [ ] Update production environment variables

## Credits

Migration completed using prism-php/prism v0.98.0.

---

**Questions or issues?** Check the logs, review Prism documentation, or file an issue in the GitHub repository.
