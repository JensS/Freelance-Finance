<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $fillable = [
        'month',
        'year',
        'recommendations',
        'data',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'data' => 'array',
    ];

    /**
     * Get recommendation for specific month and year
     */
    public static function forMonth(int $month, int $year): ?self
    {
        return static::where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get all recommendations for a year
     */
    public static function forYear(int $year)
    {
        return static::where('year', $year)
            ->orderBy('month')
            ->get();
    }
}
