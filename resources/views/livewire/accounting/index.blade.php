<div x-data="{
    contextMenu: {
        show: false,
        x: 0,
        y: 0,
        transactionId: null
    },
    quickLook: {
        show: false,
        x: 0,
        y: 0,
        documentId: null,
        loading: false
    },
    editingField: null,
    editingTransactionId: null,
    editValue: '',
    showContextMenu(event, transactionId) {
        event.preventDefault();
        this.contextMenu.show = true;
        this.contextMenu.x = event.pageX;
        this.contextMenu.y = event.pageY;
        this.contextMenu.transactionId = transactionId;
    },
    hideContextMenu() {
        this.contextMenu.show = false;
        this.contextMenu.transactionId = null;
    },
    showQuickLook(event, documentId) {
        this.quickLook.show = true;
        this.quickLook.documentId = documentId;
        this.quickLook.loading = true;

        const rect = event.target.getBoundingClientRect();
        this.quickLook.x = rect.right + 10;
        this.quickLook.y = rect.top;
    },
    hideQuickLook() {
        this.quickLook.show = false;
        this.quickLook.documentId = null;
    },
    startEdit(field, transactionId, currentValue) {
        this.editingField = field;
        this.editingTransactionId = transactionId;
        this.editValue = currentValue || '';
        this.$nextTick(() => {
            const input = document.querySelector(`#edit-${field}-${transactionId}`);
            if (input) {
                input.focus();
                input.select();
            }
        });
    },
    cancelEdit() {
        this.editingField = null;
        this.editingTransactionId = null;
        this.editValue = '';
    },
    saveEdit(transactionId, field) {
        const data = {};
        data[field] = this.editValue;
        $wire.updateTransaction(transactionId, data);
        this.cancelEdit();
    }
}"
@click.away="hideContextMenu()"
@keydown.escape="cancelEdit(); hideContextMenu()">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Buchhaltung</h1>
            <p class="mt-1 text-sm text-gray-500">Verwalten Sie Ihre Banktransaktionen</p>
        </div>
        <div class="flex gap-3">
            <button
                wire:click="togglePaperlessModal"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Aus Paperless importieren
            </button>
            <button
                wire:click="toggleUploadModal"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Kontoauszug hochladen
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session()->has('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-6 rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.accounting.partials.statistics-cards')

    @include('livewire.accounting.partials.filters')

    @include('livewire.accounting.partials.transactions-table')

    @include('livewire.accounting.partials.upload-modal')

    @include('livewire.accounting.partials.paperless-import-modal')

    @include('livewire.accounting.partials.context-menu')

    @include('livewire.accounting.partials.quicklook-preview')
</div>
