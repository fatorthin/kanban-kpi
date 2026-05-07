<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskReference extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'task_type', 'default_difficulty_points'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function recurringTasks(): HasMany
    {
        return $this->hasMany(RecurringTask::class);
    }
}
