<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    protected $fillable = [
        'month',
        'year',
        'total_income',
        'total_expenses',
        'net_income',
        'data',
        'report_pdf_path',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'total_income' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_income' => 'decimal:2',
        'data' => 'array',
    ];

    /**
     * Get report for specific month and year
     */
    public static function forMonth(int $month, int $year): ?self
    {
        return static::where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get all reports for a year
     */
    public static function forYear(int $year)
    {
        return static::where('year', $year)
            ->orderBy('month')
            ->get();
    }
}
