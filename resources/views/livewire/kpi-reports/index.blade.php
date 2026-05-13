<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">KPI Reports</h1>
            <p class="text-secondary text-sm mt-1">Monthly performance scores and ranking.</p>
        </div>
    </div>

    {{-- Period selector --}}
    <div class="flex-responsive" style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
        <div class="w-full-mobile">
            <label class="form-label">Month</label>
            <select class="form-select w-full-mobile" wire:model.live="month" style="width:auto;">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full-mobile">
            <label class="form-label">Year</label>
            <select class="form-select w-full-mobile" wire:model.live="year" style="width:auto;">
                @foreach(range(now()->year, now()->year - 2) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($reports->isEmpty())
    <div class="card" style="text-align:center;padding:48px;">
        <div style="font-size:40px;margin-bottom:12px;">📊</div>
        <p style="color:var(--color-text-secondary);">No KPI reports found for this period.</p>
        <p class="text-xs text-muted mt-1">Reports are generated on the 1st of each month.</p>
    </div>
    @else
    {{-- Ranking table --}}
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <h3 style="font-size:15px;font-weight:600;">Performance Ranking —
                {{ \Carbon\Carbon::createFromDate($year, $month)->format('F Y') }}
            </h3>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff</th>
                        <th>S_prod</th>
                        <th>S_qual</th>
                        <th>S_time</th>
                        <th>NAK Score</th>
                        <th>Load Points</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $i => $report)
                    <tr>
                        <td style="font-weight:600;color:{{ $i < 3 ? 'var(--color-primary)' : 'var(--color-neutral)' }};">
                            #{{ $i + 1 }}
                        </td>
                        <td style="font-weight:500;">{{ $report->user?->name }}</td>
                        <td>{{ number_format($report->productivity_score, 1) }}</td>
                        <td>{{ number_format($report->quality_score, 1) }}</td>
                        <td>{{ number_format($report->timeliness_score, 1) }}</td>
                        <td>
                            <strong style="font-size:15px;color:{{ $report->final_kpi_score >= 70 ? 'var(--color-success)' : ($report->final_kpi_score >= 50 ? 'var(--color-warning)' : 'var(--color-error)') }}">
                                {{ number_format($report->final_kpi_score, 1) }}
                            </strong>
                        </td>
                        <td>{{ number_format($report->total_load_points) }}</td>
                        <td>
                            <button class="btn btn-ghost btn-sm" wire:click="$set('selectedUserId', {{ $report->user_id }})">Detail</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Drill-down --}}
    @if($detail)
    <div class="card">
        <div class="card-header">
            <h3 style="font-size:15px;font-weight:600;">{{ $detail->user?->name }} — Performance Detail</h3>
            <button class="btn btn-ghost btn-sm" wire:click="$set('selectedUserId', null)">✕ Close</button>
        </div>
        <div class="card-body">
            <div class="responsive-grid grid-cols-1 md:grid-cols-3 md:gap-6" style="margin-bottom:24px;">
                @foreach(['productivity_score' => ['S_prod','Productivity'],'quality_score'=>['S_qual','Quality'],'timeliness_score'=>['S_time','Timeliness']] as $field => [$code, $label])
                <div style="text-align:center;">
                    <div style="font-size:36px;font-weight:700;letter-spacing:-0.03em;color:var(--color-primary);">
                        {{ number_format($detail->$field, 1) }}
                    </div>
                    <div style="font-size:13px;color:var(--color-text-secondary);">{{ $label }} ({{ $code }})</div>
                    <div class="kpi-bar-track" style="margin-top:8px;">
                        <div class="kpi-bar-fill" style="width:{{ min(100, $detail->$field) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex-responsive items-center justify-between" style="display:flex;justify-content:space-between;padding:16px;background:var(--color-bg);border-radius:var(--radius-lg);">
                <div style="text-align:center;">
                    <div style="font-size:24px;font-weight:700;letter-spacing:-0.03em;">{{ number_format($detail->final_kpi_score, 2) }}</div>
                    <div class="text-sm text-secondary">Final NAK Score</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:24px;font-weight:700;letter-spacing:-0.03em;">{{ number_format($detail->total_load_points) }}</div>
                    <div class="text-sm text-secondary">Load Points</div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
