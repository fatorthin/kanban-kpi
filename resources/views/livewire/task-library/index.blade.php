<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Task Library</h1>
            <p class="text-secondary text-sm mt-1">Manage your standard task templates and SOP references.</p>
        </div>
        <button class="btn btn-primary btn-md" id="btn-add-task-ref" wire:click="$set('showForm', true)">+ Add Reference</button>
    </div>

    {{-- Search --}}
    <div style="margin-bottom:16px;">
        <input type="text" class="form-input" style="max-width:320px;" wire:model.live="search" placeholder="Search task references...">
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Default Points</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($refs as $ref)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $ref->title }}</div>
                        @if($ref->description)
                        <div class="text-xs text-muted" style="margin-top:2px;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $ref->description }}
                        </div>
                        @endif
                    </td>
                    <td><span class="badge {{ $ref->task_type === 'Client' ? 'badge-new' : 'badge-progress' }}">{{ $ref->task_type }}</span></td>
                    <td><strong>{{ $ref->default_difficulty_points }}</strong> pts</td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-ghost btn-sm" wire:click="edit({{ $ref->id }})">Edit</button>
                            <button class="btn btn-danger btn-sm" wire:click="delete({{ $ref->id }})" wire:confirm="Delete this reference?">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--color-neutral);padding:40px;">No task references found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Form Modal --}}
    @if($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal" id="modal-task-ref-form">
            <div class="modal-header">
                <h2 style="font-size:16px;font-weight:600;">{{ $editingId ? 'Edit Reference' : 'New Task Reference' }}</h2>
                <button class="btn btn-ghost btn-sm" wire:click="$set('showForm', false)">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-input" wire:model="title" placeholder="e.g. Annual Corporate Tax Return">
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">SOP / Description</label>
                    <textarea class="form-textarea" wire:model="desc" placeholder="Standard operating procedures..."></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Type *</label>
                        <select class="form-select" wire:model="type">
                            <option value="Client">Client</option>
                            <option value="Internal">Internal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Points *</label>
                        <input type="number" class="form-input" wire:model="points" min="0">
                        @error('points')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" id="btn-save-ref" wire:click="save">Save</button>
            </div>
        </div>
    </div>
    @endif
</div>
