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
        'clientId'   => 'nullable|exists:clients,id',
        'picId'      => 'required|exists:users,id',
        'frequency'  => 'required|in:Daily,Weekly,Monthly,Yearly',
        'dayOfMonth' => 'nullable|integer|min:1|max:31',
    ];

    public function save(): void
    {
        $selectedRef = TaskReference::find($this->refId);
        $isClientTask = $selectedRef && $selectedRef->task_type === 'Client';

        $rules = $this->rules;
        if ($isClientTask) {
            $rules['clientId'] = 'required|exists:clients,id';
        }

        $this->validate($rules);

        $data = [
            'task_reference_id' => $this->refId,
            'client_id'         => $isClientTask ? $this->clientId : null,
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
        $user = Auth::user();
        if ($user->isDirector()) {
            $staff = User::all();
        } elseif ($user->isManager()) {
            $directSubordinateIds = \Illuminate\Support\Facades\DB::table('manager_staff')
                ->where('manager_id', $user->id)
                ->pluck('staff_id');
            $indirectSubordinateIds = \Illuminate\Support\Facades\DB::table('manager_staff')
                ->whereIn('manager_id', $directSubordinateIds)
                ->pluck('staff_id');
            $allSubordinateIds = $directSubordinateIds->concat($indirectSubordinateIds)->unique();
            $staff = User::whereIn('id', $allSubordinateIds)->get();
        } else {
            $staff = collect();
        }

        return view('livewire.recurring-tasks.index', [
            'recurringTasks' => RecurringTask::with(['taskReference', 'pic', 'client'])->get(),
            'references'     => TaskReference::all(),
            'clients'        => Client::all(),
            'staff'          => $staff,
        ]);
    }
}
