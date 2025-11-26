<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseEntry extends Model
{
    protected $fillable = [
        'type',
        'title',
        'description',
        'data',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
    ];

    // Type constants
    public const TYPE_RECEIPT_SOURCE = 'receipt_source';

    public const TYPE_NOTE_TEMPLATE = 'note_template';

    /**
     * Scope to get only receipt sources
     */
    public function scopeReceiptSources($query)
    {
        return $query->where('type', self::TYPE_RECEIPT_SOURCE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    /**
     * Scope to get only note templates
     */
    public function scopeNoteTemplates($query)
    {
        return $query->where('type', self::TYPE_NOTE_TEMPLATE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    /**
     * Get all active entries grouped by type
     */
    public static function getAllGrouped(): array
    {
        return [
            'receipt_sources' => self::receiptSources()->get(),
            'note_templates' => self::noteTemplates()->get(),
        ];
    }

    /**
     * Format for AI prompt context
     */
    public function formatForAI(): string
    {
        if ($this->type === self::TYPE_RECEIPT_SOURCE) {
            return $this->formatReceiptSourceForAI();
        }

        if ($this->type === self::TYPE_NOTE_TEMPLATE) {
            return $this->formatNoteTemplateForAI();
        }

        return '';
    }

    /**
     * Format receipt source for AI context
     */
    private function formatReceiptSourceForAI(): string
    {
        $data = $this->data;
        $parts = [
            "**{$this->title}**",
        ];

        if (! empty($data['bank_transaction_pattern'])) {
            $parts[] = "- Bank statement pattern: {$data['bank_transaction_pattern']}";
        }

        if (! empty($data['email_sender'])) {
            $parts[] = "- Email sender: {$data['email_sender']}";
        }

        if (! empty($data['email_subject_pattern'])) {
            $parts[] = "- Email subject: {$data['email_subject_pattern']}";
        }

        if (! empty($data['url'])) {
            $parts[] = "- Invoice location: {$data['url']}";
        }

        if (! empty($data['navigation'])) {
            $parts[] = "- How to access: {$data['navigation']}";
        }

        if (! empty($this->description)) {
            $parts[] = "- Notes: {$this->description}";
        }

        return implode("\n", $parts);
    }

    /**
     * Format note template for AI context
     */
    private function formatNoteTemplateForAI(): string
    {
        $data = $this->data;
        $parts = [
            "**{$this->title}**",
        ];

        if (! empty($data['example_note'])) {
            $parts[] = "Example: \"{$data['example_note']}\"";
        }

        if (! empty($data['usage_context'])) {
            $parts[] = "Used for: {$data['usage_context']}";
        }

        if (! empty($this->description)) {
            $parts[] = "Notes: {$this->description}";
        }

        return implode("\n", $parts);
    }

    /**
     * Get all receipt sources formatted for AI
     */
    public static function getReceiptSourcesForAI(): string
    {
        $sources = self::receiptSources()->get();

        if ($sources->isEmpty()) {
            return '';
        }

        $formatted = $sources->map(fn ($source) => $source->formatForAI())->join("\n\n");

        return "**Known Receipt Sources:**\n\n".$formatted;
    }

    /**
     * Get all note templates formatted for AI
     */
    public static function getNoteTemplatesForAI(): string
    {
        $templates = self::noteTemplates()->get();

        if ($templates->isEmpty()) {
            return '';
        }

        $formatted = $templates->map(fn ($template) => $template->formatForAI())->join("\n\n");

        return "**Example Accounting Notes (for context):**\n\n".$formatted;
    }
}
