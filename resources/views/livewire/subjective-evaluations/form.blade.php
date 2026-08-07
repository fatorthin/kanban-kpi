<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('subjective-evaluations.index') }}" wire:navigate class="btn btn-secondary btn-sm" style="height:36px;width:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                &larr;
            </a>
            <div>
                <h1 class="page-title" style="font-size:20px;font-weight:700;">Lembar Penilaian Subjektif Kinerja</h1>
                <p class="text-secondary text-sm" style="color:var(--color-text-secondary);margin-top:2px;">
                    Pegawai: <strong style="color:var(--color-text);">{{ $evaluation->user->name }}</strong> ({{ $evaluation->user->division->name ?? 'Tanpa Divisi' }}) &bull; Periode: <strong>{{ $evaluation->month }}/{{ $evaluation->year }}</strong>
                </p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                🖨️ Cetak / Print
            </button>

            @if($canEditSelf && $evaluation->self_status !== 'Submitted')
                <button wire:click="saveSelfAssessment(true)" class="btn btn-primary btn-sm">
                    Kirim Self Assessment
                </button>
            @endif

            @if($canEditManager && $evaluation->manager_status !== 'Submitted')
                <button wire:click="saveManagerAssessment(true)" class="btn btn-primary btn-sm" style="background:var(--color-secondary);">
                    Simpan Penilaian Atasan
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

    {{-- Summary Cards --}}
    <div class="card" style="padding:20px;margin-bottom:24px;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;">
            <div>
                <span style="font-size:12px;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Status Self Assessment</span>
                @if($evaluation->self_status === 'Submitted')
                    <span class="badge badge-completed" style="font-size:13px;padding:4px 12px;">✓ Sudah Selesai</span>
                @else
                    <span class="badge badge-review" style="font-size:13px;padding:4px 12px;">⏳ Belum Dikirim</span>
                @endif
            </div>

            <div>
                <span style="font-size:12px;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Status Penilaian Atasan</span>
                @if($evaluation->manager_status === 'Submitted')
                    <span class="badge badge-completed" style="font-size:13px;padding:4px 12px;">✓ Sudah Dinilai oleh {{ $evaluation->evaluator->name ?? 'Atasan' }}</span>
                @else
                    <span class="badge badge-review" style="font-size:13px;padding:4px 12px;">⏳ Belum Dinilai</span>
                @endif
            </div>

            <div>
                <span style="font-size:12px;color:var(--color-text-secondary);display:block;margin-bottom:4px;">Skor Akhir Subjektif</span>
                <strong style="font-size:22px;color:var(--color-primary);letter-spacing:-0.02em;">
                    {{ $evaluation->final_subjective_score ? number_format($evaluation->final_subjective_score, 2) : '-' }} <span style="font-size:14px;color:var(--color-neutral);font-weight:400;">/ 5.00</span>
                </strong>
            </div>
        </div>
    </div>

    {{-- Assessment Comparison Table (Matches user's reference table image) --}}
    <div class="card" style="margin-bottom:24px;overflow:hidden;">
        <div class="table-wrapper" style="border:none;border-radius:var(--radius-xl);">
            <table>
                <thead>
                    <tr>
                        <th style="width:48px;text-align:center;padding:12px 14px;border-right:1px solid var(--color-border);">No</th>
                        <th style="padding:12px 16px;border-right:1px solid var(--color-border);">Item / Indikator Penilaian</th>
                        <th style="width:160px;text-align:center;padding:12px 14px;border-right:1px solid var(--color-border);">Self Assessment</th>
                        <th style="width:160px;text-align:center;padding:12px 14px;">Penilaian Atasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        {{-- Category Row: e.g. I. Kompetensi Dasar --}}
                        <tr style="background:var(--color-bg);font-weight:700;">
                            <td style="text-align:center;padding:10px 14px;border-right:1px solid var(--color-border);font-weight:700;">
                                {{ $category->code }}.
                            </td>
                            <td colspan="3" style="padding:10px 16px;font-size:14px;font-weight:700;">
                                {{ $category->name }}
                            </td>
                        </tr>

                        @foreach($category->criteria as $criterion)
                            {{-- Criterion Row: e.g. 1 Rispek --}}
                            <tr style="background:color-mix(in srgb, var(--color-bg) 50%, var(--color-surface));font-weight:600;">
                                <td style="text-align:center;padding:8px 14px;border-right:1px solid var(--color-border);font-weight:600;">
                                    {{ $criterion->number }}
                                </td>
                                <td colspan="3" style="padding:8px 16px;font-weight:600;color:var(--color-text);">
                                    {{ $criterion->name }}
                                </td>
                            </tr>

                            @foreach($criterion->indicators as $indicator)
                                {{-- Indicator Row: e.g. a. Menerima dan menghargai... --}}
                                <tr>
                                    <td style="text-align:center;padding:10px 14px;border-right:1px solid var(--color-border);color:var(--color-neutral);font-weight:500;">
                                        {{ $indicator->letter }}.
                                    </td>
                                    <td style="padding:10px 16px;border-right:1px solid var(--color-border);line-height:1.5;">
                                        {{ $indicator->statement }}
                                    </td>

                                    {{-- Self Assessment Cell --}}
                                    <td style="text-align:center;padding:8px 14px;border-right:1px solid var(--color-border);vertical-align:middle;">
                                        @if($canEditSelf && $evaluation->self_status !== 'Submitted')
                                            <select class="form-select" wire:model.defer="selfScores.{{ $indicator->id }}" style="padding:4px 8px;font-size:13px;font-weight:600;text-align:center;width:100%;">
                                                <option value="">- Pilih -</option>
                                                <option value="5">5 - Sangat Baik</option>
                                                <option value="4">4 - Baik</option>
                                                <option value="3">3 - Cukup</option>
                                                <option value="2">2 - Kurang</option>
                                                <option value="1">1 - Sangat Kurang</option>
                                            </select>
                                        @else
                                            <strong style="font-size:14px;color:var(--color-text);">
                                                {{ $selfScores[$indicator->id] ?? '-' }}
                                            </strong>
                                        @endif
                                    </td>

                                    {{-- Manager Assessment Cell --}}
                                    <td style="text-align:center;padding:8px 14px;vertical-align:middle;">
                                        @if($canEditManager && $evaluation->manager_status !== 'Submitted')
                                            <select class="form-select" wire:model.defer="managerScores.{{ $indicator->id }}" style="padding:4px 8px;font-size:13px;font-weight:600;text-align:center;width:100%;">
                                                <option value="">- Pilih -</option>
                                                <option value="5">5 - Sangat Baik</option>
                                                <option value="4">4 - Baik</option>
                                                <option value="3">3 - Cukup</option>
                                                <option value="2">2 - Kurang</option>
                                                <option value="1">1 - Sangat Kurang</option>
                                            </select>
                                        @else
                                            <strong style="font-size:14px;color:var(--color-text);">
                                                {{ $managerScores[$indicator->id] ?? '-' }}
                                            </strong>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach

                    {{-- Summary Total Row --}}
                    <tr style="background:var(--color-bg);font-weight:700;border-top:2px solid var(--color-border);">
                        <td colspan="2" style="text-align:right;padding:12px 16px;border-right:1px solid var(--color-border);font-[600];">
                            RATA-RATA SKOR
                        </td>
                        <td style="text-align:center;padding:12px 14px;border-right:1px solid var(--color-border);font-size:15px;font-weight:700;">
                            {{ $evaluation->average_self_score ? number_format($evaluation->average_self_score, 2) : '-' }}
                        </td>
                        <td style="text-align:center;padding:12px 14px;font-size:15px;font-weight:700;color:var(--color-primary);">
                            {{ $evaluation->average_manager_score ? number_format($evaluation->average_manager_score, 2) : '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Manager Feedback Notes --}}
    <div class="card" style="padding:20px;margin-bottom:24px;">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:8px;">Catatan & Ulasan Atasan</h3>
        @if($canEditManager && $evaluation->manager_status !== 'Submitted')
            <textarea class="form-textarea" wire:model.defer="notes" rows="3" placeholder="Masukkan ulasan atau masukan untuk pengembangan staf..."></textarea>
            <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                <button wire:click="saveManagerAssessment(false)" class="btn btn-secondary btn-sm">
                    Simpan Draft Penilaian
                </button>
            </div>
        @else
            <p style="font-size:13px;color:var(--color-text-secondary);font-style:italic;">
                {{ $evaluation->notes ?: 'Tidak ada catatan khusus dari atasan.' }}
            </p>
        @endif
    </div>

    @if($canEditSelf && $evaluation->self_status !== 'Submitted')
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button wire:click="saveSelfAssessment(false)" class="btn btn-secondary btn-md">
                Simpan Draft Self Assessment
            </button>
            <button wire:click="saveSelfAssessment(true)" class="btn btn-primary btn-md">
                Kirim Self Assessment Sekarang
            </button>
        </div>
    @endif
</div>
