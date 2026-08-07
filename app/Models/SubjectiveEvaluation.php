<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SubjectiveEvaluation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'evaluator_id',
        'month',
        'year',
        'self_status',
        'manager_status',
        'self_submitted_at',
        'manager_submitted_at',
        'average_self_score',
        'average_manager_score',
        'final_subjective_score',
        'notes',
    ];

    protected $casts = [
        'self_submitted_at'     => 'datetime',
        'manager_submitted_at'  => 'datetime',
        'average_self_score'    => 'decimal:2',
        'average_manager_score' => 'decimal:2',
        'final_subjective_score'=> 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('subjective_evaluation')
            ->setDescriptionForEvent(fn(string $eventName) => "Subjective evaluation {$eventName} for user #{$this->user_id} ({$this->month}/{$this->year})");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SubjectiveEvaluationScore::class, 'subjective_evaluation_id');
    }

    /**
     * Recalculate average self & manager scores and final subjective score.
     */
    public function recalculateScores(): void
    {
        $scores = $this->scores;
        
        $selfScores = $scores->pluck('self_score')->filter(fn($val) => !is_null($val));
        $managerScores = $scores->pluck('manager_score')->filter(fn($val) => !is_null($val));

        $avgSelf = $selfScores->count() > 0 ? $selfScores->average() : null;
        $avgManager = $managerScores->count() > 0 ? $managerScores->average() : null;

        $this->average_self_score = $avgSelf ? round($avgSelf, 2) : null;
        $this->average_manager_score = $avgManager ? round($avgManager, 2) : null;

        // Final subjective score can be manager score if available, or average of both
        if ($avgManager && $avgSelf) {
            $this->final_subjective_score = round(($avgSelf * 0.4) + ($avgManager * 0.6), 2);
        } else {
            $this->final_subjective_score = $avgManager ? round($avgManager, 2) : ($avgSelf ? round($avgSelf, 2) : null);
        }

        $this->save();
    }
}
