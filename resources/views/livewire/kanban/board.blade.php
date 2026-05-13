<div wire:poll.5s.visible>
    {{-- ================================================================ Header --}}
    <div class="flex flex-responsive items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Kanban Board</h1>
            <p class="text-secondary text-sm mt-1">Drag tasks across columns to update their status.</p>
        </div>
        <div class="flex gap-2">
            @if (auth()->user()->isManager() || auth()->user()->isDirector())
                <button class="btn btn-primary btn-md" id="btn-create-task" wire:click="$set('showForm', true)">
                    + New Task
                </button>
            @endif
        </div>
    </div>

    {{-- ================================================================ Filters --}}
    <div class="flex-responsive" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
        {{-- Search bar --}}
        <div class="w-full-mobile" style="position:relative;flex:1;min-width:200px;max-width:300px;">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--color-neutral);">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </span>
            <input type="text" class="form-input w-full-mobile" placeholder="Search tasks, clients, or PIC..." 
                   style="padding-left:34px; padding-right:34px;"
                   wire:model.live.debounce.300ms="search">
            
            @if($search)
            <button style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--color-neutral);background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;"
                    wire:click="resetSearch"
                    title="Clear search">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
            @endif
        </div>

        <select class="form-select w-full-mobile" style="width:auto;" wire:model.live="filterType">
            <option value="">All Types</option>
            <option value="Client">Client</option>
            <option value="Internal">Internal</option>
        </select>
        @if (auth()->user()->isManager() || auth()->user()->isDirector())
            <select class="form-select w-full-mobile" style="width:auto;" wire:model.live="filterPic">
                <option value="">All Staff</option>
                @foreach ($staff as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}{{ $s->id === auth()->id() ? ' (Me)' : '' }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- ================================================================ Board --}}
    @php
        $columnColors = [
            'New' => '#4F46E5',
            'In_Progress' => '#C2410C',
            'Review' => '#92400E',
            'Revision' => '#B91C1C',
            'Completed' => '#065F46',
        ];
        $columnLabels = [
            'New' => 'New',
            'In_Progress' => 'In Progress',
            'Review' => 'Review',
            'Revision' => 'Revision',
            'Completed' => 'Completed',
        ];
    @endphp

    {{-- Load Sortable.js for Drag & Drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <div class="kanban-board" x-data="{
        initSortable() {
            document.querySelectorAll('.kanban-cards').forEach(el => {
                if (el.sortableInstance) el.sortableInstance.destroy();
                el.sortableInstance = new Sortable(el, {
                    group: 'kanban',
                    animation: 150,
                    ghostClass: 'kanban-card-ghost',
                    onEnd: (evt) => {
                        // If dropped in the same place without reordering
                        if (evt.from === evt.to && evt.oldIndex === evt.newIndex) return;
    
                        let taskId = evt.item.dataset.taskId;
                        let newStatus = evt.to.dataset.status;
    
                        // Get array of IDs in the target column in the new sorted order
                        let newOrder = Array.from(evt.to.children)
                            .filter(child => child.dataset && child.dataset.taskId)
                            .map(child => child.dataset.taskId);
    
                        if (taskId && newStatus) {
                            $wire.updateTaskOrder(taskId, newStatus, newOrder);
                        }
                    }
                });
            });
        }
    }" x-init="initSortable();
    Livewire.hook('morph.updated', () => {
        initSortable();
    });">
        @foreach ($statuses as $status)
            @php $columnTasks = $tasks[$status] ?? collect(); @endphp
            <div class="kanban-column" id="col-{{ strtolower(str_replace('_', '-', $status)) }}">
                <div class="kanban-column-header">
                    <span class="kanban-column-title" style="color:{{ $columnColors[$status] }};">
                        {{ $columnLabels[$status] }}
                    </span>
                    <span class="kanban-column-count">{{ $totalCounts[$status] }}</span>
                </div>
                <div class="kanban-cards" data-status="{{ $status }}">
                    @forelse($columnTasks as $task)
                        <div class="kanban-card {{ $task->is_takeover ? 'is-takeover' : '' }}" id="task-card-{{ $task->id }}" data-task-id="{{ $task->id }}" wire:click="$dispatch('openTask', { taskId: {{ $task->id }} })">

                            @if ($task->is_takeover || $task->unread_messages_count > 0)
                                <div style="display:flex;align-items:center;gap:4px;margin-bottom:6px;">
                                    @if ($task->unread_messages_count > 0)
                                        <span class="badge" style="background:#EFF6FF;color:#1D4ED8;">
                                            {{ $task->unread_messages_count }} pesan baru
                                        </span>
                                    @endif

                                    @if ($task->is_takeover)
                                        <span class="badge badge-takeover">🔄 Takeover</span>
                                    @endif
                                </div>
                            @endif

                            <div class="kanban-card-title">{{ $task->title }}</div>
                            <div class="responsive-grid grid-cols-1" style="gap:4px;">
                                @if ($task->client)
                                    <div class="kanban-card-meta-item">
                                        <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                        </svg>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $task->client->name }}</span>
                                    </div>
                                @endif
                                <div class="kanban-card-meta-item">
                                    <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    {{ $task->pic?->name ?? '—' }}
                                </div>
                                <div class="kanban-card-meta-item">
                                    <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    <span style="{{ $task->deadline->isPast() && $task->status !== 'Completed' ? 'color:var(--color-error);font-weight:500;' : '' }}">
                                        {{ $task->deadline->format('d M Y') }}
                                    </span>
                                </div>
                                <div style="display:flex;gap:12px;">
                                    <div class="kanban-card-meta-item">
                                        <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                        </svg>
                                        {{ $task->difficulty_points }} pts
                                    </div>
                                    <div class="kanban-card-meta-item">
                                        <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        {{ $task->revision_count }}x rev
                                    </div>
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div class="kanban-card-actions" wire:click.stop>
                                {{-- Takeover button --}}
                                @if ($task->isTakeoverEligible() && auth()->id() !== $task->pic_id)
                                    <button class="btn btn-danger btn-sm" id="btn-takeover-{{ $task->id }}" wire:click="takeoverTask({{ $task->id }})">
                                        🔄 Take Over
                                    </button>
                                @endif

                                {{-- Move buttons based on role --}}
                                @if (auth()->user()->isStaff() && $task->pic_id === auth()->id())
                                    @if ($task->status === 'New')
                                        <button class="btn btn-secondary btn-sm" wire:click="moveTask({{ $task->id }}, 'In_Progress')">→ Start</button>
                                    @elseif($task->status === 'In_Progress')
                                        <button class="btn btn-secondary btn-sm" wire:click="moveTask({{ $task->id }}, 'Review')">→ Review</button>
                                    @elseif($task->status === 'Revision')
                                        <button class="btn btn-secondary btn-sm" wire:click="moveTask({{ $task->id }}, 'In_Progress')">↺ Rework</button>
                                    @endif
                                @endif

                                @if ((auth()->user()->isManager() || auth()->user()->isDirector()) && in_array($task->status, ['Review']))
                                    <button class="btn btn-secondary btn-sm" wire:click="moveTask({{ $task->id }}, 'Completed')">✓ Approve</button>
                                    <button class="btn btn-danger btn-sm" wire:click="moveTask({{ $task->id }}, 'Revision')">✗ Revision</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:32px 8px;color:var(--color-neutral);font-size:13px;">
                            No tasks
                        </div>
                    @endforelse

                    @if($hasMore[$status])
                    <div style="padding:12px;text-align:center;">
                        <button class="btn btn-ghost btn-sm w-full" 
                                style="font-size:11px;color:var(--color-neutral);border:1px dashed var(--color-border);"
                                wire:click="loadMore('{{ $status }}')">
                            Load More...
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ================================================================ Task Slide-over --}}
    @livewire('kanban.task-slide-over')

    {{-- ================================================================ Create Task Modal --}}
    @if ($showForm)
        <div class="modal-backdrop" wire:click.self="closeForm">
            <div class="modal" id="modal-create-task">
                <div class="modal-header">
                    <h2 style="font-size:16px;font-weight:600;">{{ $editingTaskId ? 'Edit Task' : 'Create New Task' }}</h2>
                    <button class="btn btn-ghost btn-sm" wire:click="closeForm">✕</button>
                </div>
                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                    {{-- Reference selector --}}
                    <div class="form-group" x-data="{ open: false }" @click.away="open = false">
                        <label class="form-label">Task Reference (optional)</label>
                        <div style="position:relative;">
                            {{-- Dropdown Trigger --}}
                            <div @click="open = !open; if(open) setTimeout(() => $refs.refSearchInput.focus(), 100)" 
                                 class="form-select" 
                                 style="cursor:pointer; display:flex; justify-content:space-between; align-items:center; background:var(--color-surface);">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:90%;">
                                    @php $selectedRef = $references->firstWhere('id', $formRefId); @endphp
                                    {{ $selectedRef ? $selectedRef->title : '— Select from library —' }}
                                </span>
                                <svg style="width:14px; height:14px; color:var(--color-neutral);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Dropdown Content --}}
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 style="position:absolute; top:100%; left:0; right:0; z-index:100; margin-top:4px; background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); box-shadow:var(--shadow-dropdown); padding:8px; display:none;"
                                 :style="open ? 'display:block' : 'display:none'">
                                
                                {{-- Search Box --}}
                                <div style="margin-bottom:8px; position:relative;">
                                    <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:var(--color-neutral);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input type="text" 
                                           x-ref="refSearchInput"
                                           class="form-input" 
                                           placeholder="Search reference..." 
                                           wire:model.live.debounce.300ms="referenceSearch"
                                           style="height:34px; font-size:13px; padding-left:32px;">
                                </div>

                                {{-- Results List --}}
                                <div style="max-height:200px; overflow-y:auto; margin:0 -4px; padding:0 4px;">
                                    <div @click="$wire.set('formRefId', null); open = false;" 
                                         class="dropdown-item" 
                                         style="padding:8px 12px; font-size:13px; cursor:pointer; border-radius:var(--radius-sm); color:var(--color-text-secondary);">
                                        — Select from library —
                                    </div>
                                    @foreach ($references as $ref)
                                        <div @click="$wire.loadReference({{ $ref->id }}); $wire.set('formRefId', {{ $ref->id }}); open = false;" 
                                             class="dropdown-item {{ $formRefId == $ref->id ? 'is-selected' : '' }}" 
                                             style="padding:8px 12px; font-size:13px; cursor:pointer; border-radius:var(--radius-sm); display:flex; justify-content:space-between; align-items:center;">
                                            <span style="font-weight:{{ $formRefId == $ref->id ? '600' : '400' }}; color:var(--color-text);">
                                                {{ $ref->title }}
                                            </span>
                                        </div>
                                    @endforeach

                                    @if($references->isEmpty())
                                        <div style="padding:16px; text-align:center; font-size:12px; color:var(--color-neutral);">
                                            No references found
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-input" wire:model="formTitle" placeholder="Task title">
                        @error('formTitle')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" wire:model="formDesc" placeholder="SOP or description..."></textarea>
                    </div>
                    <div class="responsive-grid grid-cols-1 md:grid-cols-2" style="gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Type *</label>
                            <select class="form-select" wire:model.live="formType">
                                <option value="Client">Client</option>
                                <option value="Internal">Internal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Difficulty Points *</label>
                            <input type="number" class="form-input" wire:model="formPoints" min="0">
                            @error('formPoints')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    @if($formType === 'Client')
                    <div class="form-group" x-data="{ open: false }" @click.away="open = false">
                        <label class="form-label">Client</label>
                        <div style="position:relative;">
                            {{-- Dropdown Trigger --}}
                            <div @click="open = !open; if(open) setTimeout(() => $refs.clientSearchInput.focus(), 100)" 
                                 class="form-select" 
                                 style="cursor:pointer; display:flex; justify-content:space-between; align-items:center; background:var(--color-surface);">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:90%;">
                                    @php $selectedClient = $clients->firstWhere('id', $formClientId); @endphp
                                    {{ $selectedClient ? $selectedClient->name : '— Select Client —' }}
                                </span>
                                <svg style="width:14px; height:14px; color:var(--color-neutral);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Dropdown Content --}}
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 style="position:absolute; top:100%; left:0; right:0; z-index:100; margin-top:4px; background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); box-shadow:var(--shadow-dropdown); padding:8px; display:none;"
                                 :style="open ? 'display:block' : 'display:none'">
                                
                                {{-- Search Box inside dropdown --}}
                                <div style="margin-bottom:8px; position:relative;">
                                    <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:var(--color-neutral);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input type="text" 
                                           x-ref="clientSearchInput"
                                           class="form-input" 
                                           placeholder="Search client..." 
                                           wire:model.live.debounce.300ms="clientSearch"
                                           style="height:34px; font-size:13px; padding-left:32px;">
                                </div>

                                {{-- Results List --}}
                                <div style="max-height:200px; overflow-y:auto; margin:0 -4px; padding:0 4px;">
                                    <div @click="$wire.set('formClientId', null); open = false;" 
                                         class="dropdown-item" 
                                         style="padding:8px 12px; font-size:13px; cursor:pointer; border-radius:var(--radius-sm); color:var(--color-text-secondary);">
                                        — Select Client —
                                    </div>
                                    @foreach ($clients as $client)
                                        <div @click="$wire.set('formClientId', {{ $client->id }}); open = false;" 
                                             class="dropdown-item {{ $formClientId == $client->id ? 'is-selected' : '' }}" 
                                             style="padding:8px 12px; font-size:13px; cursor:pointer; border-radius:var(--radius-sm); display:flex; justify-content:space-between; align-items:center;">
                                            <span style="font-weight:{{ $formClientId == $client->id ? '600' : '400' }}; color:var(--color-text);">
                                                {{ $client->name }}
                                            </span>
                                            <span style="font-size:11px; color:var(--color-neutral);">{{ $client->code }}</span>
                                        </div>
                                    @endforeach

                                    @if($clients->isEmpty())
                                        <div style="padding:16px; text-align:center; font-size:12px; color:var(--color-neutral);">
                                            No clients found
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @error('formClientId') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Assign to PIC *</label>
                        <select class="form-select" wire:model="formPicId">
                            <option value="">— Select staff —</option>
                            @foreach ($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}{{ $s->id === auth()->id() ? ' (Me)' : '' }}</option>
                            @endforeach
                        </select>
                        @error('formPicId')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deadline *</label>
                        <input type="datetime-local" class="form-input" wire:model="formDeadline">
                        @error('formDeadline')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-md" wire:click="closeForm">Cancel</button>
                    <button class="btn btn-primary btn-md" id="btn-save-task" wire:click="saveTask">Save Task</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ Takeover Modal --}}
    @if ($showTakeoverModal)
        <div class="modal-backdrop" wire:click.self="$set('showTakeoverModal', false)">
            <div class="modal" style="max-width:400px;">
                <div class="modal-header">
                    <h2 style="font-size:16px;font-weight:600;">Takeover & Reassign</h2>
                    <button class="btn btn-ghost btn-sm" wire:click="$set('showTakeoverModal', false)">✕</button>
                </div>
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--color-neutral);margin-bottom:16px;">
                        This task has breached its deadline. Select a staff member to reassign it to.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Assign to PIC *</label>
                        <select class="form-select" wire:model="takeoverPicId">
                            <option value="">— Select recipient —</option>
                            <optgroup label="Action">
                                <option value="{{ auth()->id() }}">Take over for myself</option>
                            </optgroup>
                            @if($staff->count() > 0)
                            <optgroup label="Delegate to Staff">
                                @foreach ($staff as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        @error('takeoverPicId') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-md" wire:click="$set('showTakeoverModal', false)">Cancel</button>
                    <button class="btn btn-primary btn-md" wire:click="confirmTakeover">Confirm Takeover</button>
                </div>
            </div>
        </div>
    @endif
</div>
