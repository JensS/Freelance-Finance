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

        return 0;
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
