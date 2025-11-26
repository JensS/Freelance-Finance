<?php

namespace App\Livewire\Components;

use App\Services\PaperlessService;
use Livewire\Component;

class PaperlessDocumentLinker extends Component
{
    // Search input
    public string $searchQuery = '';

    // Search results
    public array $searchResults = [];

    // Selected document
    public ?int $selectedDocumentId = null;

    public ?array $selectedDocument = null;

    // Loading states
    public bool $isSearching = false;

    public bool $showResults = false;

    // Debounce timer
    public int $searchDebounceMs = 300;

    // Context data (optional - can be passed from parent)
    public ?array $contextData = null;

    // Events emitted
    // - documentSelected: When a document is selected
    // - documentLinked: When link is confirmed

    public function mount(?array $contextData = null)
    {
        $this->contextData = $contextData;
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->showResults = false;

            return;
        }

        $this->isSearching = true;
        $this->showResults = true;
    }

    public function search()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->isSearching = false;

            return;
        }

        try {
            $paperlessService = app(PaperlessService::class);

            // Search for documents by query (name, date, content)
            $this->searchResults = $paperlessService->searchDocumentsAdvanced(
                query: $this->searchQuery,
                includeContent: true
            );

            // Limit to 10 results for performance
            $this->searchResults = array_slice($this->searchResults, 0, 10);

        } catch (\Exception $e) {
            \Log::error('Paperless search failed in linker', ['error' => $e->getMessage()]);
            $this->searchResults = [];
        } finally {
            $this->isSearching = false;
        }
    }

    public function selectDocument(int $documentId)
    {
        $this->selectedDocumentId = $documentId;

        // Find the selected document from search results
        $this->selectedDocument = collect($this->searchResults)->firstWhere('id', $documentId);

        if (! $this->selectedDocument) {
            // If not in search results, fetch from Paperless
            try {
                $paperlessService = app(PaperlessService::class);
                $this->selectedDocument = $paperlessService->getDocument($documentId);
            } catch (\Exception $e) {
                \Log::error('Failed to fetch document details', ['document_id' => $documentId]);
            }
        }

        // Emit event to parent
        $this->dispatch('documentSelected', documentId: $documentId, document: $this->selectedDocument);

        // Clear search
        $this->clearSearch();
    }

    public function clearSelection()
    {
        $this->selectedDocumentId = null;
        $this->selectedDocument = null;
        $this->dispatch('documentCleared');
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showResults = false;
    }

    public function confirmLink()
    {
        if (! $this->selectedDocumentId) {
            return;
        }

        $this->dispatch('documentLinked', documentId: $this->selectedDocumentId, document: $this->selectedDocument);
    }

    public function render()
    {
        return view('livewire.components.paperless-document-linker');
    }
}
