<!-- Transactions Table -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Datum
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Beschreibung
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Kategorie
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Netto / Brutto
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Validiert
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Aktionen
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50"
                    @contextmenu="showContextMenu($event, {{ $transaction->id }})">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d.m.Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <!-- Editable Description -->
                        <div class="text-gray-900"
                             x-show="!(editingField === 'description' && editingTransactionId === {{ $transaction->id }})"
                             @dblclick="startEdit('description', {{ $transaction->id }}, '{{ addslashes($transaction->description) }}')">
                            {{ $transaction->description }}
                        </div>
                        <input
                            type="text"
                            id="edit-description-{{ $transaction->id }}"
                            x-model="editValue"
                            x-show="editingField === 'description' && editingTransactionId === {{ $transaction->id }}"
                            @keydown.enter="saveEdit({{ $transaction->id }}, 'description')"
                            @blur="saveEdit({{ $transaction->id }}, 'description')"
                            class="w-full px-2 py-1 text-sm border border-indigo-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="display: none;">

                        <!-- Editable Note -->
                        @if($transaction->note)
                            <div class="text-xs text-gray-500 mt-1"
                                 x-show="!(editingField === 'note' && editingTransactionId === {{ $transaction->id }})"
                                 @dblclick="startEdit('note', {{ $transaction->id }}, '{{ addslashes($transaction->note) }}')">
                                <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                {{ $transaction->note }}
                            </div>
                        @endif
                        <input
                            type="text"
                            id="edit-note-{{ $transaction->id }}"
                            x-model="editValue"
                            x-show="editingField === 'note' && editingTransactionId === {{ $transaction->id }}"
                            @keydown.enter="saveEdit({{ $transaction->id }}, 'note')"
                            @blur="saveEdit({{ $transaction->id }}, 'note')"
                            class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 mt-1"
                            style="display: none;">

                        <!-- QuickLook indicator for Paperless document -->
                        @if($transaction->matched_paperless_document_id)
                            <div class="text-xs text-indigo-600 mt-1 inline-flex items-center cursor-help"
                                 @mouseenter="showQuickLook($event, {{ $transaction->matched_paperless_document_id }})"
                                 @mouseleave="hideQuickLook()">
                                <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                                Beleg #{{ $transaction->matched_paperless_document_id }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($transaction->category)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($transaction->category) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                        <!-- Editable Amount -->
                        <div class="font-medium"
                             x-show="!(editingField === 'amount' && editingTransactionId === {{ $transaction->id }})"
                             @dblclick="startEdit('amount', {{ $transaction->id }}, '{{ $transaction->amount }}')">
                            {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2, ',', '.') }} €
                        </div>
                        <input
                            type="number"
                            step="0.01"
                            id="edit-amount-{{ $transaction->id }}"
                            x-model="editValue"
                            x-show="editingField === 'amount' && editingTransactionId === {{ $transaction->id }}"
                            @keydown.enter="saveEdit({{ $transaction->id }}, 'amount')"
                            @blur="saveEdit({{ $transaction->id }}, 'amount')"
                            class="w-full px-2 py-1 text-sm border border-indigo-300 rounded text-right focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            style="display: none;">

                        @if($transaction->net_amount !== null && $transaction->vat_rate !== null)
                            <div class="text-xs text-gray-500 mt-0.5">
                                Netto: {{ number_format($transaction->net_amount, 2, ',', '.') }} €
                                ({{ number_format($transaction->vat_rate, 0) }}% MwSt.)
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <input
                            type="checkbox"
                            wire:change="markAsValidated({{ $transaction->id }}, $event.target.checked)"
                            {{ $transaction->is_validated ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center gap-2">
                            @if($transaction->amount > 0 && !$transaction->invoice_id)
                                <button
                                    wire:click="findMatches({{ $transaction->id }})"
                                    class="text-green-600 hover:text-green-900"
                                    title="Rechnung suchen"
                                >
                                    Rechnung
                                </button>
                            @endif
                            @if($transaction->invoice_id)
                                <span class="text-xs text-green-700">
                                    ✓ Verknüpft
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        Keine Transaktionen gefunden.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $transactions->links() }}
    </div>
</div>
