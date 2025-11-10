<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLineItem extends Model
{
    protected $fillable = [
        'description',
        'unit_price',
        'unit',
        'usage_count',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'usage_count' => 'integer',
    ];

    /**
     * Increment usage count when this item is used
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get the most frequently used line items for autocomplete
     */
    public static function getMostUsed(int $limit = 10)
    {
        return static::orderBy('usage_count', 'desc')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
