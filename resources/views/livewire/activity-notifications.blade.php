<div x-data="{ open: false }" class="relative">
    {{-- Bell Icon --}}
    <button @click="open = !open" class="notification-bell-btn" id="btn-notifications">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:20px;height:20px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        @if($unreadCount > 0)
            <span class="notification-badge animate-pulse">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         class="notification-dropdown"
         style="display: none;">

        <div class="notification-header">
            <h3>Notifikasi</h3>
            @if($unreadCount > 0)
                <span style="font-size:10px; font-weight:700; background:var(--color-primary); color:#fff; padding:2px 8px; border-radius:10px;">
                    {{ $unreadCount }} Baru
                </span>
            @else
                <span style="font-size:10px; font-weight:700; background:var(--color-bg); padding:2px 8px; border:1px solid var(--color-border); border-radius:10px;">
                    Terbaca
                </span>
            @endif
        </div>

        {{-- Mark all read button --}}
        @if($unreadCount > 0)
        <div style="padding:8px 16px; border-bottom:1px solid var(--color-border); display:flex; justify-content:flex-end;">
            <button wire:click="markAllRead"
                    id="btn-mark-all-read"
                    style="font-size:12px; font-weight:600; color:var(--color-primary); background:none; border:none; cursor:pointer; padding:4px 8px; border-radius:6px; display:flex; align-items:center; gap:6px; transition:background 0.15s;"
                    onmouseover="this.style.background='color-mix(in srgb,var(--color-primary) 10%,transparent)'"
                    onmouseout="this.style.background='none'">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Tandai sudah dibaca
            </button>
        </div>
        @endif

        <div class="notification-list">
            @forelse($activities as $activity)
                @php
                    $isUnread = is_null(auth()->user()->notifications_read_at)
                        || $activity->created_at->gt(auth()->user()->notifications_read_at);
                @endphp
                <div class="notification-item"
                     style="display:flex; gap:12px; padding:12px 16px; border-bottom:1px solid var(--color-border);
                            {{ $isUnread ? 'background:color-mix(in srgb,var(--color-primary) 5%,transparent);' : '' }}">
                    {{-- Unread dot --}}
                    @if($isUnread)
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--color-primary);flex-shrink:0;margin-top:6px;"></div>
                    @else
                    <div style="width:8px;height:8px;flex-shrink:0;"></div>
                    @endif

                    <div class="notification-item-avatar"
                         style="width:32px; height:32px; border-radius:var(--radius-full); background:var(--color-bg); border:1px solid var(--color-border); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:11px; font-weight:700;">
                        {{ strtoupper(substr($activity->causer?->name ?? 'S', 0, 1)) }}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; color:var(--color-text); line-height:1.4;">
                            {{ $activity->description }}
                        </div>
                        <div style="font-size:11px; color:var(--color-neutral); margin-top:4px;">
                            {{ $activity->created_at->diffForHumans() }} • {{ $activity->causer?->name ?? 'System' }}
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding:40px 20px; text-align:center;">
                    <p style="font-size:13px; color:var(--color-neutral);">Belum ada aktivitas baru.</p>
                </div>
            @endforelse
        </div>

        <div class="notification-footer">
            <a href="{{ route('activity-logs') }}" wire:navigate>Lihat Semua Aktivitas</a>
        </div>
    </div>
</div>
