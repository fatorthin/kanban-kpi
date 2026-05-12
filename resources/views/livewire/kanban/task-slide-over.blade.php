<div>
    @if($isOpen && $task)
    <div class="slideover-backdrop"></div>
    <div class="slideover-panel" id="slideover-task-{{ $task->id }}">
        <div class="slideover-header">
            <div style="flex:1;min-width:0;">
                <h2 style="font-size:16px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $task->title }}
                </h2>
                @php $badgeMap=['New'=>'badge-new','In_Progress'=>'badge-progress','Review'=>'badge-review','Revision'=>'badge-revision','Completed'=>'badge-completed']; @endphp
                <span class="badge {{ $badgeMap[$task->status] ?? '' }}" style="margin-top:4px;">
                    {{ str_replace('_', ' ', $task->status) }}
                </span>
            </div>
            <button class="btn btn-ghost btn-sm" wire:click="close">✕</button>
        </div>

        <div class="slideover-body">
            {{-- Meta Info --}}
            <div class="responsive-grid grid-cols-2" style="gap:16px;margin-bottom:24px;">
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--color-neutral);letter-spacing:0.06em;margin-bottom:4px;">Client</div>
                    <div style="font-size:14px;font-weight:500;">{{ $task->client?->name ?? 'Internal' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--color-neutral);letter-spacing:0.06em;margin-bottom:4px;">Type</div>
                    <div style="font-size:14px;font-weight:500;">{{ $task->task_type }}</div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--color-neutral);letter-spacing:0.06em;margin-bottom:4px;">PIC</div>
                    <div style="font-size:14px;font-weight:500;">{{ $task->pic?->name ?? '—' }}</div>
                    @if($task->is_takeover && $task->originalPic)
                    <div style="font-size:11px;color:var(--color-error);margin-top:2px;">
                        Originally: {{ $task->originalPic->name }}
                    </div>
                    @endif
                </div>
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--color-neutral);letter-spacing:0.06em;margin-bottom:4px;">Manager</div>
                    <div style="font-size:14px;font-weight:500;">{{ $task->manager?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--color-neutral);letter-spacing:0.06em;margin-bottom:4px;">Deadline</div>
                    <div style="font-size:14px;font-weight:500;{{ $task->deadline->isPast() && $task->status !== 'Completed' ? 'color:var(--color-error);' : '' }}">
                        {{ $task->deadline->format('d M Y, H:i') }}
                    </div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:var(--color-neutral);letter-spacing:0.06em;margin-bottom:4px;">Points</div>
                    <div style="font-size:14px;font-weight:500;">
                        {{ $task->difficulty_points }} pts
                        @if($task->is_takeover)
                            <span style="font-size:11px;color:var(--color-success);">(+20% bonus)</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($task->revision_count > 0)
            <div style="background:color-mix(in srgb,var(--color-error) 8%,transparent);border:1px solid color-mix(in srgb,var(--color-error) 20%,transparent);border-radius:var(--radius-lg);padding:10px 14px;margin-bottom:20px;font-size:13px;color:var(--color-error);">
                ⚠ Revised {{ $task->revision_count }}x — Quality penalty: -{{ $task->revision_count * 15 }} points
            </div>
            @endif

            {{-- SOP / Description --}}
            @if($task->description)
            <div style="margin-bottom:24px;">
                <h4 style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-neutral);margin-bottom:8px;">SOP / Description</h4>
                <div style="font-size:14px;line-height:1.7;color:var(--color-text);background:var(--color-bg);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:14px;">
                    {!! nl2br(e($task->description)) !!}
                </div>
            </div>
            @endif

            {{-- Chat --}}
            <div>
                <h4 style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-neutral);margin-bottom:12px;">
                    Chat ({{ $task->messages->count() }})
                </h4>
                <div class="chat-messages" id="chat-messages-{{ $task->id }}">
                    @forelse($task->messages as $msg)
                    @php $isOwn = $msg->user_id === auth()->id(); @endphp
                    <div class="chat-bubble {{ $isOwn ? 'own' : '' }}">
                        <div class="chat-avatar">{{ strtoupper(substr($msg->user?->name ?? 'U', 0, 2)) }}</div>
                        <div class="chat-content">
                            @if(!$isOwn)
                            <div class="chat-sender">{{ $msg->user?->name }}</div>
                            @endif
                            <div class="chat-text">{{ $msg->message }}</div>
                            <div style="font-size:11px;color:var(--color-neutral);margin-top:3px;{{ $isOwn ? 'text-align:right;' : '' }}">
                                {{ $msg->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;font-size:13px;color:var(--color-neutral);padding:16px 0;">No messages yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Chat input --}}
        <div class="slideover-footer">
            <div style="display:flex;gap:8px;">
                <input type="text" class="form-input" wire:model="newMessage"
                       placeholder="Type a message..." id="chat-input-{{ $task->id }}"
                       wire:keydown.enter="sendMessage">
                <button class="btn btn-primary btn-md" wire:click="sendMessage" id="btn-send-msg">Send</button>
            </div>
        </div>
    </div>
    @endif
</div>
