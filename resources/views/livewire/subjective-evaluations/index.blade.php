<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
        <div>
            <h1 class="page-title" style="font-size:24px;font-weight:700;letter-spacing:-0.03em;">Penilaian Subjektif Kinerja</h1>
            <p class="text-secondary text-sm" style="color:var(--color-text-secondary);margin-top:4px;">
                Evaluasi kompetensi dasar (Self Assessment & Penilaian Atasan) bulanan.
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            @if(auth()->user()->isDirector() || auth()->user()->isManager())
                <a href="{{ route('subjective-evaluations.indicators') }}" wire:navigate class="btn btn-secondary btn-md">
                    ⚙️ Master Indikator
                </a>

                <button wire:click="generateSessions" wire:loading.attr="disabled" class="btn btn-primary btn-md">
                    <span wire:loading.remove>🔄 Buka Sesi Bulan Ini</span>
                    <span wire:loading>Processing...</span>
                </button>
            @endif
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

    {{-- Filter Options --}}
    <div class="card" style="padding:16px 20px;margin-bottom:24px;">
        <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
            <div>
                <label class="form-label">Bulan</label>
                <select class="form-select" wire:model.live="selectedMonth" style="width:auto;min-width:140px;">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Tahun</label>
                <select class="form-select" wire:model.live="selectedYear" style="width:auto;min-width:100px;">
                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div style="flex:1;min-width:200px;">
                <label class="form-label">Pencarian</label>
                <input type="text" class="form-input" wire:model.live.debounce.300ms="search" placeholder="Cari nama staff...">
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card">
        <div class="table-wrapper" style="border:none;border-radius:var(--radius-xl);">
            <table>
                <thead>
                    <tr>
                        <th style="padding:12px 16px;">PEGAWAI (STAFF)</th>
                        <th style="padding:12px 16px;">PENILAI (ATASAN)</th>
                        <th style="padding:12px 16px;text-align:center;">STATUS SELF ASSESSMENT</th>
                        <th style="padding:12px 16px;text-align:center;">STATUS PENILAIAN ATASAN</th>
                        <th style="padding:12px 16px;text-align:center;">RATA-RATA SELF</th>
                        <th style="padding:12px 16px;text-align:center;">RATA-RATA ATASAN</th>
                        <th style="padding:12px 16px;text-align:center;">SKOR AKHIR SUBJEKTIF</th>
                        <th style="padding:12px 16px;text-align:right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evaluations as $eval)
                        <tr>
                            <td style="padding:14px 16px;">
                                <strong style="font-size:14px;color:var(--color-text);display:block;">{{ $eval->user->name }}</strong>
                                <span style="font-size:12px;color:var(--color-text-secondary);">{{ $eval->user->division->name ?? 'Tanpa Divisi' }}</span>
                            </td>
                            <td style="padding:14px 16px;color:var(--color-text-secondary);">
                                {{ $eval->evaluator->name ?? 'Belum Ditentukan' }}
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                @if($eval->self_status === 'Submitted')
                                    <span class="badge badge-completed">Sudah Selesai</span>
                                @else
                                    <span class="badge badge-review">Draft / Belum</span>
                                @endif
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                @if($eval->manager_status === 'Submitted')
                                    <span class="badge badge-completed">Sudah Dinilai</span>
                                @else
                                    <span class="badge badge-review">Draft / Belum</span>
                                @endif
                            </td>
                            <td style="padding:14px 16px;text-align:center;font-weight:600;">
                                {{ $eval->average_self_score ? number_format($eval->average_self_score, 2) : '-' }}
                            </td>
                            <td style="padding:14px 16px;text-align:center;font-weight:600;">
                                {{ $eval->average_manager_score ? number_format($eval->average_manager_score, 2) : '-' }}
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <strong style="font-size:15px;color:var(--color-primary);">
                                    {{ $eval->final_subjective_score ? number_format($eval->final_subjective_score, 2) : '-' }}
                                </strong>
                            </td>
                            <td style="padding:14px 16px;text-align:right;">
                                <a href="{{ route('subjective-evaluations.show', $eval->id) }}" wire:navigate class="btn btn-ghost btn-sm" style="color:var(--color-primary);font-weight:600;">
                                    @if(auth()->id() === $eval->user_id && $eval->self_status !== 'Submitted')
                                        Isi Self Assessment &rarr;
                                    @elseif((auth()->user()->isDirector() || auth()->id() === $eval->evaluator_id) && $eval->manager_status !== 'Submitted')
                                        Isi Penilaian Atasan &rarr;
                                    @else
                                        Lihat Lembar Evaluasi &rarr;
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:48px;color:var(--color-text-secondary);">
                                Belum ada sesi penilaian subjektif untuk periode {{ $selectedMonth }}/{{ $selectedYear }}.<br>
                                @if(auth()->user()->isDirector() || auth()->user()->isManager())
                                    <span style="font-size:13px;margin-top:8px;display:inline-block;">Klik tombol <strong>"🔄 Buka Sesi Bulan Ini"</strong> di atas untuk memulai.</span>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evaluations->hasPages())
            <div style="padding:16px 24px;border-top:1px solid var(--color-border);">
                {{ $evaluations->links() }}
            </div>
        @endif
    </div>
</div>
