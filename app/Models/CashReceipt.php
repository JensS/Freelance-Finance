<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashReceipt extends Model
{
    protected $fillable = [
        'receipt_date',
        'correspondent',
        'description',
        'category',
        'amount',
        'net_amount',
        'vat_rate',
        'vat_amount',
        'note',
        'paperless_document_id',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    /**
     * Get VAT rate from category
     */
    public function getVatRate(): int
    {
        if (str_contains($this->category, '0%')) {
            return 0;
        }
        if (str_contains($this->category, '7%')) {
            return 7;
        }
        if (str_contains($this->category, '19%')) {
            return 19;
        }

        return 0;
    }

    /**
     * Calculate and set net/gross breakdown from gross amount
     */
    public function calculateNetGross(): void
    {
        // @phpstan-ignore-next-line
        if ($this->amount === null) {
            return;
        }

        // Get VAT rate from category
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
}
