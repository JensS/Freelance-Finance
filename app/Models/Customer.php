<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'address',
        'tax_id',
        'eu_vat_id',
        'notes',
    ];

    protected $casts = [
        'address' => 'array',
    ];

    /**
     * Get all invoices for this customer
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all quotes for this customer
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddressAttribute(): string
    {
        if (! $this->address) {
            return '';
        }

        $address = $this->address;

        return implode("\n", array_filter([
            $address['street'] ?? null,
            trim(($address['zip'] ?? '').' '.($address['city'] ?? '')),
        ]));
    }
}
