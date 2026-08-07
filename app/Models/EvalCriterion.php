<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvalCriterion extends Model
{
    use HasFactory;

    protected $fillable = ['eval_category_id', 'number', 'name', 'sort_order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EvalCategory::class, 'eval_category_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(EvalIndicator::class)->orderBy('sort_order');
    }
}
