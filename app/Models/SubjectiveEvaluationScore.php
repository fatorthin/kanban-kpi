<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectiveEvaluationScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'subjective_evaluation_id',
        'eval_indicator_id',
        'self_score',
        'manager_score',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(SubjectiveEvaluation::class, 'subjective_evaluation_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(EvalIndicator::class, 'eval_indicator_id');
    }
}
