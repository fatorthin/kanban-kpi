<?php

namespace App\Livewire\SubjectiveEvaluations;

use App\Models\SubjectiveEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public int $selectedMonth;
    public int $selectedYear;
    public string $search = '';

    public function mount()
    {
        $this->selectedMonth = (int) Carbon::now()->month;
        $this->selectedYear  = (int) Carbon::now()->year;
    }

    public function generateSessions()
    {
        if (!Auth::user()->isDirector() && !Auth::user()->isManager()) {
            session()->flash('error', 'Anda tidak memiliki hak akses untuk memicu pembuatan sesi evaluasi.');
            return;
        }

        Artisan::call('subjective-eval:generate', [
            '--month' => $this->selectedMonth,
            '--year'  => $this->selectedYear,
        ]);

        session()->flash('success', "Sesi Penilaian Subjektif untuk Periode {$this->selectedMonth}/{$this->selectedYear} berhasil diperbarui.");
    }

    public function render()
    {
        $user = Auth::user();

        $query = SubjectiveEvaluation::with(['user.division', 'evaluator'])
            ->where('month', $this->selectedMonth)
            ->where('year', $this->selectedYear);

        // Role-based visibility scope
        if ($user->hasRole('staff')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            // Manager sees self evaluation + subordinates
            $subordinateIds = $user->staffs()->pluck('users.id')->toArray();
            $subordinateIds[] = $user->id;

            $query->whereIn('user_id', $subordinateIds);
        }
        // Director sees all

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $evaluations = $query->orderBy('user_id')->paginate(15);

        return view('livewire.subjective-evaluations.index', [
            'evaluations' => $evaluations,
            'months'      => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ])->layout('components.layouts.app');
    }
}
