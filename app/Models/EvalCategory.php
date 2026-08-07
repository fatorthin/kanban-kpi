<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvalCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'sort_order'];

    public function criteria(): HasMany
    {
        return $this->hasMany(EvalCriterion::class)->orderBy('sort_order');
    }
}
