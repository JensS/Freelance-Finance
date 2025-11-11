<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaperlessService
{
    private string $baseUrl;

    private string $apiToken;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.paperless.url'), '/');
        $this->apiToken = config('services.paperless.token');
    }

    /**
     * Get HTTP client with authentication headers
     */
    private function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Token '.$this->apiToken,
            'Accept' => 'application/json',
        ])->timeout(30);
    }

    /**
     * Upload a document to Paperless
     *
     * @param  string  $filePath  Absolute path to the PDF file
     * @param  array  $metadata  Additional metadata (title, correspondent, tags, etc.)
     * @return array|null Document data or null on failure
     */
    public function uploadDocument(string $filePath, array $metadata = []): ?array
    {
        if (! file_exists($filePath)) {
            Log::error('Paperless upload failed: File not found', ['path' => $filePath]);

            return null;
        }

        // Check if Paperless is properly configured
        if (empty($this->apiToken)) {
            Log::error('Paperless upload failed: API token not configured');

            return null;
        }

        if (empty($this->baseUrl)) {
            Log::error('Paperless upload failed: Base URL not configured');

            return null;
        }

        // Get storage path from settings if not provided in metadata
        if (! isset($metadata['storage_path'])) {
            $storagePath = \App\Models\Setting::get('paperless_storage_path');
            if ($storagePath) {
                $metadata['storage_path'] = $storagePath;
            }
        }

        try {
            $response = $this->client()->attach(
                'document',
                file_get_contents($filePath),
                basename($filePath)
            )->post($this->baseUrl.'/api/documents/post_document/', array_filter([
                'title' => $metadata['title'] ?? null,
                'correspondent' => $metadata['correspondent'] ?? null,
                'document_type' => $metadata['document_type'] ?? null,
                'tags' => $metadata['tags'] ?? null,
                'created' => $metadata['created'] ?? null,
                'storage_path' => $metadata['storage_path'] ?? null,
            ]));

            if ($response->successful()) {
                $responseData = $response->json();

                // Paperless API can return different response formats
                // Sometimes it returns just a document ID as a string
                // Sometimes it returns a JSON object with document data
                if (is_string($responseData)) {
                    // API returned just a document ID
                    Log::info('Document uploaded to Paperless', ['document_id' => $responseData]);

                    return ['id' => $responseData];
                } elseif (is_array($responseData)) {
                    // API returned a JSON object
                    Log::info('Document uploaded to Paperless', ['document_id' => $responseData['id'] ?? null]);

                    return $responseData;
                } else {
                    Log::error('Paperless upload returned unexpected response format', ['response' => $responseData]);

                    return null;
                }
            }

            // Handle specific error cases
            if ($response->status() === 401) {
                Log::error('Paperless authentication failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $this->baseUrl,
                    'has_token' => ! empty($this->apiToken),
                ]);
            } else {
                Log::error('Paperless upload failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Paperless upload exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Search for documents in Paperless
     *
     * @param  string  $query  Search query
     * @param  array  $filters  Additional filters (correspondent, tags, etc.)
     * @return array Search results
     */
    public function searchDocuments(string $query, array $filters = []): array
    {
        // Check if Paperless is properly configured
        if (empty($this->apiToken) || empty($this->baseUrl)) {
            Log::warning('Paperless search skipped: API not configured');

            return [];
        }

        // Get storage path from settings if not provided in filters
        if (! isset($filters['storage_path_id'])) {
            $storagePathId = \App\Models\Setting::get('paperless_storage_path');
            if ($storagePathId) {
                $filters['storage_path_id'] = $storagePathId;
            }
        }

        try {
            $params = array_filter([
                'query' => $query,
                'correspondent__id' => $filters['correspondent_id'] ?? null,
                'document_type__id' => $filters['document_type_id'] ?? null,
                'tags__id__all' => $filters['tags'] ?? null,
                'storage_path__id' => $filters['storage_path_id'] ?? null,
            ]);

            $response = $this->client()->get($this->baseUrl.'/api/documents/', $params);

            if ($response->successful()) {
                return $response->json('results', []);
            }

            Log::error('Paperless search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Paperless search exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get a specific document by ID
     *
     * @param  int  $documentId  Document ID in Paperless
     * @return array|null Document data or null on failure
     */
    public function getDocument(int $documentId): ?array
    {
        // Check if Paperless is properly configured
        if (empty($this->apiToken) || empty($this->baseUrl)) {
            Log::warning('Paperless get document skipped: API not configured');

            return null;
        }

        try {
            $response = $this->client()->get($this->baseUrl.'/api/documents/'.$documentId.'/');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paperless get document failed', [
                'document_id' => $documentId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Paperless get document exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Download document PDF from Paperless
     *
     * @param  int  $documentId  Document ID in Paperless
     * @return string|null PDF content or null on failure
     */
    public function downloadDocument(int $documentId): ?string
    {
        try {
            $response = $this->client()->get($this->baseUrl.'/api/documents/'.$documentId.'/download/');

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('Paperless download failed', [
                'document_id' => $documentId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Paperless download exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Get document thumbnail URL from Paperless
     *
     * @param  int  $documentId  Document ID in Paperless
     * @return string|null Thumbnail URL or null if not available
     */
    public function getDocumentThumbnailUrl(int $documentId): ?string
    {
        if (empty($this->baseUrl)) {
            return null;
        }

        return $this->baseUrl.'/api/documents/'.$documentId.'/thumb/';
    }

    /**
     * Get document preview URL from Paperless
     *
     * @param  int  $documentId  Document ID in Paperless
     * @return string|null Preview URL or null if not available
     */
    public function getDocumentPreviewUrl(int $documentId): ?string
    {
        if (empty($this->baseUrl)) {
            return null;
        }

        return $this->baseUrl.'/api/documents/'.$documentId.'/preview/';
    }

    /**
     * Update document metadata in Paperless
     *
     * @param  int  $documentId  Document ID in Paperless
     * @param  array  $metadata  Metadata to update
     * @return bool Success status
     */
    public function updateDocument(int $documentId, array $metadata): bool
    {
        try {
            $response = $this->client()->patch(
                $this->baseUrl.'/api/documents/'.$documentId.'/',
                array_filter($metadata)
            );

            if ($response->successful()) {
                Log::info('Document updated in Paperless', ['document_id' => $documentId]);

                return true;
            }

            Log::error('Paperless update failed', [
                'document_id' => $documentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Paperless update exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get all correspondents from Paperless
     *
     * @return array List of correspondents
     */
    public function getCorrespondents(): array
    {
        try {
            $response = $this->client()->get($this->baseUrl.'/api/correspondents/');

            if ($response->successful()) {
                return $response->json('results', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Paperless get correspondents exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Create a correspondent in Paperless
     *
     * @param  string  $name  Correspondent name
     * @return array|null Created correspondent or null on failure
     */
    public function createCorrespondent(string $name): ?array
    {
        try {
            $response = $this->client()->post($this->baseUrl.'/api/correspondents/', [
                'name' => $name,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paperless create correspondent failed', [
                'name' => $name,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Paperless create correspondent exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Get all document types from Paperless
     *
     * @return array List of document types
     */
    public function getDocumentTypes(): array
    {
        try {
            $response = $this->client()->get($this->baseUrl.'/api/document_types/');

            if ($response->successful()) {
                return $response->json('results', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Paperless get document types exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get all tags from Paperless
     *
     * @return array List of tags
     */
    public function getTags(): array
    {
        try {
            $response = $this->client()->get($this->baseUrl.'/api/tags/');

            if ($response->successful()) {
                return $response->json('results', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Paperless get tags exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Test connection to Paperless
     *
     * @return bool Connection status
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->client()->get($this->baseUrl.'/api/documents/?page_size=1');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Paperless connection test failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get expense documents from Paperless (receipts not yet imported)
     *
     * @param  array  $options  Options for filtering (tags, date_range, etc.)
     * @return array List of expense documents
     */
    public function getExpenseDocuments(array $options = []): array
    {
        if (empty($this->apiToken) || empty($this->baseUrl)) {
            Log::warning('Paperless get expenses skipped: API not configured');

            return [];
        }

        try {
            // Get tags for expense receipts
            $tags = $this->getTags();
            $expenseTagIds = [];

            foreach ($tags as $tag) {
                // Look for expense-related tags: Eingangsrechnung, Barbeleg, Bewirtung
                if (in_array($tag['name'], ['Eingangsrechnung', 'Barbeleg', 'Bewirtung'])) {
                    $expenseTagIds[] = $tag['id'];
                }
            }

            if (empty($expenseTagIds)) {
                Log::warning('No expense tags found in Paperless');

                return [];
            }

            // Build query parameters
            $params = [
                'tags__id__in' => implode(',', $expenseTagIds),
                'page_size' => $options['page_size'] ?? 50,
                'ordering' => '-created',
            ];

            // Add storage path filter from settings
            $storagePathId = \App\Models\Setting::get('paperless_storage_path');
            if ($storagePathId) {
                $params['storage_path__id'] = $storagePathId;
            }

            // Add date filter if provided
            if (isset($options['date_after'])) {
                $params['created__date__gte'] = $options['date_after'];
            }

            if (isset($options['date_before'])) {
                $params['created__date__lte'] = $options['date_before'];
            }

            $response = $this->client()->get($this->baseUrl.'/api/documents/', $params);

            if ($response->successful()) {
                return $response->json('results', []);
            }

            Log::error('Paperless get expense documents failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Paperless get expense documents exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get tag ID by name
     *
     * @param  string  $tagName  Tag name to search for
     * @return int|null Tag ID or null if not found
     */
    public function getTagIdByName(string $tagName): ?int
    {
        $tags = $this->getTags();

        foreach ($tags as $tag) {
            if ($tag['name'] === $tagName) {
                return $tag['id'];
            }
        }

        return null;
    }

    /**
     * Get all storage paths from Paperless
     *
     * @return array List of storage paths
     */
    public function getStoragePaths(): array
    {
        try {
            $response = $this->client()->get($this->baseUrl.'/api/storage_paths/');

            if ($response->successful()) {
                return $response->json('results', []);
            }

            Log::error('Paperless get storage paths failed', [
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Paperless get storage paths exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get storage path ID by name
     *
     * @param  string  $pathName  Storage path name to search for
     * @return int|null Storage path ID or null if not found
     */
    public function getStoragePathIdByName(string $pathName): ?int
    {
        $paths = $this->getStoragePaths();

        foreach ($paths as $path) {
            if ($path['name'] === $pathName) {
                return $path['id'];
            }
        }

        return null;
    }
}
