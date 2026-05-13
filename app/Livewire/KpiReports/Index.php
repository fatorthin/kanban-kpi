<?php

namespace App\Livewire\KpiReports;

use App\Models\KpiReport;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public int $month;
    public int $year;
    public ?int $selectedUserId = null;

    public function mount(): void
    {
        $this->month = (int) now()->format('m');
        $this->year  = (int) now()->format('Y');
    }

    public function generateReports(): void
    {
        if (!Auth::user()->isDirector()) {
            return;
        }

        Artisan::call('kpi:generate', [
            'month' => $this->month,
            'year'  => $this->year,
        ]);

        $this->dispatch('notify', type: 'success', message: 'KPI reports generated for ' . $this->month . '/' . $this->year);
    }

    public function render(): \Illuminate\View\View
    {
        $user = Auth::user();

        $query = KpiReport::with('user')
            ->where('month', $this->month)
            ->where('year', $this->year);

        if ($user->isStaff()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isManager()) {
            $divisionUserIds = User::where('division_id', $user->division_id)->pluck('id');
            $query->whereIn('user_id', $divisionUserIds);
        }

        $reports = $query->orderByDesc('final_kpi_score')->get();

        $detail = $this->selectedUserId
            ? KpiReport::where('user_id', $this->selectedUserId)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->with('user')
                ->first()
            : null;

        $users = $user->isDirector() ? User::all() : null;

        return view('livewire.kpi-reports.index', compact('reports', 'detail', 'users'));
    }
}
