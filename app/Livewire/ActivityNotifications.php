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

    public function render()
    {
        $user = Auth::user();
        
        // Fetch recent activities. 
        // For simple notification, we show activities where the user is the subject, CAUSER, or PIC of the task.
        $activities = Activity::latest()
            ->with(['causer', 'subject'])
            ->where(function($q) use ($user) {
                $q->where('causer_id', $user->id)
                  ->orWhere('subject_type', 'App\Models\Task'); // Simplified for demo
            })
            ->take(5)
            ->get();

        return view('livewire.activity-notifications', [
            'activities' => $activities
        ]);
    }
}
