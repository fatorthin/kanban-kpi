<?php

namespace App\Livewire\SubjectiveEvaluations;

use App\Models\EvalCategory;
use App\Models\EvalCriterion;
use App\Models\EvalIndicator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Indicators extends Component
{
    public bool $showIndicatorModal = false;
    public ?int $editingIndicatorId = null;

    public int $selectedCriterionId = 0;
    public string $letter = 'a';
    public string $statement = '';
    public int $sortOrder = 1;

    protected $rules = [
        'selectedCriterionId' => 'required|exists:eval_criteria,id',
        'letter'              => 'required|string|max:5',
        'statement'           => 'required|string|min:3',
        'sortOrder'           => 'required|integer|min:1',
    ];

    public function mount()
    {
        if (!Auth::user()->isDirector() && !Auth::user()->isManager()) {
            abort(403, 'Akses ditolak.');
        }
    }

    public function openCreateModal(int $criterionId)
    {
        $this->resetInputFields();
        $this->selectedCriterionId = $criterionId;
        $this->showIndicatorModal = true;
    }

    public function openEditModal(int $indicatorId)
    {
        $indicator = EvalIndicator::findOrFail($indicatorId);
        $this->editingIndicatorId = $indicator->id;
        $this->selectedCriterionId = $indicator->eval_criterion_id;
        $this->letter = $indicator->letter;
        $this->statement = $indicator->statement;
        $this->sortOrder = $indicator->sort_order;
        $this->showIndicatorModal = true;
    }

    public function saveIndicator()
    {
        $this->validate();

        EvalIndicator::updateOrCreate(
            ['id' => $this->editingIndicatorId],
            [
                'eval_criterion_id' => $this->selectedCriterionId,
                'letter'            => $this->letter,
                'statement'         => $this->statement,
                'sort_order'        => $this->sortOrder,
            ]
        );

        session()->flash('success', $this->editingIndicatorId ? 'Indikator berhasil diperbarui.' : 'Indikator baru berhasil ditambahkan.');
        $this->closeModal();
    }

    public function deleteIndicator(int $indicatorId)
    {
        EvalIndicator::findOrFail($indicatorId)->delete();
        session()->flash('success', 'Indikator berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showIndicatorModal = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->editingIndicatorId = null;
        $this->selectedCriterionId = 0;
        $this->letter = 'a';
        $this->statement = '';
        $this->sortOrder = 1;
    }

    public function render()
    {
        $categories = EvalCategory::with(['criteria.indicators'])->orderBy('sort_order')->get();

        return view('livewire.subjective-evaluations.indicators', [
            'categories' => $categories,
        ])->layout('components.layouts.app');
    }
}
