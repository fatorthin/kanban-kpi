<div>
    <div class="mb-8">
        <h1 class="page-title">Staff Load Monitoring</h1>
        <p class="text-secondary text-sm mt-1">Monitor current workload and point distribution across your team.</p>
    </div>

    <div class="responsive-grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3" style="gap:24px; align-items: stretch;">
        @foreach($staff as $member)
        <div class="card" style="display:flex; flex-direction:column; height:100%; padding:24px;">
            {{-- Profile Header --}}
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg, var(--color-primary), #6366f1); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                </div>
                <div>
                    <div style="font-weight:700; color:var(--color-text); font-size:16px; line-height:1.2;">{{ $member->name }}</div>
                    <div style="font-size:12px; color:var(--color-text-secondary); margin-top:2px;">{{ $member->position_name ?? 'Staff' }}</div>
                </div>
            </div>

            {{-- Load Stats Card --}}
            <div style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:16px; padding:20px; margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div>
                        <div style="font-size:10px; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:600; margin-bottom:4px;">Total Load</div>
                        <div style="display:flex; align-items:baseline; gap:4px;">
                            <span style="font-size:28px; font-weight:800; color:var(--color-text); line-height:1;">{{ $member->load_points }}</span>
                            <span style="font-size:14px; font-weight:600; color:var(--color-text-secondary);">pts</span>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:10px; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:600; margin-bottom:4px;">Active Tasks</div>
                        <div style="font-size:20px; font-weight:700; color:var(--color-text);">{{ $member->task_count }}</div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                @php 
                    $percentage = min(100, ($member->load_points / 150) * 100); // Baseline 150 pts as "Heavy Load"
                    $barColor = 'var(--color-primary)';
                    if($member->load_points >= 100) $barColor = '#f59e0b'; // Warning
                    if($member->load_points >= 150) $barColor = '#ef4444'; // Danger
                @endphp
                <div style="height:10px; background:var(--color-border); border-radius:10px; overflow:hidden; position:relative;">
                    <div style="height:100%; width:{{ $percentage }}%; background:{{ $barColor }}; border-radius:10px; transition:width 0.8s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:10px; color:var(--color-text-secondary); font-weight:500;">
                    <span>Light</span>
                    <span>Moderate</span>
                    <span>Heavy</span>
                </div>
            </div>

            {{-- Task Breakdown --}}
            <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div style="font-size:13px; font-weight:700; color:var(--color-text);">Task Breakdown</div>
                    <span style="font-size:11px; font-weight:600; color:var(--color-primary); background:rgba(99, 102, 241, 0.1); padding:2px 8px; border-radius:999px;">
                        Top 5
                    </span>
                </div>
                
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @forelse($member->assignedTasks->take(5) as $task)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:var(--color-bg); border-radius:10px; border:1px solid transparent; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--color-border)'; this.style.background='var(--color-surface)';" onmouseout="this.style.borderColor='transparent'; this.style.background='var(--color-bg)';">
                            <div style="display:flex; flex-direction:column; min-width:0;">
                                <span style="font-size:12px; font-weight:600; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $task->title }}
                                </span>
                                <span style="font-size:10px; color:var(--color-text-secondary);">{{ $task->client?->name ?? 'Internal' }}</span>
                            </div>
                            <div style="padding-left:12px; flex-shrink:0;">
                                <span style="font-size:11px; font-weight:700; color:var(--color-text);">{{ $task->difficulty_points }}</span>
                                <span style="font-size:9px; font-weight:600; color:var(--color-text-secondary);">pts</span>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:32px 0; border:1px dashed var(--color-border); border-radius:12px; display:flex; flex-direction:column; align-items:center; gap:8px;">
                            <svg style="width:24px; height:24px; color:var(--color-neutral); opacity:0.5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span style="font-size:12px; color:var(--color-neutral); font-weight:500;">No active tasks</span>
                        </div>
                    @endforelse
                    
                    @if($member->assignedTasks->count() > 5)
                        <div style="font-size:11px; color:var(--color-text-secondary); text-align:center; padding-top:8px; border-top:1px dashed var(--color-border); font-weight:500;">
                            + {{ $member->assignedTasks->count() - 5 }} other tasks currently active
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($staff->isEmpty())
    <div style="text-align:center; padding:80px 40px; background:var(--color-surface); border:2px dashed var(--color-border); border-radius:24px;">
        <div style="width:64px; height:64px; background:var(--color-bg); border-radius:9999px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg style="width:32px; height:32px; color:var(--color-neutral);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h3 style="font-size:18px; font-weight:700; color:var(--color-text); margin-bottom:8px;">No Staff Found</h3>
        <p style="font-size:14px; color:var(--color-text-secondary); max-width:320px; margin:0 auto;">There are no staff members currently assigned to your management or available for monitoring.</p>
    </div>
    @endif
</div>
