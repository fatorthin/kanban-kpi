<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiReport extends Model
{
    protected $fillable = [
        'user_id', 'month', 'year',
        'total_load_points', 'productivity_score', 'quality_score',
        'timeliness_score', 'final_kpi_score', 'total_incentive',
    ];

    protected $casts = [
        'productivity_score' => 'decimal:2',
        'quality_score'      => 'decimal:2',
        'timeliness_score'   => 'decimal:2',
        'final_kpi_score'    => 'decimal:2',
        'total_incentive'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
