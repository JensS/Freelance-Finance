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
        'note',
        'paperless_document_id',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
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
}
