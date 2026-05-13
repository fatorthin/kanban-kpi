<?php

namespace App\Livewire\Kanban;

use App\Models\Task;
use App\Models\TaskMessage;
use App\Models\MessageReadStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TaskSlideOver extends Component
{
    public ?int $taskId   = null;
    public ?Task $task    = null;
    public bool $isOpen   = false;
    public string $newMessage = '';
    public string $newDeadline = '';
    public bool $isEditingDeadline = false;

    protected $listeners = ['openTask' => 'open'];

    public function open(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->task   = Task::with(['pic', 'originalPic', 'manager', 'client', 'taskReference', 'messages.user'])->findOrFail($taskId);
        $this->isOpen = true;
        $this->markAllRead();
        $this->dispatch('taskMessagesRead');
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->task   = null;
        $this->taskId = null;
    }

    public function editDeadline(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->isDirector()) return;
        $this->newDeadline = $this->task->deadline->format('Y-m-d\TH:i');
        $this->isEditingDeadline = true;
    }

    public function saveDeadline(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->isDirector()) return;
        $this->validate(['newDeadline' => 'required|date']);

        $oldDeadline = $this->task->deadline->format('d M Y, H:i');
        $this->task->update(['deadline' => $this->newDeadline]);
        
        activity()
            ->useLog('task_activity')
            ->causedBy($user)
            ->performedOn($this->task)
            ->log("Deadline tugas diubah dari {$oldDeadline} menjadi " . $this->task->deadline->format('d M Y, H:i'));

        $this->isEditingDeadline = false;
        $this->task->refresh();
        $this->dispatch('notify', type: 'success', message: 'Deadline updated.');
    }

    public function cancelEditDeadline(): void
    {
        $this->isEditingDeadline = false;
    }

    public function sendMessage(): void
    {
        $this->validate(['newMessage' => 'required|string|max:2000']);
        $user = Auth::user();
        $task = Task::findOrFail($this->taskId);

        $msg = TaskMessage::create([
            'task_id' => $this->taskId,
            'user_id' => $user->id,
            'message' => $this->newMessage,
        ]);

        activity()
            ->useLog('task_activity')
            ->causedBy($user)
            ->performedOn($task)
            ->withProperties([
                'task_id' => $task->id,
                'message_id' => $msg->id,
                'message_preview' => mb_substr($msg->message, 0, 120),
            ])
            ->log('Pesan baru pada tugas: "' . $task->title . '"');

        $this->newMessage = '';
        $this->task       = Task::with(['messages.user'])->findOrFail($this->taskId);
        $this->markAllRead();
        $this->dispatch('notify', type: 'success', message: 'Message sent.');
        $this->dispatch('taskMessagesRead');
    }

    private function markAllRead(): void
    {
        $userId = Auth::id();
        $messages = TaskMessage::where('task_id', $this->taskId)
            ->where('user_id', '!=', $userId)
            ->get();

        foreach ($messages as $msg) {
            MessageReadStatus::updateOrCreate(
                ['message_id' => $msg->id, 'user_id' => $userId],
                ['read_at'    => now()]
            );
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.kanban.task-slide-over');
    }
}
