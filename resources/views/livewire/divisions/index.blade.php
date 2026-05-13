<div>
    <div class="flex flex-responsive items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Divisions</h1>
            <p class="text-secondary text-sm mt-1">Manage company divisions and departments.</p>
        </div>
        <button class="btn btn-primary btn-md w-full-mobile" wire:click="$set('showForm', true)">+ Add Division</button>
    </div>

    <div style="margin-bottom:16px;">
        <input type="text" class="form-input w-full-mobile" style="max-width:320px;" wire:model.live="search" placeholder="Search divisions...">
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>User Count</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($divisions as $div)
                <tr>
                    <td style="font-weight:500;">{{ $div->name }}</td>
                    <td style="color:var(--color-text-secondary);">{{ $div->users_count }} users</td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-ghost btn-sm" wire:click="edit({{ $div->id }})">Edit</button>
                            @if($div->users_count === 0)
                                <button class="btn btn-danger btn-sm" wire:click="delete({{ $div->id }})" wire:confirm="Delete this division?">Delete</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:var(--color-neutral);padding:40px;">No divisions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showForm)
    <div class="modal-backdrop">
        <div class="modal" style="max-width:400px;">
            <div class="modal-header">
                <h2 style="font-size:16px;font-weight:600;">{{ $editingId ? 'Edit Division' : 'Add Division' }}</h2>
                <button class="btn btn-ghost btn-sm" wire:click="$set('showForm', false)">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Division Name *</label>
                    <input type="text" class="form-input" wire:model="name" placeholder="e.g. Accounting">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" wire:click="save">Save Division</button>
            </div>
        </div>
    </div>
    @endif
</div>
