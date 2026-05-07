<?php

namespace App\Livewire\RecurringTasks;

use App\Models\RecurringTask;
use App\Models\TaskReference;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm     = false;
    public ?int $editingId    = null;
    public int $refId         = 0;
    public ?int $clientId     = null;
    public int $picId         = 0;
    public string $frequency  = 'Monthly';
    public ?int $dayOfMonth   = null;
    public bool $isActive     = true;

    protected array $rules = [
        'refId'      => 'required|exists:task_references,id',
        'picId'      => 'required|exists:users,id',
        'frequency'  => 'required|in:Daily,Weekly,Monthly,Yearly',
        'dayOfMonth' => 'nullable|integer|min:1|max:31',
    ];

    public function save(): void
    {
        $this->validate();

        $data = [
            'task_reference_id' => $this->refId,
            'client_id'         => $this->clientId,
            'pic_id'            => $this->picId,
            'manager_id'        => Auth::id(),
            'frequency'         => $this->frequency,
            'day_of_month'      => $this->dayOfMonth,
            'is_active'         => $this->isActive,
        ];

        if ($this->editingId) {
            RecurringTask::findOrFail($this->editingId)->update($data);
        } else {
            RecurringTask::create($data);
        }

        $this->reset(['showForm', 'editingId', 'refId', 'clientId', 'picId', 'frequency', 'dayOfMonth', 'isActive']);
    }

    public function toggle(int $id): void
    {
        $task = RecurringTask::findOrFail($id);
        $task->update(['is_active' => ! $task->is_active]);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.recurring-tasks.index', [
            'recurringTasks' => RecurringTask::with(['taskReference', 'pic', 'client'])->get(),
            'references'     => TaskReference::all(),
            'clients'        => Client::all(),
            'staff'          => User::role('staff')->get(),
        ]);
    }
}
