<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxPayment extends Model
{
    protected $fillable = [
        'year',
        'type',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get all tax payments for a specific year
     */
    public static function forYear(int $year)
    {
        return static::where('year', $year)
            ->orderBy('payment_date')
            ->get();
    }

    /**
     * Get total tax paid for a year
     */
    public static function totalForYear(int $year): float
    {
        return (float) static::where('year', $year)->sum('amount');
    }
}
