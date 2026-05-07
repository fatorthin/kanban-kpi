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

    protected $listeners = ['openTask' => 'open'];

    public function open(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->task   = Task::with(['pic', 'originalPic', 'manager', 'client', 'taskReference', 'messages.user'])->findOrFail($taskId);
        $this->isOpen = true;
        $this->markAllRead();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->task   = null;
        $this->taskId = null;
    }

    public function sendMessage(): void
    {
        $this->validate(['newMessage' => 'required|string|max:2000']);
        $user = Auth::user();

        $msg = TaskMessage::create([
            'task_id' => $this->taskId,
            'user_id' => $user->id,
            'message' => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->task       = Task::with(['messages.user'])->findOrFail($this->taskId);
        $this->markAllRead();
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
