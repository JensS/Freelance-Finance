<?php

namespace App\Livewire\Settings;

use App\Models\KnowledgeBaseEntry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Wissensbasis - Einstellungen')]
class KnowledgeBaseSettings extends Component
{
    public string $activeSection = 'receipt_sources'; // receipt_sources or note_templates

    public $entries = [];

    public $editingEntry = null;

    public $showModal = false;

    // Form fields
    public string $entryType = '';

    public string $title = '';

    public string $description = '';

    public string $category = '';

    // Receipt source specific fields
    public string $url = '';

    public string $navigation = '';

    public string $invoiceFormat = '';

    public string $emailSender = '';

    public string $emailSubjectPattern = '';

    public string $bankTransactionPattern = '';

    // Note template specific fields
    public string $exampleNote = '';

    public string $usageContext = '';

    public string $success = '';

    public function mount()
    {
        $this->loadEntries();
    }

    public function loadEntries()
    {
        if ($this->activeSection === 'receipt_sources') {
            $this->entries = KnowledgeBaseEntry::receiptSources()->get()->toArray();
        } else {
            $this->entries = KnowledgeBaseEntry::noteTemplates()->get()->toArray();
        }
    }

    public function switchSection($section)
    {
        $this->activeSection = $section;
        $this->loadEntries();
    }

    public function openModal($type)
    {
        $this->resetForm();
        $this->entryType = $type;
        $this->showModal = true;
    }

    public function editEntry($id)
    {
        $entry = KnowledgeBaseEntry::findOrFail($id);
        $this->editingEntry = $id;
        $this->entryType = $entry->type;
        $this->title = $entry->title;
        $this->description = $entry->description ?? '';
        $this->category = $entry->category ?? '';

        $data = $entry->data;

        if ($entry->type === KnowledgeBaseEntry::TYPE_RECEIPT_SOURCE) {
            $this->url = $data['url'] ?? '';
            $this->navigation = $data['navigation'] ?? '';
            $this->invoiceFormat = $data['invoice_format'] ?? '';
            $this->emailSender = $data['email_sender'] ?? '';
            $this->emailSubjectPattern = $data['email_subject_pattern'] ?? '';
            $this->bankTransactionPattern = $data['bank_transaction_pattern'] ?? '';
        } else {
            $this->exampleNote = $data['example_note'] ?? '';
            $this->usageContext = $data['usage_context'] ?? '';
        }

        $this->showModal = true;
    }

    public function saveEntry()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
        ]);

        $data = [];

        if ($this->entryType === KnowledgeBaseEntry::TYPE_RECEIPT_SOURCE) {
            $data = [
                'url' => $this->url,
                'navigation' => $this->navigation,
                'invoice_format' => $this->invoiceFormat,
                'email_sender' => $this->emailSender,
                'email_subject_pattern' => $this->emailSubjectPattern,
                'bank_transaction_pattern' => $this->bankTransactionPattern,
            ];
        } else {
            $data = [
                'example_note' => $this->exampleNote,
                'usage_context' => $this->usageContext,
            ];
        }

        if ($this->editingEntry) {
            $entry = KnowledgeBaseEntry::findOrFail($this->editingEntry);
            $entry->update([
                'title' => $this->title,
                'description' => $this->description,
                'category' => $this->category,
                'data' => $data,
            ]);
            $this->success = 'Eintrag erfolgreich aktualisiert!';
        } else {
            KnowledgeBaseEntry::create([
                'type' => $this->entryType,
                'title' => $this->title,
                'description' => $this->description,
                'category' => $this->category,
                'data' => $data,
                'is_active' => true,
            ]);
            $this->success = 'Eintrag erfolgreich erstellt!';
        }

        $this->showModal = false;
        $this->loadEntries();
        $this->resetForm();
    }

    public function deleteEntry($id)
    {
        KnowledgeBaseEntry::findOrFail($id)->delete();
        $this->success = 'Eintrag erfolgreich gelöscht!';
        $this->loadEntries();
    }

    public function toggleActive($id)
    {
        $entry = KnowledgeBaseEntry::findOrFail($id);
        $entry->update(['is_active' => ! $entry->is_active]);
        $this->loadEntries();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingEntry = null;
        $this->title = '';
        $this->description = '';
        $this->category = '';
        $this->url = '';
        $this->navigation = '';
        $this->invoiceFormat = '';
        $this->emailSender = '';
        $this->emailSubjectPattern = '';
        $this->bankTransactionPattern = '';
        $this->exampleNote = '';
        $this->usageContext = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.settings.knowledge-base-settings');
    }
}
