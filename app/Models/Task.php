<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Task extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'task_reference_id', 'client_id', 'pic_id', 'original_pic_id',
        'manager_id', 'title', 'description', 'task_type', 'status',
        'previous_status', 'is_takeover', 'takeover_reason', 'sort_order',
        'difficulty_points', 'revision_count', 'deadline', 'completed_at',
    ];

    protected $casts = [
        'deadline'     => 'datetime',
        'completed_at' => 'datetime',
        'is_takeover'  => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('task_activity')
            ->setDescriptionForEvent(fn(string $eventName) => $this->getFriendlyEventDescription($eventName));
    }

    protected function getFriendlyEventDescription(string $eventName): string
    {
        $statusMap = [
            'New' => 'Baru',
            'In_Progress' => 'Sedang Dikerjakan',
            'Review' => 'Siap Direview',
            'Revision' => 'Perlu Revisi',
            'Completed' => 'Selesai'
        ];

        if ($eventName === 'created') {
            return "Tugas baru ditambahkan: \"{$this->title}\"";
        }

        if ($eventName === 'updated') {
            if ($this->isDirty('status')) {
                $newStatus = $statusMap[$this->status] ?? $this->status;
                return "Status tugas \"{$this->title}\" diperbarui menjadi: {$newStatus}";
            }
            if ($this->isDirty('pic_id')) {
                return "PIC tugas \"{$this->title}\" dipindahkan.";
            }
            return "Informasi tugas \"{$this->title}\" diperbarui.";
        }

        if ($eventName === 'deleted') {
            return "Tugas \"{$this->title}\" dihapus dari sistem.";
        }

        return "Aktivitas pada tugas: {$this->title}";
    }

    // ---------------------------------------------------------------- Relations

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

    public function originalPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_pic_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TaskMessage::class);
    }

    // ---------------------------------------------------------------- Helpers

    /**
     * Determine if the task is eligible to be taken over.
     * Eligible: deadline passed by more than 24 hours AND not yet completed.
     */
    public function isTakeoverEligible(): bool
    {
        if ($this->status === 'Completed') {
            return false;
        }

        return $this->deadline && $this->deadline->addHours(24)->isPast();
    }

    /**
     * Calculate the adjusted difficulty points after a takeover rescue bonus.
     */
    public function rescueBonusPoints(): int
    {
        return (int) round($this->difficulty_points * 1.2);
    }

    /**
     * Calculate the penalty points deducted from the original PIC.
     */
    public function penaltyPoints(): int
    {
        return (int) round($this->difficulty_points * 0.5);
    }
}
