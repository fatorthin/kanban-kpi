<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvalIndicator extends Model
{
    use HasFactory;

    protected $fillable = ['eval_criterion_id', 'letter', 'statement', 'sort_order'];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvalCriterion::class, 'eval_criterion_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SubjectiveEvaluationScore::class, 'eval_indicator_id');
    }
}
