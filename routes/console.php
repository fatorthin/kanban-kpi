<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule for Recurring Tasks (Daily at midnight)
Schedule::command('recurring:process')->daily();

// Schedule for KPI Reports (1st of every month at midnight)
Schedule::command('kpi:generate')->monthlyOn(1, '00:00');

// Schedule for Subjective Performance Evaluation (25th of every month at midnight)
Schedule::command('subjective-eval:generate')->monthlyOn(25, '00:00');

