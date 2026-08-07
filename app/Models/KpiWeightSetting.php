<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiWeightSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'production_weight',
        'quality_weight',
        'timeliness_weight',
        'subjective_weight',
    ];

    protected $casts = [
        'production_weight' => 'decimal:2',
        'quality_weight'    => 'decimal:2',
        'timeliness_weight' => 'decimal:2',
        'subjective_weight' => 'decimal:2',
    ];

    /**
     * Get weights for a specific period, or fallback to default template, or hardcoded defaults.
     * Returns decimal multipliers (e.g. ['production' => 0.25, 'quality' => 0.35, ...])
     */
    public static function getWeightsForPeriod(?int $month = null, ?int $year = null): array
    {
        $setting = null;

        // 1. Try period-specific setting
        if ($month && $year) {
            $setting = static::where('month', $month)->where('year', $year)->first();
        }

        // 2. Fallback to default template (month IS NULL AND year IS NULL)
        if (!$setting) {
            $setting = static::whereNull('month')->whereNull('year')->first();
        }

        $prodWeight = $setting ? (float) $setting->production_weight : 25.0;
        $qualWeight = $setting ? (float) $setting->quality_weight : 35.0;
        $timeWeight = $setting ? (float) $setting->timeliness_weight : 25.0;
        $subjWeight = $setting ? (float) $setting->subjective_weight : 15.0;

        return [
            'production' => round($prodWeight / 100.0, 4),
            'quality'    => round($qualWeight / 100.0, 4),
            'timeliness' => round($timeWeight / 100.0, 4),
            'subjective' => round($subjWeight / 100.0, 4),
            'raw'        => [
                'production' => $prodWeight,
                'quality'    => $qualWeight,
                'timeliness' => $timeWeight,
                'subjective' => $subjWeight,
            ],
        ];
    }
}
