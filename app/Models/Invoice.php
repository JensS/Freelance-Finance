<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'type',
        'project_name',
        'service_period_start',
        'service_period_end',
        'service_location',
        'issue_date',
        'due_date',
        'items',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'notes',
        'paperless_document_id',
    ];

    protected $casts = [
        'items' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
        'service_period_start' => 'date',
        'service_period_end' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the invoice
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Generate the next invoice number (German law compliant - no gaps allowed)
     *
     * This method uses database locking to ensure sequential numbers without gaps.
     * According to German law (§14 UStG), invoice numbers must be:
     * - Unique
     * - Sequential
     * - Without gaps
     */
    public static function generateInvoiceNumber(): string
    {
        return \DB::transaction(function () {
            // Lock the table to prevent race conditions
            $lastInvoice = static::lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if (! $lastInvoice) {
                return '1';
            }

            // Extract number from last invoice number
            // This handles both pure numbers and prefixed formats like "2025-001"
            $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastInvoice->invoice_number);

            return (string) ($lastNumber + 1);
        });
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
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && ! $this->paperless_document_id;
    }

    /**
     * Generate PDF for this invoice
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
                'iban' => 'DEXX XXXXX XXXX XXXX XXXX',
                'bic' => 'XXXXXXXXXXXX',
            ]),
            'tax_number' => Setting::get('tax_number', 'XX/XXX/XXXXX'),
            'eu_vat_id' => Setting::get('eu_vat_id', 'DEXXXXXXX'),
        ];

        $pdf = \PDF::loadView('pdfs.invoice', [
            'invoice' => $this,
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

        return "{$date}-Rechnung-{$customer}-{$this->invoice_number}.pdf";
    }

    /**
     * Upload invoice PDF to Paperless
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
                'title' => "Rechnung {$this->invoice_number} - {$this->customer->name}",
                'created' => $this->issue_date->format('Y-m-d'),
            ]);

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            if ($result && isset($result['id'])) {
                // Update invoice with Paperless document ID
                $this->update(['paperless_document_id' => $result['id']]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            // Clean up temp file on error
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            \Log::error('Failed to upload invoice to Paperless', [
                'invoice_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
