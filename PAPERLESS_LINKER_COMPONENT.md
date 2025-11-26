# Paperless Document Linker Component

A reusable Livewire component for linking Paperless documents with autocomplete search functionality.

## Features

- **Autocomplete Search**: Real-time search with debouncing
- **Multi-field Search**: Search by document title, date, or content
- **Context Display**: Optional transaction/context information display
- **Event-driven**: Emits events for parent components to listen
- **Loading States**: Built-in visual feedback for async operations
- **Responsive UI**: Dropdown results with document previews
- **Storage Path Filtering**: Automatically filters by configured storage path

## Installation

The component is already created at:
- **Component**: `app/Livewire/Components/PaperlessDocumentLinker.php`
- **View**: `resources/views/livewire/components/paperless-document-linker.blade.php`

## Basic Usage

### 1. Include in Your Blade View

```blade
@livewire('components.paperless-document-linker')
```

### 2. With Context Data

Pass transaction or context data to help users identify the correct document:

```blade
@livewire('components.paperless-document-linker', [
    'contextData' => [
        'correspondent' => 'Amazon',
        'amount' => -49.99,
        'date' => '2025-11-14',
    ]
])
```

### 3. Listen to Events

The component emits the following events:

#### `documentSelected`
Emitted when a user selects a document from search results.

```blade
<div
    x-data="{ selectedDocId: null }"
    @document-selected.window="selectedDocId = $event.detail.documentId"
>
    @livewire('components.paperless-document-linker')
</div>
```

Or in a Livewire component:

```php
// In your parent Livewire component
protected $listeners = ['documentSelected' => 'handleDocumentSelected'];

public function handleDocumentSelected($documentId, $document)
{
    $this->selectedDocumentId = $documentId;
    $this->selectedDocument = $document;
}
```

#### `documentCleared`
Emitted when the user clears their selection.

```php
protected $listeners = ['documentCleared' => 'handleDocumentCleared'];

public function handleDocumentCleared()
{
    $this->selectedDocumentId = null;
    $this->selectedDocument = null;
}
```

## Full Example: Modal Integration

Here's a complete example of integrating the component into a modal:

### Livewire Component (PHP)

```php
<?php

namespace App\Livewire\Transactions;

use App\Models\BankTransaction;
use Livewire\Component;

class LinkingModal extends Component
{
    public $showModal = false;
    public $transaction;
    public $selectedDocumentId = null;
    public $linkingInProgress = false;

    protected $listeners = [
        'documentSelected' => 'handleDocumentSelected',
        'documentCleared' => 'handleDocumentCleared',
    ];

    public function openModal($transactionId)
    {
        $this->transaction = BankTransaction::findOrFail($transactionId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDocumentId = null;
    }

    public function handleDocumentSelected($documentId, $document)
    {
        $this->selectedDocumentId = $documentId;
    }

    public function handleDocumentCleared()
    {
        $this->selectedDocumentId = null;
    }

    public function linkDocument()
    {
        if (!$this->selectedDocumentId) {
            return;
        }

        $this->linkingInProgress = true;

        try {
            $this->transaction->update([
                'paperless_document_id' => $this->selectedDocumentId,
            ]);

            session()->flash('success', 'Dokument erfolgreich verknüpft!');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Fehler beim Verknüpfen: ' . $e->getMessage());
        } finally {
            $this->linkingInProgress = false;
        }
    }

    public function render()
    {
        return view('livewire.transactions.linking-modal');
    }
}
```

### Blade View

```blade
@if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>

            <div class="relative bg-white rounded-lg p-6 max-w-2xl w-full">
                <h3 class="text-lg font-medium mb-4">Dokument verknüpfen</h3>

                {{-- Use the component --}}
                @livewire('components.paperless-document-linker', [
                    'contextData' => [
                        'correspondent' => $transaction->correspondent,
                        'amount' => $transaction->amount,
                        'date' => $transaction->transaction_date,
                    ]
                ], key('linker-'.$transaction->id))

                {{-- Action buttons --}}
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 border rounded-md"
                    >
                        Abbrechen
                    </button>
                    <button
                        wire:click="linkDocument"
                        :disabled="!$wire.selectedDocumentId"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50"
                    >
                        Verknüpfen
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
```

## Component Properties

### Public Properties

- `string $searchQuery` - Current search input
- `array $searchResults` - Search results from Paperless
- `int|null $selectedDocumentId` - ID of selected document
- `array|null $selectedDocument` - Full document data of selection
- `bool $isSearching` - Loading state for search
- `bool $showResults` - Controls dropdown visibility
- `int $searchDebounceMs` - Debounce delay (default: 300ms)
- `array|null $contextData` - Optional transaction context

### Context Data Structure

```php
[
    'correspondent' => 'Vendor Name',  // Optional
    'amount' => -123.45,               // Optional
    'date' => '2025-11-14',           // Optional
]
```

## Component Methods

### Public Methods

- `search()` - Trigger a search manually
- `selectDocument(int $documentId)` - Select a document
- `clearSelection()` - Clear the current selection
- `clearSearch()` - Clear search query and results
- `confirmLink()` - Emit `documentLinked` event (optional)

## Search Capabilities

The component searches across:
1. **Document Title** - Full-text search in title field
2. **Document Date** - Matches date formats (YYYY-MM-DD, DD.MM.YYYY)
3. **Document Content** - OCR text content (optional)
4. **Correspondent Name** - Associated correspondent
5. **Archive Serial Number** - Document ASN

### Search Examples

- `"Amazon"` - Finds all Amazon documents
- `"14.11.2025"` - Finds documents from this date
- `"Rechnung 123"` - Finds documents with "Rechnung 123" in title or content
- `"EUR 49.99"` - Finds documents mentioning this amount

## Styling

The component uses Tailwind CSS classes and is fully responsive. Key styles:

- **Search Input**: Full-width with search icon and spinner
- **Dropdown**: Absolute positioned, max-height 384px (96 * 4px), scrollable
- **Results**: Hover effects, border separators
- **Selected**: Indigo-themed highlight box
- **Loading**: Animated spinner, gray text

## Performance

- **Debounced Search**: 300ms default (configurable)
- **Result Limit**: 10 documents max
- **N+1 Prevention**: Correspondents fetched once
- **Content Exclusion**: Optional content field exclusion for speed

## Advanced Customization

### Custom Debounce Time

```blade
@livewire('components.paperless-document-linker', [
    'searchDebounceMs' => 500  // 500ms debounce
])
```

### With Additional Filters

Modify the component to accept filters:

```php
// In PaperlessDocumentLinker.php
public array $filters = [];

public function search()
{
    $this->searchResults = $paperlessService->searchDocumentsAdvanced(
        query: $this->searchQuery,
        includeContent: true,
        filters: $this->filters  // Pass custom filters
    );
}
```

## Troubleshooting

### Search Returns No Results

1. Check Paperless connection in Settings
2. Verify storage path is configured
3. Check browser console for JavaScript errors
4. Review Laravel logs for API errors

### Events Not Firing

1. Ensure parent component has `$listeners` array
2. Check that method names match exactly
3. Verify Livewire is properly loaded on page

### Slow Search Performance

1. Reduce `page_size` in `searchDocumentsAdvanced()`
2. Disable content inclusion: `includeContent: false`
3. Add more specific filters (tags, correspondent)

## API Reference

### PaperlessService::searchDocumentsAdvanced()

```php
/**
 * Advanced search for documents across multiple fields
 *
 * @param  string  $query  Search query (can be title, date, content)
 * @param  bool  $includeContent  Include content field in results
 * @param  array  $filters  Additional filters
 * @return array Search results with correspondent names
 */
public function searchDocumentsAdvanced(
    string $query,
    bool $includeContent = false,
    array $filters = []
): array
```

**Filters:**
- `page_size` (int) - Results per page (default: 50)
- `storage_path_id` (int) - Filter by storage path
- `tags` (int|string) - Filter by tag ID(s)
- `correspondent_id` (int) - Filter by correspondent
- `document_type_id` (int) - Filter by document type

## Examples in Codebase

See working examples in:
- `resources/views/livewire/accounting/partials/linking-modal-with-search.blade.php`
- Use in MonthlyView component for transaction linking

## Future Enhancements

Potential improvements:
- PDF preview in dropdown
- Recent searches history
- Favorite/starred documents
- Bulk linking support
- Keyboard navigation (arrow keys)
- Advanced filter UI (date range, tags)
