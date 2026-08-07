<?php

namespace App\Livewire\SubjectiveEvaluations;

use App\Models\EvalCategory;
use App\Models\SubjectiveEvaluation;
use App\Models\SubjectiveEvaluationScore;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public int $evaluationId;
    public SubjectiveEvaluation $evaluation;

    public array $selfScores = [];
    public array $managerScores = [];
    public ?string $notes = '';

    public bool $canEditSelf = false;
    public bool $canEditManager = false;

    public function mount(int $id)
    {
        $this->evaluationId = $id;
        $this->loadEvaluation();
    }

    public function loadEvaluation()
    {
        $this->evaluation = SubjectiveEvaluation::with([
            'user.division',
            'evaluator',
            'scores.indicator.criterion.category'
        ])->findOrFail($this->evaluationId);

        $currentUser = Auth::user();

        // Permissions check
        $isSelf = $currentUser->id === $this->evaluation->user_id;
        $isManager = $currentUser->isDirector() || $currentUser->id === $this->evaluation->evaluator_id;

        $this->canEditSelf = $isSelf;
        $this->canEditManager = $isManager;

        $this->notes = $this->evaluation->notes;

        // Fill existing scores into component properties
        foreach ($this->evaluation->scores as $score) {
            $this->selfScores[$score->eval_indicator_id] = $score->self_score;
            $this->managerScores[$score->eval_indicator_id] = $score->manager_score;
        }
    }

    public function saveSelfAssessment(bool $submit = true)
    {
        if (!$this->canEditSelf) {
            session()->flash('error', 'Anda tidak memiliki hak akses untuk mengedit Self Assessment ini.');
            return;
        }

        foreach ($this->selfScores as $indicatorId => $score) {
            SubjectiveEvaluationScore::updateOrCreate(
                [
                    'subjective_evaluation_id' => $this->evaluation->id,
                    'eval_indicator_id'        => $indicatorId,
                ],
                [
                    'self_score' => $score !== '' ? (int) $score : null,
                ]
            );
        }

        if ($submit) {
            $this->evaluation->self_status = 'Submitted';
            $this->evaluation->self_submitted_at = now();
        }

        $this->evaluation->recalculateScores();
        $this->loadEvaluation();

        session()->flash('success', $submit ? 'Self Assessment berhasil dikirimkan.' : 'Draft Self Assessment disimpan.');
    }

    public function saveManagerAssessment(bool $submit = true)
    {
        if (!$this->canEditManager) {
            session()->flash('error', 'Anda tidak memiliki hak akses untuk mengedit Penilaian Atasan ini.');
            return;
        }

        foreach ($this->managerScores as $indicatorId => $score) {
            SubjectiveEvaluationScore::updateOrCreate(
                [
                    'subjective_evaluation_id' => $this->evaluation->id,
                    'eval_indicator_id'        => $indicatorId,
                ],
                [
                    'manager_score' => $score !== '' ? (int) $score : null,
                ]
            );
        }

        $this->evaluation->notes = $this->notes;

        if ($submit) {
            $this->evaluation->manager_status = 'Submitted';
            $this->evaluation->manager_submitted_at = now();
        }

        $this->evaluation->recalculateScores();
        $this->loadEvaluation();

        session()->flash('success', $submit ? 'Penilaian Atasan berhasil disimpan & dikirimkan.' : 'Draft Penilaian Atasan disimpan.');
    }

    public function render()
    {
        $categories = EvalCategory::with(['criteria.indicators'])->orderBy('sort_order')->get();

        return view('livewire.subjective-evaluations.form', [
            'categories' => $categories,
        ])->layout('components.layouts.app');
    }
}
