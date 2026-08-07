<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('subjective-evaluations.index') }}" wire:navigate class="btn btn-secondary btn-sm" style="height:36px;width:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                &larr;
            </a>
            <div>
                <h1 class="page-title" style="font-size:20px;font-weight:700;">Master Indikator Penilaian Subjektif</h1>
                <p class="text-secondary text-sm" style="color:var(--color-text-secondary);margin-top:2px;">
                    Kelola kategori, kriteria, dan item indikator penilaian kinerja dasar (RAFA).
                </p>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="card" style="background:color-mix(in srgb, var(--color-success) 10%, transparent);border-color:var(--color-success);padding:14px 20px;margin-bottom:20px;color:var(--color-success);font-weight:500;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:20px;">
        @foreach($categories as $category)
            <div class="card" style="overflow:hidden;">
                <div class="card-header" style="background:var(--color-bg);">
                    <h2 style="font-size:15px;font-weight:700;color:var(--color-text);">
                        {{ $category->code }}. {{ $category->name }}
                    </h2>
                </div>

                <div style="display:flex;flex-direction:column;divide-y:1px solid var(--color-border);">
                    @foreach($category->criteria as $criterion)
                        <div style="padding:20px;border-bottom:1px solid var(--color-border);">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                <h3 style="font-size:13px;font-weight:700;text-transform:uppercase;color:var(--color-text);letter-spacing:0.04em;">
                                    {{ $criterion->number }}. {{ $criterion->name }}
                                </h3>
                                <button wire:click="openCreateModal({{ $criterion->id }})" class="btn btn-ghost btn-sm" style="color:var(--color-primary);font-weight:600;">
                                    + Tambah Indikator
                                </button>
                            </div>

                            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                                @forelse($criterion->indicators as $indicator)
                                    <li class="list-item" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);background:var(--color-bg);display:flex;align-items:center;justify-content:space-between;">
                                        <div style="display:flex;align-items:flex-start;gap:8px;font-size:13px;">
                                            <strong style="color:var(--color-text);">{{ $indicator->letter }}.</strong>
                                            <span style="color:var(--color-text);">{{ $indicator->statement }}</span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                            <button wire:click="openEditModal({{ $indicator->id }})" class="btn btn-ghost btn-sm" style="padding:2px 8px;height:auto;">
                                                Edit
                                            </button>
                                            <button wire:click="deleteIndicator({{ $indicator->id }})" wire:confirm="Yakin ingin menghapus indikator ini?" class="btn btn-ghost btn-sm" style="color:var(--color-error);padding:2px 8px;height:auto;">
                                                Hapus
                                            </button>
                                        </div>
                                    </li>
                                @empty
                                    <li style="font-size:12px;color:var(--color-neutral);font-style:italic;">Belum ada indikator untuk kriteria ini.</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Indicator Modal --}}
    @if($showIndicatorModal)
        <div style="position:fixed;inset:0;z-index:99;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;">
            <div class="card" style="max-width:480px;width:100%;padding:24px;box-shadow:var(--shadow-dropdown);">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">
                    {{ $editingIndicatorId ? 'Edit Indikator' : 'Tambah Indikator Baru' }}
                </h3>

                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Huruf Poin (e.g. a, b, c)</label>
                        <input type="text" class="form-input" wire:model.defer="letter">
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Pernyataan Indikator</label>
                        <textarea class="form-textarea" wire:model.defer="statement" rows="3"></textarea>
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Urutan Sort Order</label>
                        <input type="number" class="form-input" wire:model.defer="sortOrder">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                    <button wire:click="closeModal" class="btn btn-secondary btn-sm">Batal</button>
                    <button wire:click="saveIndicator" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
