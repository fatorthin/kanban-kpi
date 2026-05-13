<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Grade Multipliers</h1>
            <p class="text-secondary text-sm mt-1">Configure productivity multipliers for each client grade.</p>
        </div>
        <button class="btn btn-primary btn-md" wire:click="$set('showAddForm', true)">+ Add Grade</button>
    </div>

    @if($showAddForm)
    <div class="card" style="margin-bottom:24px; padding:20px; border:2px solid var(--color-primary);">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">Add New Grade</h3>
        <div class="flex-responsive" style="display:flex; gap:16px; align-items:flex-end;">
            <div style="flex:1;">
                <label class="form-label">Grade Name (e.g. H)</label>
                <input type="text" class="form-input" wire:model="newGrade" placeholder="H">
                @error('newGrade') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div style="flex:1;">
                <label class="form-label">Multiplier Value</label>
                <input type="number" step="0.1" class="form-input" wire:model="newMultiplier">
                @error('newMultiplier') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex; gap:8px;">
                <button class="btn btn-primary btn-md" wire:click="create">Create Grade</button>
                <button class="btn btn-secondary btn-md" wire:click="$set('showAddForm', false)">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Current Multiplier</th>
                        <th style="width:200px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($multipliers as $m)
                    <tr>
                        <td style="font-weight:600;">Grade {{ $m->grade }}</td>
                        <td>
                            @if($editingId === $m->id)
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <input type="number" step="0.1" class="form-input" style="width:100px;" wire:model="multiplierValue">
                                    <span class="text-xs text-muted">x</span>
                                </div>
                                @error('multiplierValue') <span class="form-error">{{ $message }}</span> @enderror
                            @else
                                <span class="badge badge-grade-{{ strtolower($m->grade) }}" style="font-size:14px;padding:6px 12px;">
                                    {{ number_format($m->multiplier, 2) }}x
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($editingId === $m->id)
                                <div style="display:flex;gap:6px;">
                                    <button class="btn btn-primary btn-sm" wire:click="save">Save</button>
                                    <button class="btn btn-secondary btn-sm" wire:click="cancel">Cancel</button>
                                </div>
                            @else
                                <div style="display:flex;gap:6px;">
                                    <button class="btn btn-ghost btn-sm" wire:click="edit({{ $m->id }})">Edit Value</button>
                                    <button class="btn btn-danger btn-sm" wire:click="delete({{ $m->id }})" wire:confirm="Are you sure you want to delete this grade?">Delete</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:24px; padding:20px; background:var(--color-bg); border:1px solid var(--color-border); border-radius:var(--radius-lg); display:flex; gap:16px; align-items:flex-start;">
        <div style="width:40px; height:40px; border-radius:10px; background:rgba(79, 70, 229, 0.1); color:var(--color-primary); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px; height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Informasi Perhitungan</h4>
            <p style="font-size:13px; color:var(--color-text-secondary); line-height:1.6;">
                Multiplier ini digunakan untuk memberikan bobot lebih pada klien dengan grade tinggi. 
                Sistem akan secara otomatis mengalikan <strong>Difficulty Points</strong> dari setiap tugas dengan multiplier grade klien yang bersangkutan.
                <br><br>
                <span style="color:var(--color-primary); font-weight:500;">Rumus:</span> Poin Akhir = Poin Kesulitan × Multiplier Grade
            </p>
        </div>
    </div>
</div>
