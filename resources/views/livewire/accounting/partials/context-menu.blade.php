<!-- Context Menu -->
<div x-show="contextMenu.show"
     x-transition
     :style="{ position: 'fixed', top: contextMenu.y + 'px', left: contextMenu.x + 'px', zIndex: 9999 }"
     class="bg-white rounded-lg shadow-lg border border-gray-200 py-1 min-w-48"
     @click.stop>
    <button
        @click="startEdit('description', contextMenu.transactionId, ''); hideContextMenu()"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Beschreibung bearbeiten
    </button>
    <button
        @click="startEdit('amount', contextMenu.transactionId, ''); hideContextMenu()"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Betrag bearbeiten
    </button>
    <button
        @click="startEdit('note', contextMenu.transactionId, ''); hideContextMenu()"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Notiz bearbeiten
    </button>
    <hr class="my-1">
    <button
        @click="$wire.markAsValidated(contextMenu.transactionId, true); hideContextMenu()"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Als validiert markieren
    </button>
    <button
        @click="$wire.findMatches(contextMenu.transactionId); hideContextMenu()"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        Rechnung suchen
    </button>
    <hr class="my-1">
    <button
        @click="if(confirm('Transaktion wirklich löschen?')) { $wire.deleteTransaction(contextMenu.transactionId); hideContextMenu(); }"
        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Löschen
    </button>
</div>
