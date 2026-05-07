<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Recurring Tasks</h1>
            <p class="text-secondary text-sm mt-1">Manage scheduled tasks and automation rules.</p>
        </div>
        @if(auth()->user()->isManager() || auth()->user()->isDirector())
        <button class="btn btn-primary btn-md" id="btn-add-recurring" wire:click="$set('showForm', true)">+ Add Schedule</button>
        @endif
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Reference / Title</th>
                    <th>Client</th>
                    <th>Assigned PIC</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    @if(auth()->user()->isManager() || auth()->user()->isDirector())
                    <th style="width:100px;"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($recurringTasks as $task)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $task->taskReference->title }}</div>
                    </td>
                    <td>{{ $task->client?->name ?? 'Internal' }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:24px;height:24px;border-radius:9999px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;">
                                {{ strtoupper(substr($task->pic?->name ?? 'U', 0, 2)) }}
                            </div>
                            <span>{{ $task->pic?->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-new">{{ $task->frequency }}</span>
                        @if($task->frequency === 'Monthly' && $task->day_of_month)
                            <span class="text-xs text-muted ml-1">on day {{ $task->day_of_month }}</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn {{ $task->is_active ? 'btn-ghost' : 'btn-danger' }} btn-sm"
                                wire:click="toggle({{ $task->id }})">
                            {{ $task->is_active ? 'Active' : 'Paused' }}
                        </button>
                    </td>
                    @if(auth()->user()->isManager() || auth()->user()->isDirector())
                    <td>
                        <button class="btn btn-ghost btn-sm" wire:click="$set('editingId', {{ $task->id }}); $set('showForm', true)">Edit</button>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="{{ (auth()->user()->isManager() || auth()->user()->isDirector()) ? '6' : '5' }}" style="text-align:center;color:var(--color-neutral);padding:40px;">No recurring tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showForm)
    <div class="modal-backdrop" wire:click.self="$set('showForm', false)">
        <div class="modal" id="modal-recurring-form">
            <div class="modal-header">
                <h2 style="font-size:16px;font-weight:600;">{{ $editingId ? 'Edit Schedule' : 'New Schedule' }}</h2>
                <button class="btn btn-ghost btn-sm" wire:click="$set('showForm', false)">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Task Reference *</label>
                    <select class="form-select" wire:model="refId">
                        <option value="">— Select reference —</option>
                        @foreach($references as $ref)
                            <option value="{{ $ref->id }}">{{ $ref->title }}</option>
                        @endforeach
                    </select>
                    @error('refId')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Client</label>
                    <select class="form-select" wire:model="clientId">
                        <option value="">— Internal Task —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assign to PIC *</label>
                    <select class="form-select" wire:model="picId">
                        <option value="">— Select staff —</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('picId')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Frequency *</label>
                        <select class="form-select" wire:model.live="frequency">
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                    @if($frequency === 'Monthly')
                    <div class="form-group">
                        <label class="form-label">Day of Month *</label>
                        <input type="number" class="form-input" wire:model="dayOfMonth" min="1" max="31">
                        @error('dayOfMonth')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    @endif
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" id="is_active" wire:model="isActive" style="width:16px;height:16px;accent-color:var(--color-primary);">
                    <label for="is_active" style="font-size:14px;cursor:pointer;">Schedule Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-md" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn btn-primary btn-md" id="btn-save-recurring" wire:click="save">Save Schedule</button>
            </div>
        </div>
    </div>
    @endif
</div>
