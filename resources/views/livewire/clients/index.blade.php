<div>
    <div class="flex flex-responsive items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Client Database</h1>
            <p class="text-secondary text-sm mt-1">Manage clients and their service grades.</p>
        </div>
        <button class="btn btn-primary btn-md w-full-mobile" id="btn-add-client" wire:click="$set('showForm', true)">+ Add Client</button>
    </div>

    <div style="margin-bottom:16px;">
        <input type="text" class="form-input w-full-mobile" style="max-width:320px;" wire:model.live="search" placeholder="Search clients...">
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Code</th>
                    <th>Grade</th>
                    <th>Tasks</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td style="font-weight:500;">{{ $client->name }}</td>
                    <td style="font-family:monospace;font-size:13px;">{{ $client->code }}</td>
                    <td>
                        <span class="badge badge-grade-{{ strtolower($client->grade) }}">Grade {{ $client->grade }}</span>
                    </td>
                    <td style="color:var(--color-text-secondary);">{{ $client->tasks_count }} tasks</td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-ghost btn-sm" wire:click="edit({{ $client->id }})">Edit</button>
                            <button class="btn btn-danger btn-sm" wire:click="delete({{ $client->id }})" wire:confirm="Delete this client?">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:var(--color-neutral);padding:40px;">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showForm)
    <div class="modal-backdrop">
        <div class="modal" id="modal-client-form">
            <div class="modal-header">
                <h2 style="font-size:16px;font-weight:600;">{{ $editingId ? 'Edit Client' : 'New Client' }}</h2>
                <button class="btn btn-ghost btn-sm" wire:click="$set('showForm', false)">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Client Name *</label>
                    <input type="text" class="form-input" wire:model="name">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Client Code *</label>
                    <input type="text" class="form-input" wire:model="code" placeholder="e.g. PT-ABC">
                    @error('code')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Grade *</label>
                    <select class="form-select" wire:model="grade">
                        @foreach($grades as $g)
                            <option value="{{ $g }}">Grade {{ $g }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" id="btn-save-client" wire:click="save">Save</button>
            </div>
        </div>
    </div>
    @endif
</div>
