<?php

namespace App\Livewire\KpiWeights;

use App\Models\KpiWeightSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    // Default Weight Template State
    public float $defaultProduction = 25.0;
    public float $defaultQuality = 35.0;
    public float $defaultTimeliness = 25.0;
    public float $defaultSubjective = 15.0;

    // Period Specific Weight State
    public int $selectedMonth;
    public int $selectedYear;
    public float $periodProduction = 25.0;
    public float $periodQuality = 35.0;
    public float $periodTimeliness = 25.0;
    public float $periodSubjective = 15.0;

    public bool $showPeriodModal = false;
    public ?int $editingId = null;

    public function mount()
    {
        if (!Auth::user()->isDirector()) {
            abort(403, 'Hanya Level Director yang berhak mengelola Pengaturan Bobot KPI.');
        }

        $this->selectedMonth = (int) Carbon::now()->month;
        $this->selectedYear  = (int) Carbon::now()->year;

        $this->loadDefaultWeights();
    }

    public function loadDefaultWeights()
    {
        $default = KpiWeightSetting::whereNull('month')->whereNull('year')->first();
        if ($default) {
            $this->defaultProduction = (float) $default->production_weight;
            $this->defaultQuality    = (float) $default->quality_weight;
            $this->defaultTimeliness = (float) $default->timeliness_weight;
            $this->defaultSubjective = (float) $default->subjective_weight;
        }
    }

    public function saveDefaultWeights()
    {
        $total = $this->defaultProduction + $this->defaultQuality + $this->defaultTimeliness + $this->defaultSubjective;
        if (abs($total - 100.0) > 0.01) {
            session()->flash('error', "Total penjumlahan bobot harus tepat 100%. Saat ini: {$total}%");
            return;
        }

        KpiWeightSetting::updateOrCreate(
            ['month' => null, 'year' => null],
            [
                'production_weight' => $this->defaultProduction,
                'quality_weight'    => $this->defaultQuality,
                'timeliness_weight' => $this->defaultTimeliness,
                'subjective_weight' => $this->defaultSubjective,
            ]
        );

        session()->flash('success', 'Pengaturan Bobot Standar (Default) berhasil disimpan!');
    }

    public function openPeriodModal(?int $id = null)
    {
        $this->editingId = $id;

        if ($id) {
            $setting = KpiWeightSetting::findOrFail($id);
            $this->selectedMonth    = (int) $setting->month;
            $this->selectedYear     = (int) $setting->year;
            $this->periodProduction = (float) $setting->production_weight;
            $this->periodQuality    = (float) $setting->quality_weight;
            $this->periodTimeliness = (float) $setting->timeliness_weight;
            $this->periodSubjective = (float) $setting->subjective_weight;
        } else {
            $this->periodProduction = $this->defaultProduction;
            $this->periodQuality    = $this->defaultQuality;
            $this->periodTimeliness = $this->defaultTimeliness;
            $this->periodSubjective = $this->defaultSubjective;
        }

        $this->showPeriodModal = true;
    }

    public function savePeriodWeights()
    {
        $total = $this->periodProduction + $this->periodQuality + $this->periodTimeliness + $this->periodSubjective;
        if (abs($total - 100.0) > 0.01) {
            session()->flash('error', "Total penjumlahan bobot periode harus tepat 100%. Saat ini: {$total}%");
            return;
        }

        KpiWeightSetting::updateOrCreate(
            ['id' => $this->editingId],
            [
                'month'             => $this->selectedMonth,
                'year'              => $this->selectedYear,
                'production_weight' => $this->periodProduction,
                'quality_weight'    => $this->periodQuality,
                'timeliness_weight' => $this->periodTimeliness,
                'subjective_weight' => $this->periodSubjective,
            ]
        );

        session()->flash('success', "Bobot KPI khusus periode {$this->selectedMonth}/{$this->selectedYear} berhasil disimpan!");
        $this->closeModal();
    }

    public function deletePeriodWeight(int $id)
    {
        KpiWeightSetting::findOrFail($id)->delete();
        session()->flash('success', 'Konfigurasi bobot khusus periode berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showPeriodModal = false;
        $this->editingId = null;
    }

    public function render()
    {
        $periodSettings = KpiWeightSetting::whereNotNull('month')
            ->whereNotNull('year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('livewire.kpi-weights.index', [
            'periodSettings' => $periodSettings,
            'months'         => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
        ])->layout('components.layouts.app');
    }
}
