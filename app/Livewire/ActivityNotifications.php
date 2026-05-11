<?php

namespace App\Livewire;

use App\Models\ActivityReadStatus;
use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityNotifications extends Component
{
    public function markAsRead(int $activityId): void
    {
        $user = Auth::user();
        $activity = $this->relevantActivitiesQuery($user)->whereKey($activityId)->first();

        if (! $activity) {
            return;
        }

        ActivityReadStatus::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $user->id],
            ['read_at' => now()]
        );
    }

    public function markAllRead(): void
    {
        $user = Auth::user();
        $activityIds = $this->relevantActivitiesQuery($user)->pluck('id');

        foreach ($activityIds as $activityId) {
            ActivityReadStatus::updateOrCreate(
                ['activity_id' => $activityId, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }
        
        // Also update the simple timestamp for backward compatibility if needed
        $user->notifications_read_at = now();
        $user->save();
    }

    public function getListeners()
    {
        return [
            "echo:task_activity,TaskUpdated" => '$refresh',
            "notify" => '$refresh',
        ];
    }

    public function render()
    {
        $user = Auth::user();

        $activities = $this->relevantActivitiesQuery($user)
            ->latest()
            ->take(15)
            ->get();

        $readActivityIds = ActivityReadStatus::query()
            ->where('user_id', $user->id)
            ->whereIn('activity_id', $activities->pluck('id'))
            ->pluck('activity_id')
            ->all();

        foreach ($activities as $activity) {
            $activity->is_read = in_array($activity->id, $readActivityIds, true);
        }

        $unreadCount = $this->relevantActivitiesQuery($user)
            ->whereNotIn('id', ActivityReadStatus::query()
                ->select('activity_id')
                ->where('user_id', $user->id))
            ->count();

        return view('livewire.activity-notifications', [
            'activities' => $activities,
            'unreadCount' => $unreadCount,
        ]);
    }

    private function relevantActivitiesQuery(User $user)
    {
        $involvedTaskIds = Task::query()
            ->where(function ($q) use ($user) {
                $q->where('pic_id', $user->id)
                    ->orWhere('manager_id', $user->id)
                    ->orWhereHas('messages', fn($mq) => $mq->where('user_id', $user->id));
            })
            ->pluck('id');

        return Activity::query()
            ->with(['causer', 'subject'])
            ->where(function ($q) use ($user, $involvedTaskIds) {
                $q->where('causer_id', $user->id)
                    ->orWhere(function ($taskQ) use ($involvedTaskIds) {
                        $taskQ->where('subject_type', Task::class)
                            ->whereIn('subject_id', $involvedTaskIds);
                    });
            })
            ->where(function ($q) use ($user) {
                $q->where('description', 'not like', 'Pesan baru pada tugas:%')
                    ->orWhere(function ($msgQ) use ($user) {
                        $msgQ->where('description', 'like', 'Pesan baru pada tugas:%')
                            ->where('causer_id', '!=', $user->id);
                    });
            });
    }
}
