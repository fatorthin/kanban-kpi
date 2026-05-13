<?php

namespace App\Livewire\Divisions;

use App\Models\Division;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $search = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:divisions,name',
    ];

    public function edit(int $id)
    {
        $division = Division::findOrFail($id);
        $this->editingId = $id;
        $this->name = $division->name;
        $this->showForm = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['name'] = 'required|string|max:255|unique:divisions,name,' . $this->editingId;
        }
        $this->validate($rules);

        if ($this->editingId) {
            Division::findOrFail($this->editingId)->update(['name' => $this->name]);
        } else {
            Division::create(['name' => $this->name]);
        }

        $this->reset(['showForm', 'editingId', 'name']);
        $this->dispatch('notify', type: 'success', message: 'Division saved successfully.');
    }

    public function delete(int $id)
    {
        $division = Division::findOrFail($id);
        if ($division->users()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Cannot delete division with active users.');
            return;
        }
        $division->delete();
        $this->dispatch('notify', type: 'success', message: 'Division deleted.');
    }

    public function render()
    {
        $divisions = Division::when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->withCount('users')
            ->get();

        return view('livewire.divisions.index', compact('divisions'));
    }
}
