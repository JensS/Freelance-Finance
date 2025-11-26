<!-- QuickLook Preview -->
<div x-show="quickLook.show"
     x-transition
     :style="{ position: 'fixed', top: quickLook.y + 'px', left: quickLook.x + 'px', zIndex: 9999 }"
     class="bg-white rounded-lg shadow-2xl border-2 border-indigo-200 overflow-hidden"
     style="width: 400px; max-height: 500px;"
     @click.stop>
    <div class="bg-indigo-50 px-4 py-2 border-b border-indigo-200 flex items-center justify-between">
        <div class="text-sm font-medium text-indigo-900">QuickLook Vorschau</div>
        <button @click="hideQuickLook()" class="text-indigo-600 hover:text-indigo-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="p-2 bg-gray-50" style="max-height: 450px; overflow-y: auto;">
        <template x-if="quickLook.documentId">
            <img
                :src="`/paperless/documents/${quickLook.documentId}/preview`"
                @load="quickLook.loading = false"
                @@error="quickLook.loading = false"
                alt="Dokumentvorschau"
                class="w-full h-auto"
                style="max-height: 440px; object-fit: contain;">
        </template>
        <div x-show="quickLook.loading" class="flex items-center justify-center py-20">
            <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</div>
