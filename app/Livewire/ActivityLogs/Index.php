<?php

namespace App\Livewire\ActivityLogs;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';

    public function render(): \Illuminate\View\View
    {
        $logs = Activity::with(['causer', 'subject'])
            ->when($this->search, fn ($q) =>
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('log_name', 'like', '%' . $this->search . '%')
            )
            ->latest()
            ->paginate(30);

        return view('livewire.activity-logs.index', compact('logs'));
    }
}
