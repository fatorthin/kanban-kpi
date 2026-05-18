<?php

namespace App\Livewire\Manager;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LoadMonitoring extends Component
{
    public function render(): \Illuminate\View\View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get staff members with their current active tasks load
        $query = User::query()
            ->with(['assignedTasks' => function($query) {
                $query->whereNotIn('status', ['Completed', 'Canceled']);
            }]);

        if (!$user->isDirector()) {
            $directSubordinateIds = \Illuminate\Support\Facades\DB::table('manager_staff')
                ->where('manager_id', $user->id)
                ->pluck('staff_id');
            $indirectSubordinateIds = \Illuminate\Support\Facades\DB::table('manager_staff')
                ->whereIn('manager_id', $directSubordinateIds)
                ->pluck('staff_id');
            $allSubordinateIds = $directSubordinateIds->concat($indirectSubordinateIds)->unique();

            $query->whereIn('id', $allSubordinateIds);
        }

        $staff = $query->get()
            ->map(function($user) {
                $user->load_points = $user->assignedTasks->sum('difficulty_points');
                $user->task_count = $user->assignedTasks->count();
                return $user;
            })
            ->sortByDesc('load_points');

        return view('livewire.manager.load-monitoring', compact('staff'));
    }
}
