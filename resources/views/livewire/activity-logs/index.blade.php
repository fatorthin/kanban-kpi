<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Activity Logs</h1>
            <p class="text-secondary text-sm mt-1">Audit trail of system events and task takeovers.</p>
        </div>
    </div>

    <div style="margin-bottom:16px;">
        <input type="text" class="form-input" style="max-width:320px;" wire:model.live="search" placeholder="Search logs...">
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Oleh</th>
                    <th>Aktivitas</th>
                    <th>Objek</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size:13px;color:var(--color-text-secondary);white-space:nowrap;">
                        <div class="font-semibold text-text">{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-xs">{{ $log->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:32px; height:32px; border-radius:var(--radius-full); background:var(--color-bg); border:1px solid var(--color-border); display:flex; align-items:center; justify-content:center; text-align:center; flex-shrink:0;">
                                <span style="font-size:11px; font-weight:700; color:var(--color-neutral);">
                                    {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div style="font-weight:600; font-size:13px; color:var(--color-text); line-height:1.2;">
                                    {{ $log->causer?->name ?? 'Sistem' }}
                                </div>
                                <div style="font-size:11px; color:var(--color-text-secondary); margin-top:2px;">
                                    {{ $log->causer?->division?->name ?? ( $log->causer?->roles->first()?->name ?? 'Automated' ) }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-sm font-medium text-text">
                            {{ $log->description }}
                        </div>
                    </td>
                    <td style="font-size:13px;color:var(--color-text-secondary);">
                        <span class="text-xs px-2 py-0.5 bg-bg border border-border rounded text-neutral font-medium">
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        </span>
                    </td>
                    <td>
                        @if($log->properties->count() > 0)
                            <button class="btn btn-ghost btn-sm" 
                                    style="height:24px; font-size:11px;"
                                    title="{{ json_encode($log->properties) }}">
                                Detail JSON
                            </button>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:var(--color-neutral);padding:40px;">Tidak ada log aktivitas ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $logs->links(data: ['scrollTo' => false]) }}
    </div>
</div>
