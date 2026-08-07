<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
        <div>
            <h1 class="page-title" style="font-size:24px;font-weight:700;letter-spacing:-0.03em;">Pengaturan Bobot Komponen KPI</h1>
            <p class="text-secondary text-sm" style="color:var(--color-text-secondary);margin-top:4px;">
                Atur komposisi persentase bobot Production, Quality, Timeliness, dan Penilaian Subjektif (Khusus Role Director).
            </p>
        </div>

        <div>
            <button wire:click="openPeriodModal()" class="btn btn-primary btn-md">
                + Tambah Bobot Periode Khusus
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="card" style="background:color-mix(in srgb, var(--color-success) 10%, transparent);border-color:var(--color-success);padding:14px 20px;margin-bottom:20px;color:var(--color-success);font-weight:500;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="card" style="background:color-mix(in srgb, var(--color-error) 10%, transparent);border-color:var(--color-error);padding:14px 20px;margin-bottom:20px;color:var(--color-error);font-weight:500;">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Global Default Weights Card --}}
    <div class="card" style="padding:24px;margin-bottom:24px;">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">
            ⚙️ Bobot Standar Global (Default Template)
        </h3>
        <p style="font-size:13px;color:var(--color-text-secondary);margin-bottom:20px;">
            Bobot ini digunakan secara otomatis pada saat melakukan pembentukan Laporan KPI bulanan apabila tidak ada bobot khusus periode yang ditentukan. Total penjumlahan seluruh komponen harus tepat **100%**.
        </p>

        <form wire:submit.prevent="saveDefaultWeights">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:20px;margin-bottom:20px;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="font-weight:600;">Production Weight (%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-input" wire:model="defaultProduction" style="font-weight:700;color:var(--color-primary);">
                    <span style="font-size:11px;color:var(--color-neutral);margin-top:4px;display:block;">Skor Poin Tugas Selesai</span>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="font-weight:600;">Quality Weight (%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-input" wire:model="defaultQuality" style="font-weight:700;color:var(--color-primary);">
                    <span style="font-size:11px;color:var(--color-neutral);margin-top:4px;display:block;">Persentase Bebas Revisi</span>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="font-weight:600;">Timeliness Weight (%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-input" wire:model="defaultTimeliness" style="font-weight:700;color:var(--color-primary);">
                    <span style="font-size:11px;color:var(--color-neutral);margin-top:4px;display:block;">Ketepatan Waktu Deadline</span>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="font-weight:600;">Subjective Weight (%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-input" wire:model="defaultSubjective" style="font-weight:700;color:var(--color-primary);">
                    <span style="font-size:11px;color:var(--color-neutral);margin-top:4px;display:block;">Evaluasi Self & Atasan</span>
                </div>
            </div>

            @php
                $defaultTotal = $defaultProduction + $defaultQuality + $defaultTimeliness + $defaultSubjective;
            @endphp

            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--color-bg);border-radius:var(--radius-lg);border:1px solid var(--color-border);flex-wrap:wrap;gap:12px;">
                <div style="font-size:13px;font-weight:600;">
                    Total Bobot: 
                    <strong style="font-size:16px;color:{{ abs($defaultTotal - 100.0) < 0.01 ? 'var(--color-success)' : 'var(--color-error)' }}">
                        {{ $defaultTotal }}%
                    </strong>
                    @if(abs($defaultTotal - 100.0) >= 0.01)
                        <span style="font-size:12px;color:var(--color-error);margin-left:8px;">⚠️ Total harus tepat 100%!</span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-md" {{ abs($defaultTotal - 100.0) >= 0.01 ? 'disabled' : '' }}>
                    💾 Simpan Bobot Standar (Default)
                </button>
            </div>
        </form>
    </div>

    {{-- Period Specific Weights Card & Table --}}
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:15px;font-weight:600;">
                📅 Riwayat Konfigurasi Bobot Khusus Periode Bulanan
            </h3>
        </div>

        <div class="table-wrapper" style="border:none;border-radius:var(--radius-xl);">
            <table>
                <thead>
                    <tr>
                        <th style="padding:12px 16px;">Periode Bulan/Tahun</th>
                        <th style="padding:12px 16px;text-align:center;">Production (25%)</th>
                        <th style="padding:12px 16px;text-align:center;">Quality (35%)</th>
                        <th style="padding:12px 16px;text-align:center;">Timeliness (25%)</th>
                        <th style="padding:12px 16px;text-align:center;">Subjective (15%)</th>
                        <th style="padding:12px 16px;text-align:center;">Total</th>
                        <th style="padding:12px 16px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodSettings as $setting)
                        @php
                            $pTotal = $setting->production_weight + $setting->quality_weight + $setting->timeliness_weight + $setting->subjective_weight;
                        @endphp
                        <tr>
                            <td style="padding:14px 16px;font-weight:600;">
                                {{ $months[$setting->month] ?? $setting->month }} {{ $setting->year }}
                            </td>
                            <td style="padding:14px 16px;text-align:center;font-weight:600;">
                                {{ number_format($setting->production_weight, 1) }}%
                            </td>
                            <td style="padding:14px 16px;text-align:center;font-weight:600;">
                                {{ number_format($setting->quality_weight, 1) }}%
                            </td>
                            <td style="padding:14px 16px;text-align:center;font-weight:600;">
                                {{ number_format($setting->timeliness_weight, 1) }}%
                            </td>
                            <td style="padding:14px 16px;text-align:center;font-weight:600;color:var(--color-primary);">
                                {{ number_format($setting->subjective_weight, 1) }}%
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <span class="badge {{ abs($pTotal - 100.0) < 0.01 ? 'badge-completed' : 'badge-revision' }}">
                                    {{ number_format($pTotal, 1) }}%
                                </span>
                            </td>
                            <td style="padding:14px 16px;text-align:right;">
                                <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                                    <button wire:click="openPeriodModal({{ $setting->id }})" class="btn btn-ghost btn-sm">
                                        Edit
                                    </button>
                                    <button wire:click="deletePeriodWeight({{ $setting->id }})" wire:confirm="Hapus bobot khusus periode ini?" class="btn btn-ghost btn-sm" style="color:var(--color-error);">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:48px;color:var(--color-text-secondary);">
                                Belum ada penyesuaian bobot khusus per bulan.<br>
                                Seluruh pembuatan laporan KPI saat ini menggunakan <strong>Bobot Standar Global (Default)</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Period Weight Modal --}}
    @if($showPeriodModal)
        <div style="position:fixed;inset:0;z-index:99;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;">
            <div class="card" style="max-width:520px;width:100%;padding:24px;box-shadow:var(--shadow-dropdown);">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;border-bottom:1px solid var(--color-border);padding-bottom:12px;">
                    {{ $editingId ? 'Edit Bobot Khusus Periode' : 'Tambah Bobot Khusus Periode' }}
                </h3>

                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div style="display:flex;gap:12px;">
                        <div class="form-group" style="margin:0;flex:1;">
                            <label class="form-label">Bulan Periode</label>
                            <select class="form-select" wire:model="selectedMonth">
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin:0;flex:1;">
                            <label class="form-label">Tahun Periode</label>
                            <select class="form-select" wire:model="selectedYear">
                                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Production (%)</label>
                            <input type="number" step="0.1" class="form-input" wire:model="periodProduction">
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Quality (%)</label>
                            <input type="number" step="0.1" class="form-input" wire:model="periodQuality">
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Timeliness (%)</label>
                            <input type="number" step="0.1" class="form-input" wire:model="periodTimeliness">
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Subjective (%)</label>
                            <input type="number" step="0.1" class="form-input" wire:model="periodSubjective">
                        </div>
                    </div>

                    @php
                        $modalTotal = $periodProduction + $periodQuality + $periodTimeliness + $periodSubjective;
                    @endphp
                    <div style="padding:10px 14px;background:var(--color-bg);border-radius:var(--radius-md);font-size:13px;font-weight:600;">
                        Total Persentase: 
                        <strong style="color:{{ abs($modalTotal - 100.0) < 0.01 ? 'var(--color-success)' : 'var(--color-error)' }}">
                            {{ $modalTotal }}%
                        </strong>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                    <button wire:click="closeModal" class="btn btn-secondary btn-sm">Batal</button>
                    <button wire:click="savePeriodWeights" class="btn btn-primary btn-sm" {{ abs($modalTotal - 100.0) >= 0.01 ? 'disabled' : '' }}>
                        Simpan Bobot Periode
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
