<?php

namespace App\Models;

use App\Models\ActivityReadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'division_id', 
        'manager_id',
        'base_point_rate', 
        'fcm_token', 
        'notifications_read_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'base_point_rate'        => 'decimal:2',
            'notifications_read_at'  => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'pic_id');
    }

    public function managedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'manager_id');
    }

    public function originalTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'original_pic_id');
    }

    public function kpiReports(): HasMany
    {
        return $this->hasMany(KpiReport::class);
    }

    public function taskMessages(): HasMany
    {
        return $this->hasMany(TaskMessage::class);
    }

    public function activityReadStatuses(): HasMany
    {
        return $this->hasMany(ActivityReadStatus::class);
    }

    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }
}
