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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Whatsapp</th>
                    <th>Role</th>
                    <th>Division</th>
                    <th>Manager</th>
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
                    <td style="color:var(--color-text-secondary);">{{ $user->whatsapp_number ?? '—' }}</td>
                    <td>
                        @php $roleColors = ['director'=>'badge-revision','manager'=>'badge-review','staff'=>'badge-new']; @endphp
                        <span class="badge {{ $roleColors[$user->roles->first()?->name ?? 'staff'] ?? 'badge-new' }}">
                            {{ ucfirst($user->roles->first()?->name ?? '—') }}
                        </span>
                    </td>
                    <td style="color:var(--color-text-secondary);">{{ $user->division?->name ?? '—' }}</td>
                    <td style="color:var(--color-text-secondary);">
                        @if($user->roles->first()?->name === 'staff')
                            {{ $user->manager?->name ?? '—' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-ghost btn-sm" wire:click="edit({{ $user->id }})">Edit</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--color-neutral);padding:40px;">No users found.</td></tr>
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
                    <label class="form-label">No Whatsapp</label>
                    <input type="text" class="form-input" wire:model="whatsappNumber" placeholder="e.g. 08123456789">
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
                    <label class="form-label">Assigned Manager</label>
                    <select class="form-select" wire:model="managerId">
                        <option value="">— No Manager —</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                    @error('managerId')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" id="btn-save-staff" wire:click="save">Save User</button>
            </div>
        </div>
    </div>
    @endif
</div>
