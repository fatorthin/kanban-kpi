<?php

namespace App\Livewire;

use App\Models\KpiReport;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): \Illuminate\View\View
    {
        $user = Auth::user();

        if ($user->isDirector() || $user->isManager()) {
            // Manager/Director: Division-wide stats
            $scope = $user->isDirector()
                ? Task::query()
                : Task::where('manager_id', $user->id);

            $stats = [
                'total'       => (clone $scope)->count(),
                'in_progress' => (clone $scope)->where('status', 'In_Progress')->count(),
                'review'      => (clone $scope)->where('status', 'Review')->count(),
                'completed'   => (clone $scope)->where('status', 'Completed')->count(),
                'takeovers'   => (clone $scope)->where('is_takeover', true)->count(),
            ];

            $latestReport = KpiReport::where('user_id', $user->id)
                ->orderByDesc('year')->orderByDesc('month')
                ->first();

        } else {
            // Staff: Personal stats
            $scope = Task::where('pic_id', $user->id);

            $stats = [
                'total'       => (clone $scope)->count(),
                'in_progress' => (clone $scope)->where('status', 'In_Progress')->count(),
                'review'      => (clone $scope)->where('status', 'Review')->count(),
                'completed'   => (clone $scope)->where('status', 'Completed')->count(),
                'takeovers'   => (clone $scope)->where('is_takeover', true)->count(),
            ];

            $latestReport = KpiReport::where('user_id', $user->id)
                ->orderByDesc('year')->orderByDesc('month')
                ->first();
        }

        $recentTasks = (clone $scope)->with(['client', 'pic'])->latest()->take(5)->get();

        return view('livewire.dashboard', compact('stats', 'latestReport', 'recentTasks'));
    }
}
