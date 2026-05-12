<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="text-secondary text-sm mt-1">
                Welcome back, {{ auth()->user()->name }}.
                @if(auth()->user()->isStaff())
                    Here's your personal performance overview.
                @else
                    Here's your team's workload overview.
                @endif
            </p>
        </div>
    </div>

    {{-- ============================================== Stats Grid --}}
    <div class="stats-grid mb-6" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-value" style="color:var(--color-primary);">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Tasks</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#C2410C;">{{ $stats['in_progress'] }}</div>
            <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#92400E;">{{ $stats['review'] }}</div>
            <div class="stat-label">In Review</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--color-success);">{{ $stats['completed'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--color-error);">{{ $stats['takeovers'] }}</div>
            <div class="stat-label">Takeovers</div>
        </div>
    </div>

    <div class="responsive-grid grid-cols-1 md:grid-cols-2 md:gap-6">

        {{-- ========================================= KPI Summary Card --}}
        <div class="card">
            <div class="card-header">
                <h3 style="font-size:15px;font-weight:600;">KPI Summary</h3>
                @if($latestReport)
                    <span style="font-size:12px;color:var(--color-neutral);">
                        {{ \Carbon\Carbon::createFromDate($latestReport->year, $latestReport->month)->format('M Y') }}
                    </span>
                @endif
            </div>
            <div class="card-body">
                @if($latestReport)
                    <div style="margin-bottom:20px;text-align:center;">
                        <div style="font-size:48px;font-weight:700;letter-spacing:-0.04em;color:var(--color-text);">
                            {{ number_format($latestReport->final_kpi_score, 1) }}
                        </div>
                        <div style="font-size:13px;color:var(--color-text-secondary);margin-top:4px;">NAK Score</div>
                    </div>

                    @foreach(['productivity_score' => 'Productivity (S_prod)', 'quality_score' => 'Quality (S_qual)', 'timeliness_score' => 'Timeliness (S_time)'] as $key => $label)
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                            <span style="color:var(--color-text-secondary);">{{ $label }}</span>
                            <span style="font-weight:500;">{{ number_format($latestReport->$key, 1) }}</span>
                        </div>
                        <div class="kpi-bar-track">
                            <div class="kpi-bar-fill" style="width:{{ min(100, $latestReport->$key) }}%"></div>
                        </div>
                    </div>
                    @endforeach

                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--color-border);display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;color:var(--color-text-secondary);">Estimated Incentive</span>
                        <span style="font-size:15px;font-weight:600;color:var(--color-success);">
                            Rp {{ number_format($latestReport->total_incentive, 0, ',', '.') }}
                        </span>
                    </div>
                @else
                    <div style="text-align:center;padding:32px 0;color:var(--color-neutral);">
                        <svg style="width:40px;height:40px;margin:0 auto 8px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z"/>
                        </svg>
                        <p style="font-size:14px;">No KPI report yet for this month.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================================= Recent Tasks --}}
        <div class="card">
            <div class="card-header">
                <h3 style="font-size:15px;font-weight:600;">Recent Tasks</h3>
                <a href="{{ route('kanban') }}" wire:navigate class="btn btn-ghost btn-sm">View Board →</a>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($recentTasks as $task)
                <div style="padding:14px 24px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;gap:12px;" class="{{ !$loop->last ? '' : 'border-0' }}">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $task->title }}
                        </div>
                        <div style="font-size:12px;color:var(--color-neutral);margin-top:2px;">
                            {{ $task->client?->name ?? 'Internal' }} · {{ $task->pic?->name ?? '—' }}
                        </div>
                    </div>
                    @php
                        $badgeMap = ['New'=>'badge-new','In_Progress'=>'badge-progress','Review'=>'badge-review','Revision'=>'badge-revision','Completed'=>'badge-completed'];
                    @endphp
                    <span class="badge {{ $badgeMap[$task->status] ?? '' }}">
                        {{ str_replace('_', ' ', $task->status) }}
                    </span>
                </div>
                @empty
                <div style="padding:32px;text-align:center;color:var(--color-neutral);font-size:14px;">
                    No tasks found. Head to the Kanban board to get started.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
