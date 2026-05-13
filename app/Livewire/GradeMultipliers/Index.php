<?php

namespace App\Livewire\GradeMultipliers;

use App\Models\GradeMultiplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?float $multiplierValue = null;
    public bool $showAddForm = false;
    public ?string $newGrade = null;
    public float $newMultiplier = 1.0;
    public ?int $editingId = null;

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $multiplier = GradeMultiplier::find($id);
        $this->multiplierValue = $multiplier->multiplier;
    }

    public function save()
    {
        $this->validate([
            'multiplierValue' => 'required|numeric|min:0.1|max:10',
        ]);

        $multiplier = GradeMultiplier::find($this->editingId);
        $multiplier->update([
            'multiplier' => $this->multiplierValue,
        ]);

        $this->editingId = null;
        $this->dispatch('notify', type: 'success', message: 'Multiplier updated successfully.');
    }

    public function cancel()
    {
        $this->editingId = null;
        $this->showAddForm = false;
    }

    public function create()
    {
        $this->validate([
            'newGrade' => 'required|string|max:2|unique:grade_multipliers,grade',
            'newMultiplier' => 'required|numeric|min:0.1|max:10',
        ]);

        GradeMultiplier::create([
            'grade' => strtoupper($this->newGrade),
            'multiplier' => $this->newMultiplier,
        ]);

        $this->reset(['newGrade', 'newMultiplier', 'showAddForm']);
        $this->dispatch('notify', type: 'success', message: 'New grade added successfully.');
    }

    public function delete(int $id)
    {
        GradeMultiplier::find($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Grade deleted.');
    }

    public function render()
    {
        $multipliers = GradeMultiplier::orderBy('grade')->get();
        return view('livewire.grade-multipliers.index', compact('multipliers'));
    }
}
