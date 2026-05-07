<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskMessage extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'user_id', 'message', 'attachment_path'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readStatuses(): HasMany
    {
        return $this->hasMany(MessageReadStatus::class, 'message_id');
    }

    public function isReadBy(int $userId): bool
    {
        return $this->readStatuses()->where('user_id', $userId)->exists();
    }
}
