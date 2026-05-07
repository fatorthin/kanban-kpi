<?php

namespace App\Livewire\TaskLibrary;

use App\Models\TaskReference;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm    = false;
    public ?int $editingId   = null;
    public string $search    = '';
    public string $title     = '';
    public string $desc      = '';
    public string $type      = 'Client';
    public int $points       = 0;

    protected array $rules = [
        'title'  => 'required|string|max:255',
        'desc'   => 'nullable|string',
        'type'   => 'required|in:Client,Internal',
        'points' => 'required|integer|min:0',
    ];

    public function edit(int $id): void
    {
        $ref           = TaskReference::findOrFail($id);
        $this->editingId = $id;
        $this->title   = $ref->title;
        $this->desc    = $ref->description ?? '';
        $this->type    = $ref->task_type;
        $this->points  = $ref->default_difficulty_points;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title'                    => $this->title,
            'description'              => $this->desc,
            'task_type'                => $this->type,
            'default_difficulty_points'=> $this->points,
        ];

        if ($this->editingId) {
            TaskReference::findOrFail($this->editingId)->update($data);
        } else {
            TaskReference::create($data);
        }

        $this->reset(['showForm', 'editingId', 'title', 'desc', 'type', 'points']);
    }

    public function delete(int $id): void
    {
        TaskReference::findOrFail($id)->delete();
    }

    public function render(): \Illuminate\View\View
    {
        $refs = TaskReference::when($this->search, fn ($q) =>
            $q->where('title', 'like', '%' . $this->search . '%')
        )->latest()->get();

        return view('livewire.task-library.index', compact('refs'));
    }
}
