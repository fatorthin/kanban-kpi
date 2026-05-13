<?php

namespace App\Console\Commands;

use App\Models\KpiReport;
use App\Models\GradeMultiplier;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateKpiReports extends Command
{
    protected $signature = 'kpi:generate {month?} {year?}';
    protected $description = 'Generate monthly KPI reports for all staff';

    public function handle()
    {
        $month = $this->argument('month') ?? now()->subMonth()->month;
        $year = $this->argument('year') ?? now()->subMonth()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $this->info("Generating KPI reports for {$startDate->format('F Y')}...");

        $users = User::role('staff')->get();

        foreach ($users as $user) {
            $tasks = Task::where('pic_id', $user->id)
                ->where('status', 'Completed')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->with('client')
                ->get();

            if ($tasks->isEmpty()) {
                continue;
            }

            // 1. Productivity: Sum of difficulty points * Client Grade Multiplier
            $gradeMultipliers = GradeMultiplier::pluck('multiplier', 'grade')->toArray();

            $totalPoints = $tasks->sum(function($task) use ($gradeMultipliers) {
                $multiplier = $gradeMultipliers[$task->client?->grade] ?? 1.0;
                return $task->difficulty_points * $multiplier;
            });
            $prodScore = min(100, $totalPoints); // Baseline assumption: 100 points = 100% productivity

            // 2. Quality: Based on revision counts
            // Formula: 100 - (Avg Revisions * 10)
            $avgRevisions = $tasks->avg('revision_count') ?? 0;
            $qualScore = max(0, 100 - ($avgRevisions * 10));

            // 3. Timeliness: Tasks completed before/on deadline
            $onTimeTasks = $tasks->filter(fn($t) => $t->completed_at <= $t->deadline)->count();
            $timeScore = ($tasks->count() > 0) ? ($onTimeTasks / $tasks->count()) * 100 : 0;

            // Final Score: Weighted Average (40% Prod, 30% Qual, 30% Time)
            $finalScore = ($prodScore * 0.4) + ($qualScore * 0.3) + ($timeScore * 0.3);

            KpiReport::updateOrCreate(
                ['user_id' => $user->id, 'month' => $month, 'year' => $year],
                [
                    'total_load_points'  => $totalPoints,
                    'productivity_score' => $prodScore,
                    'quality_score'      => $qualScore,
                    'timeliness_score'   => $timeScore,
                    'final_kpi_score'    => $finalScore,
                    'total_incentive'    => 0, // Incentive logic can be added later if needed
                ]
            );
        }

        $this->info('KPI reports generated successfully!');
    }
}
