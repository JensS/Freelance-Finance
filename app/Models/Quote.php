<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'quote_number',
        'customer_id',
        'type',
        'project_name',
        'issue_date',
        'valid_until',
        'items',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'notes',
        'paperless_document_id',
        'converted_to_invoice_id',
    ];

    protected $casts = [
        'items' => 'array',
        'issue_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the quote
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the invoice this quote was converted to
     */
    public function convertedToInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    /**
     * Generate the next quote number
     */
    public static function generateQuoteNumber(): string
    {
        $lastQuote = static::orderBy('quote_number', 'desc')->first();

        if (! $lastQuote) {
            return 'Q-1';
        }

        // Extract number from last quote number
        $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastQuote->quote_number);

        return 'Q-'.($lastNumber + 1);
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;

        foreach ($this->items as $item) {
            $subtotal += $item['total'];
        }

        $this->subtotal = $subtotal;
        $this->vat_amount = $subtotal * ($this->vat_rate / 100);
        $this->total = $subtotal + $this->vat_amount;
    }

    /**
     * Check if quote has expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until < now();
    }

    /**
     * Check if quote has been converted to invoice
     */
    public function isConverted(): bool
    {
        return $this->converted_to_invoice_id !== null;
    }

    /**
     * Generate PDF for this quote
     */
    public function generatePdf()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'John Doe'),
            'company_address' => Setting::get('company_address', [
                'street' => 'Street Adress',
                'city' => 'City',
                'zip' => 'ZIP',
            ]),
            'bank_details' => Setting::get('bank_details', [
                'iban' => 'DEXX XXXX XXX XXXX XXX',
                'bic' => 'XXXXXXX',
            ]),
            'tax_number' => Setting::get('tax_number', 'XX/XXX/XXX'),
            'eu_vat_id' => Setting::get('eu_vat_id', 'DEXXXXXXX'),
        ];

        $pdf = \PDF::loadView('pdfs.quote', [
            'quote' => $this,
            'settings' => $settings,
        ]);

        return $pdf;
    }

    /**
     * Get filename for PDF
     */
    public function getPdfFilename(): string
    {
        $date = $this->issue_date->format('Y-m-d');
        $customer = str_replace(' ', '-', $this->customer->name);

        return "{$date}-Angebot-{$customer}-{$this->quote_number}.pdf";
    }

    /**
     * Upload quote PDF to Paperless
     *
     * @return bool Success status
     */
    public function uploadToPaperless(): bool
    {
        $paperless = app(\App\Services\PaperlessService::class);

        // Generate PDF
        $pdf = $this->generatePdf();
        $filename = $this->getPdfFilename();
        $tempPath = storage_path('app/temp/'.$filename);

        // Ensure temp directory exists
        if (! file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Save PDF to temp location
        file_put_contents($tempPath, $pdf->output());

        try {
            // Upload to Paperless with metadata
            $result = $paperless->uploadDocument($tempPath, [
                'title' => "Angebot {$this->quote_number} - {$this->customer->name}",
                'created' => $this->issue_date->format('Y-m-d'),
            ]);

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            if ($result && isset($result['id'])) {
                // Update quote with Paperless document ID
                $this->update(['paperless_document_id' => $result['id']]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            // Clean up temp file on error
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            \Log::error('Failed to upload quote to Paperless', [
                'quote_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Convert this quote to an invoice
     *
     * @return Invoice|null The created invoice or null on failure
     */
    public function convertToInvoice(): ?Invoice
    {
        // Check if already converted
        if ($this->isConverted()) {
            return $this->convertedToInvoice;
        }

        try {
            return \DB::transaction(function () {
                // Create invoice from quote data
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'customer_id' => $this->customer_id,
                    'type' => $this->type,
                    'project_name' => $this->project_name,
                    'issue_date' => now(),
                    'due_date' => now()->addDays(30), // Default 30 days payment term
                    'items' => $this->items,
                    'subtotal' => $this->subtotal,
                    'vat_rate' => $this->vat_rate,
                    'vat_amount' => $this->vat_amount,
                    'total' => $this->total,
                    'notes' => $this->notes,
                ]);

                // Mark this quote as converted
                $this->update(['converted_to_invoice_id' => $invoice->id]);

                return $invoice;
            });
        } catch (\Exception $e) {
            \Log::error('Failed to convert quote to invoice', [
                'quote_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
