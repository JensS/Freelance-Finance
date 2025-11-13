<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $fillable = [
        'transaction_date',
        'correspondent',
        'title',
        'description',
        'type',
        'category',
        'amount',
        'net_amount',
        'vat_rate',
        'vat_amount',
        'currency',
        'original_amount',
        'original_currency',
        'note',
        'matched_paperless_document_id',
        'bank_statement_id',
        'invoice_id',
        'is_validated',
        'is_business_expense',
        'raw_data',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'is_validated' => 'boolean',
        'is_business_expense' => 'boolean',
    ];

    /**
     * Check if transaction is private (should be ignored)
     */
    public function isPrivate(): bool
    {
        return $this->type === 'Privat';
    }

    /**
     * Get VAT rate from type
     */
    public function getVatRate(): int
    {
        if (str_contains($this->type, '0%')) {
            return 0;
        }
        if (str_contains($this->type, '7%')) {
            return 7;
        }
        if (str_contains($this->type, '19%')) {
            return 19;
        }
        if (str_contains($this->type, 'Reverse Charge')) {
            return 0;
        }

        return 0;
    }

    /**
     * Calculate and set net/gross breakdown from gross amount
     */
    public function calculateNetGross(): void
    {
        if ($this->amount === null) {
            return;
        }

        // Get VAT rate from type
        $vatRate = $this->getVatRate();
        $this->vat_rate = $vatRate;

        // Calculate net amount (reverse calculation from gross)
        if ($vatRate > 0) {
            $this->net_amount = round($this->amount / (1 + ($vatRate / 100)), 2);
        } else {
            $this->net_amount = $this->amount;
        }

        // Calculate VAT amount
        $this->vat_amount = $this->amount - $this->net_amount;
    }

    /**
     * Get available transaction types for the verification form
     */
    public static function getTransactionTypes(): array
    {
        return [
            'Privat' => 'Privat (nicht berücksichtigt)',
            'Geschäftsausgabe 0%' => 'Geschäftsausgabe 0% (international)',
            'Geschäftsausgabe 7%' => 'Geschäftsausgabe 7% MwSt',
            'Geschäftsausgabe 19%' => 'Geschäftsausgabe 19% MwSt',
            'Bewirtung' => 'Bewirtung (Geschäftsessen)',
            'Reverse Charge' => 'Reverse Charge (EU B2B)',
            'Einkommen 19%' => 'Einkommen 19% MwSt',
            'Umsatzsteuerstattung' => 'Umsatzsteuererstattung',
            'Steuerzahlung' => 'Steuerzahlung',
            'Nicht kategorisiert' => 'Nicht kategorisiert',
        ];
    }

    /**
     * Check if transaction is income
     */
    public function isIncome(): bool
    {
        return $this->amount > 0 && str_contains($this->type, 'Einkommen');
    }

    /**
     * Check if transaction is expense
     */
    public function isExpense(): bool
    {
        return $this->amount < 0 && str_contains($this->type, 'Geschäftsausgabe');
    }

    /**
     * Get the invoice associated with this transaction.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
