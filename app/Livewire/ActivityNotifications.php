<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityNotifications extends Component
{
    public function getListeners()
    {
        return [
            "echo:task_activity,TaskUpdated" => '$refresh',
            "notify" => '$refresh',
        ];
    }

    public function markAllRead(): void
    {
        $user = Auth::user();
        $user->notifications_read_at = now();
        $user->save();
    }

    public function render()
    {
        $user = Auth::user();

        // Fetch recent activities visible to this user
        $activities = Activity::latest()
            ->with(['causer', 'subject'])
            ->where(function ($q) use ($user) {
                $q->where('causer_id', $user->id)
                  ->orWhere('subject_type', 'App\Models\Task');
            })
            ->take(15)
            ->get();

        // Count unread: activities created after the user's last read timestamp
        $unreadCount = $activities->filter(function ($activity) use ($user) {
            if (is_null($user->notifications_read_at)) {
                return true;
            }
            return $activity->created_at->gt($user->notifications_read_at);
        })->count();

        return view('livewire.activity-notifications', [
            'activities'  => $activities,
            'unreadCount' => $unreadCount,
        ]);
    }
}
