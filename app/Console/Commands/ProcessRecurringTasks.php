<?php

namespace App\Console\Commands;

use App\Models\RecurringTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringTasks extends Command
{
    protected $signature = 'recurring:process';
    protected $description = 'Generate tasks from recurring schedules';

    public function handle()
    {
        $today = now();
        $schedules = RecurringTask::where('is_active', true)->get();
        $count = 0;

        foreach ($schedules as $schedule) {
            $shouldCreate = false;

            if ($schedule->frequency === 'daily') {
                $shouldCreate = true;
            } elseif ($schedule->frequency === 'weekly') {
                // Assuming weekly means same day as created_at or Monday
                // For simplicity, let's say Monday
                if ($today->isMonday()) {
                    $shouldCreate = true;
                }
            } elseif ($schedule->frequency === 'monthly') {
                if ($today->day == $schedule->day_of_month) {
                    $shouldCreate = true;
                }
            }

            if ($shouldCreate) {
                // Check if task already exists for today to avoid duplicates
                $exists = Task::where('task_reference_id', $schedule->task_reference_id)
                    ->where('client_id', $schedule->client_id)
                    ->where('pic_id', $schedule->pic_id)
                    ->whereDate('created_at', $today->toDateString())
                    ->exists();

                if (!$exists) {
                    Task::create([
                        'task_reference_id' => $schedule->task_reference_id,
                        'client_id'         => $schedule->client_id,
                        'pic_id'            => $schedule->pic_id,
                        'manager_id'        => $schedule->manager_id,
                        'title'             => $schedule->taskReference->title,
                        'description'       => $schedule->taskReference->description,
                        'task_type'         => $schedule->taskReference->task_type,
                        'status'            => 'New',
                        'difficulty_points' => $schedule->taskReference->default_difficulty_points,
                        'deadline'          => $today->addDays(7), // Default 7 days deadline
                    ]);
                    $count++;
                }
            }
        }

        $this->info("Processed $count recurring tasks.");
    }
}
