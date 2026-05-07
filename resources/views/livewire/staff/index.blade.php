<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Staff & Divisions</h1>
            <p class="text-secondary text-sm mt-1">Manage team members, roles, and point rates.</p>
        </div>
        <button class="btn btn-primary btn-md" id="btn-add-staff" wire:click="$set('showForm', true)">+ Add Staff</button>
    </div>

    <div style="margin-bottom:16px;">
        <input type="text" class="form-input" style="max-width:320px;" wire:model.live="search" placeholder="Search staff...">
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Division</th>
                    <th>Point Rate</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:9999px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <span style="font-weight:500;">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td style="color:var(--color-text-secondary);">{{ $user->email }}</td>
                    <td>
                        @php $roleColors = ['director'=>'badge-revision','manager'=>'badge-review','staff'=>'badge-new']; @endphp
                        <span class="badge {{ $roleColors[$user->roles->first()?->name ?? 'staff'] ?? 'badge-new' }}">
                            {{ ucfirst($user->roles->first()?->name ?? '—') }}
                        </span>
                    </td>
                    <td style="color:var(--color-text-secondary);">{{ $user->division?->name ?? '—' }}</td>
                    <td style="font-weight:500;">Rp {{ number_format($user->base_point_rate, 0, ',', '.') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-ghost btn-sm" wire:click="edit({{ $user->id }})">Edit</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--color-neutral);padding:40px;">No staff found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal" id="modal-staff-form">
            <div class="modal-header">
                <h2 style="font-size:16px;font-weight:600;">{{ $editingId ? 'Edit Staff' : 'Add Staff' }}</h2>
                <button class="btn btn-ghost btn-sm" wire:click="$set('showForm', false)">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" class="form-input" wire:model="name">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-input" wire:model="email">
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password {{ $editingId ? '(leave blank to keep)' : '*' }}</label>
                    <input type="password" class="form-input" wire:model="password">
                    @error('password')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select class="form-select" wire:model="role">
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="director">Director</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Division</label>
                        <select class="form-select" wire:model="divisionId">
                            <option value="">— None —</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Base Point Rate (Rp/point) *</label>
                    <input type="number" class="form-input" wire:model="pointRate" min="0">
                    @error('pointRate')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" id="btn-save-staff" wire:click="save">Save</button>
            </div>
        </div>
    </div>
    @endif
</div>
