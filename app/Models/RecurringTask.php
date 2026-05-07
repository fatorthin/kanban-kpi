<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_reference_id', 'client_id', 'pic_id', 'manager_id',
        'frequency', 'day_of_month', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taskReference(): BelongsTo
    {
        return $this->belongsTo(TaskReference::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
