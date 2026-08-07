<?php

namespace App\Console\Commands;

use App\Models\EvalIndicator;
use App\Models\SubjectiveEvaluation;
use App\Models\SubjectiveEvaluationScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlySubjectiveEvaluations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subjective-eval:generate {--month= : Month number (1-12)} {--year= : Year number (YYYY)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly subjective performance evaluation sessions for all active staff.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = (int) ($this->option('month') ?: Carbon::now()->month);
        $year  = (int) ($this->option('year') ?: Carbon::now()->year);

        $this->info("Generating Subjective Evaluation sessions for Period: {$month}/{$year}...");

        $activeUsers = User::where('is_active', true)->get();
        $indicators  = EvalIndicator::all();

        if ($indicators->isEmpty()) {
            $this->warn('No evaluation indicators found in database. Please run db:seed --class=SubjectiveEvaluationSeeder first.');
            return Command::FAILURE;
        }

        $createdCount = 0;

        foreach ($activeUsers as $user) {
            // Find assigned evaluator (manager_id or first manager)
            $evaluatorId = $user->manager_id ?: ($user->managers()->first()?->id);

            $evaluation = SubjectiveEvaluation::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'month'   => $month,
                    'year'    => $year,
                ],
                [
                    'evaluator_id'   => $evaluatorId,
                    'self_status'    => 'Draft',
                    'manager_status' => 'Draft',
                ]
            );

            // Update evaluator if previously null and now found
            if (!$evaluation->evaluator_id && $evaluatorId) {
                $evaluation->update(['evaluator_id' => $evaluatorId]);
            }

            // Create blank scores for all indicators if missing
            foreach ($indicators as $indicator) {
                SubjectiveEvaluationScore::firstOrCreate(
                    [
                        'subjective_evaluation_id' => $evaluation->id,
                        'eval_indicator_id'        => $indicator->id,
                    ],
                    [
                        'self_score'    => null,
                        'manager_score' => null,
                    ]
                );
            }

            $createdCount++;
        }

        $this->info("Successfully generated/synced evaluation sheets for {$createdCount} active staff members.");

        return Command::SUCCESS;
    }
}
