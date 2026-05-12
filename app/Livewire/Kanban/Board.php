<?php

namespace App\Livewire\Kanban;

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\TaskReference;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Board extends Component
{
    protected $listeners = ['taskMessagesRead' => '$refresh'];

    public ?int $filterPic      = null;
    public ?int $filterDivision = null;
    public string $filterType   = '';
    public string $search       = '';
    
    public array $limits = [
        'New'         => 5,
        'In_Progress' => 5,
        'Review'      => 5,
        'Revision'    => 5,
        'Completed'   => 5,
    ];

    public function loadMore(string $status): void
    {
        $this->limits[$status] += 5;
    }

    public function resetSearch(): void
    {
        $this->search = '';
        $this->reset('limits');
    }

    // Task creation/edit form
    public bool $showForm        = false;
    public ?int $editingTaskId   = null;
    public string $formTitle     = '';
    public string $formDesc      = '';
    public string $formType      = 'Client';
    public int $formPoints       = 0;
    public ?int $formClientId    = null;
    public ?int $formPicId       = null;
    public ?int $formRefId       = null;
    public string $formDeadline  = '';

    // Takeover reassignment
    public bool $showTakeoverModal = false;
    public ?int $takeoverTaskId   = null;
    public ?int $takeoverPicId    = null;

    protected array $rules = [
        'formTitle'    => 'required|string|max:255',
        'formDesc'     => 'nullable|string',
        'formType'     => 'required|in:Client,Internal',
        'formPoints'   => 'required|integer|min:0',
        'formClientId' => 'required_if:formType,Client|nullable|exists:clients,id',
        'formPicId'    => 'required|exists:users,id',
        'formDeadline' => 'required|date',
    ];

    public function loadReference(int $refId): void
    {
        $ref = TaskReference::find($refId);
        if ($ref) {
            $this->formTitle    = $ref->title;
            $this->formDesc     = $ref->description ?? '';
            $this->formPoints   = $ref->default_difficulty_points;
            $this->formType     = $ref->task_type;
        }
    }

    public function moveTask(int $taskId, string $newStatus): void
    {
        $task = Task::findOrFail($taskId);
        /** @var User $user */
        $user = Auth::user();
        $oldStatus = $task->status;

        $allowed = $this->canTransition($user, $task, $newStatus);
        if (! $allowed) {
            $this->dispatch('notify', type: 'error', message: 'You do not have permission to move this task.');
            return;
        }

        $task->previous_status = $oldStatus;
        $task->status = $newStatus;

        if ($newStatus === 'Revision' && $oldStatus !== 'Revision') {
            $task->revision_count++;
        }

        if ($newStatus === 'Completed') {
            $task->completed_at = now();
        } else {
            $task->completed_at = null;
        }

        $task->save();
        $this->dispatch('notify', type: 'success', message: 'Task moved to ' . $newStatus . '.', taskId: $taskId);
    }

    public function undoMove(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        if ($task->previous_status) {
            $task->status          = $task->previous_status;
            $task->previous_status = null;
            $task->completed_at    = null;
            $task->save();
        }
    }

    public function takeoverTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        /** @var User $user */
        $user = Auth::user();

        if (! $task->isTakeoverEligible()) {
            $this->dispatch('notify', type: 'error', message: 'This task is not eligible for takeover.');
            return;
        }

        // If manager, show modal to pick a subordinate
        if ($user->isManager()) {
            $this->takeoverTaskId = $taskId;
            $this->takeoverPicId = null;
            $this->showTakeoverModal = true;
            return;
        }

        // Default behavior (staff/director): take it for themselves
        $this->executeTakeover($task, $user->id);
    }

    public function confirmTakeover(): void
    {
        $this->validate([
            'takeoverPicId' => 'required|exists:users,id'
        ]);

        $task = Task::findOrFail($this->takeoverTaskId);
        $this->executeTakeover($task, $this->takeoverPicId);
        $this->showTakeoverModal = false;
    }

    private function executeTakeover(Task $task, int $newPicId): void
    {
        /** @var User $causer */
        $causer = Auth::user();
        $newPic = User::findOrFail($newPicId);

        if ($task->pic_id === $newPicId) {
            $this->dispatch('notify', type: 'error', message: 'User is already the PIC of this task.');
            return;
        }

        $task->original_pic_id  = $task->original_pic_id ?? $task->pic_id;
        $task->pic_id           = $newPicId;
        $task->is_takeover      = true;
        $task->takeover_reason  = 'Deadline Breached';
        $task->save();

        activity()->causedBy($causer)->performedOn($task)
            ->withProperties([
                'original_pic_id' => $task->original_pic_id,
                'new_pic_id'      => $newPicId,
                'reason'          => 'Deadline Breached',
            ])
            ->log("Tugas \"{$task->title}\" diambil alih dan dialihkan ke {$newPic->name}.");

        $this->dispatch('notify', type: 'success', message: 'Task taken over and reassigned.');
    }

    public function saveTask(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (! ($user->isManager() || $user->isDirector())) {
            return;
        }

        $this->validate();

        $data = [
            'title'            => $this->formTitle,
            'description'      => $this->formDesc,
            'task_type'        => $this->formType,
            'difficulty_points' => $this->formPoints,
            'client_id'        => $this->formType === 'Client' ? $this->formClientId : null,
            'pic_id'           => $this->formPicId,
            'task_reference_id' => $this->formRefId,
            'manager_id'       => $user->id,
            'deadline'         => $this->formDeadline,
            'status'           => 'New',
        ];

        if ($this->editingTaskId) {
            Task::findOrFail($this->editingTaskId)->update($data);
        } else {
            Task::create($data);
        }

        $this->reset([
            'showForm',
            'editingTaskId',
            'formTitle',
            'formDesc',
            'formType',
            'formPoints',
            'formClientId',
            'formPicId',
            'formRefId',
            'formDeadline'
        ]);
    }

    public function updateTaskOrder(int $taskId, string $newStatus, array $newOrder): void
    {
        $task = Task::findOrFail($taskId);
        /** @var User $user */
        $user = Auth::user();

        // Check if status changed and validate transition
        if ($task->status !== $newStatus) {
            $oldStatus = $task->status;

            if (! $this->canTransition($user, $task, $newStatus)) {
                $this->dispatch('notify', type: 'error', message: 'You do not have permission to move this task.');
                return;
            }

            $task->previous_status = $oldStatus;
            $task->status = $newStatus;

            if ($newStatus === 'Revision' && $oldStatus !== 'Revision') {
                $task->revision_count++;
            }

            if ($newStatus === 'Completed') {
                $task->completed_at = now();
            } else {
                $task->completed_at = null;
            }
            $task->save();
            $this->dispatch('notify', type: 'success', message: 'Task moved to ' . $newStatus . '.');
        }

        // Update the sort_order for all tasks in the target column
        foreach ($newOrder as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    private function canTransition(User $user, Task $task, string $newStatus): bool
    {
        if ($user->isDirector()) {
            return true;
        }

        if ($user->isManager() && $task->manager_id === $user->id) {
            return in_array($newStatus, ['Completed', 'Revision']);
        }

        if ($user->isStaff() && $task->pic_id === $user->id) {
            $allowed = [
                'New' => ['In_Progress'],
                'In_Progress' => ['Review'],
                'Revision' => ['In_Progress'],
            ];
            return in_array($newStatus, $allowed[$task->status] ?? []);
        }

        return false;
    }

    public function render(): \Illuminate\View\View
    {
        /** @var User $user */
        $user     = Auth::user();
        $statuses = ['New', 'In_Progress', 'Review', 'Revision', 'Completed'];

        $query = Task::with(['pic', 'client', 'manager', 'originalPic'])
            ->withCount([
                'messages as unread_messages_count' => fn($messageQuery) => $messageQuery
                    ->where('user_id', '!=', $user->id)
                    ->whereDoesntHave('readStatuses', fn($readQuery) => $readQuery->where('user_id', $user->id)),
            ]);

        if ($user->isStaff()) {
            $query->where('pic_id', $user->id);
        } elseif ($user->isManager()) {
            $query->where(function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                    ->orWhereHas('pic', fn($sq) => $sq->where('manager_id', $user->id));
            });
            if ($this->filterPic) {
                $query->where('pic_id', $this->filterPic);
            }
        }
        // Director sees all, with optional filters
        if ($user->isDirector()) {
            if ($this->filterDivision) {
                $query->whereHas('pic', fn($q) => $q->where('division_id', $this->filterDivision));
            }
            if ($this->filterPic) {
                $query->where('pic_id', $this->filterPic);
            }
        }

        if ($this->filterType) {
            $query->where('task_type', $this->filterType);
        }

        if ($this->search !== '') {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhereHas('client', fn($clientQuery) => $clientQuery->where('name', 'like', $search))
                    ->orWhereHas('pic', fn($picQuery) => $picQuery->where('name', 'like', $search));
            });
        }

        $tasks = [];
        $hasMore = [];
        $totalCounts = [];

        foreach ($statuses as $status) {
            $columnQuery = (clone $query)->where('status', $status);
            $totalCounts[$status] = $columnQuery->count();
            $tasks[$status] = $columnQuery->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->limit($this->limits[$status])
                ->get();
            
            $hasMore[$status] = $totalCounts[$status] > $this->limits[$status];
        }

        $staff = User::role('staff');
        if ($user->isManager()) {
            $staff->where('manager_id', $user->id);
        }
        $staff = $staff->get();
        $clients    = Client::all();
        $references = TaskReference::all();

        return view('livewire.kanban.board', compact('tasks', 'statuses', 'staff', 'clients', 'references', 'hasMore', 'totalCounts'));
    }
}
