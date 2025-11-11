<?php

namespace App\Http\Controllers;

use App\Services\PaperlessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaperlessProxyController extends Controller
{
    /**
     * Proxy request to get Paperless document thumbnail
     */
    public function thumbnail(int $documentId)
    {
        $paperless = app(PaperlessService::class);
        $url = $paperless->getDocumentThumbnailUrl($documentId);

        if (! $url) {
            return response('Not found', 404);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token '.config('services.paperless.token'),
            ])->timeout(10)->get($url);

            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', $response->header('Content-Type'))
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            return response('Not found', 404);
        } catch (\Exception $e) {
            return response('Error fetching thumbnail', 500);
        }
    }

    /**
     * Proxy request to get Paperless document preview
     */
    public function preview(int $documentId)
    {
        $paperless = app(PaperlessService::class);
        $url = $paperless->getDocumentPreviewUrl($documentId);

        if (! $url) {
            return response('Not found', 404);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token '.config('services.paperless.token'),
            ])->timeout(10)->get($url);

            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', $response->header('Content-Type'))
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            return response('Not found', 404);
        } catch (\Exception $e) {
            return response('Error fetching preview', 500);
        }
    }
}
