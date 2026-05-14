<div>
    <div class="flex flex-responsive items-center justify-between mb-6">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="text-secondary text-sm mt-1">Manage system users, roles, and division assignments.</p>
        </div>
        <button class="btn btn-primary btn-md w-full-mobile" id="btn-add-staff" wire:click="$set('showForm', true)">+ Add User</button>
    </div>

    <div style="margin-bottom:16px;">
        <input type="text" class="form-input w-full-mobile" style="max-width:320px;" wire:model.live="search" placeholder="Search users...">
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name & Position</th>
                    <th>Email</th>
                    <th>Whatsapp</th>
                    <th>Role</th>
                    <th>Division</th>
                    <th>Manager</th>
                    <th>Status</th>
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
                            <div style="flex:1;">
                                <div style="font-weight:500;">{{ $user->name }}</div>
                                <div style="font-size:11px;color:var(--color-text-secondary);">{{ $user->position_name ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--color-text-secondary);">{{ $user->email }}</td>
                    <td style="color:var(--color-text-secondary);">{{ $user->whatsapp_number ?? '—' }}</td>
                    <td>
                        @php $roleColors = ['director'=>'badge-revision','manager'=>'badge-review','staff'=>'badge-new']; @endphp
                        <span class="badge {{ $roleColors[$user->roles->first()?->name ?? 'staff'] ?? 'badge-new' }}">
                            {{ ucfirst($user->roles->first()?->name ?? '—') }}
                        </span>
                    </td>
                    <td style="color:var(--color-text-secondary);">{{ $user->division?->name ?? '—' }}</td>
                    <td style="color:var(--color-text-secondary);">
                        @if($user->hasRole('staff'))
                            {{ $user->managers->pluck('name')->implode(', ') ?: '—' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <button wire:click="toggleActive({{ $user->id }})" 
                                class="badge {{ $user->is_active ? 'badge-completed' : 'badge-revision' }}"
                                style="border:none; cursor:pointer;">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-ghost btn-sm" wire:click="edit({{ $user->id }})">Edit</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--color-neutral);padding:40px;">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showForm)
    <div class="modal-backdrop">
        <div class="modal" id="modal-staff-form">
            <div class="modal-header">
                <h2 style="font-size:16px;font-weight:600;">{{ $editingId ? 'Edit User' : 'Add User' }}</h2>
                <button class="btn btn-ghost btn-sm" wire:click="$set('showForm', false)">✕</button>
            </div>
            <div class="modal-body">
                <div class="responsive-grid grid-cols-1 md:grid-cols-2" style="gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-input" wire:model="name">
                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position Name</label>
                        <input type="text" class="form-input" wire:model="positionName" placeholder="e.g. Senior Accountant">
                        @error('positionName')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-input" wire:model="email">
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">No Whatsapp</label>
                    <div style="display:flex;">
                        <span style="background:var(--color-bg); border:1px solid var(--color-border); border-right:none; padding:10px 12px; border-radius:var(--radius-md) 0 0 var(--radius-md); font-size:14px; color:var(--color-neutral); font-weight:600; display:flex; align-items:center;">+62</span>
                        <input type="text" class="form-input" style="border-top-left-radius:0; border-bottom-left-radius:0;" wire:model="whatsappNumber" placeholder="8123456789">
                    </div>
                    @error('whatsappNumber')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password {{ $editingId ? '(leave blank to keep)' : '*' }}</label>
                    <input type="password" class="form-input" wire:model="password">
                    @error('password')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="responsive-grid grid-cols-1 md:grid-cols-2" style="gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select class="form-select" wire:model.live="role">
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

                @if($role === 'staff')
                <div class="form-group">
                    <label class="form-label">Assigned Managers</label>
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; background: var(--color-bg); padding: 12px; border: 1px solid var(--color-border); border-radius: var(--radius-md); max-height: 150px; overflow-y: auto;">
                        @foreach($managers as $m)
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                                <input type="checkbox" wire:model="managerIds" value="{{ $m->id }}">
                                <span>{{ $m->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('managerIds')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                @endif

                <div class="form-group">
                    <label class="flex items-center gap-2" style="cursor:pointer;">
                        <input type="checkbox" wire:model="isActive">
                        <span class="form-label" style="margin:0;">User is Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" id="btn-save-staff" wire:click="save">Save User</button>
            </div>
        </div>
    </div>
    @endif
</div>
