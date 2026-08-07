<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Kanban\Board;
use App\Livewire\RecurringTasks\Index as RecurringTasksIndex;
use App\Livewire\TaskLibrary\Index as TaskLibraryIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Staff\Index as StaffIndex;
use App\Livewire\KpiReports\Index as KpiReportsIndex;
use App\Livewire\ActivityLogs\Index as ActivityLogsIndex;
use App\Livewire\Divisions\Index as DivisionsIndex;
use App\Livewire\Manager\LoadMonitoring as LoadMonitoringIndex;
use App\Livewire\GradeMultipliers\Index as GradeMultipliersIndex;
use App\Livewire\SubjectiveEvaluations\Index as SubjectiveEvaluationsIndex;
use App\Livewire\SubjectiveEvaluations\Form as SubjectiveEvaluationsForm;
use App\Livewire\SubjectiveEvaluations\Indicators as SubjectiveEvaluationsIndicators;
use App\Livewire\Settings\WhatsAppGateway as WhatsAppGatewaySettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------- Auth
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// --------------------------------------------------------------------- App (auth protected)
Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/kanban', Board::class)->name('kanban');
    Route::get('/recurring-tasks', RecurringTasksIndex::class)->name('recurring-tasks');

    // Management (manager + director only)
    Route::middleware('role:manager|director')->group(function () {
        Route::get('/task-library', TaskLibraryIndex::class)->name('task-library');
        Route::get('/clients', ClientsIndex::class)->name('clients');
        Route::get('/load-monitoring', LoadMonitoringIndex::class)->name('load-monitoring');
        Route::get('/subjective-evaluations/indicators', SubjectiveEvaluationsIndicators::class)->name('subjective-evaluations.indicators');
    });

    // User Management (Director only)
    Route::middleware('role:director')->group(function () {
        Route::get('/staff', StaffIndex::class)->name('staff');
    });

    // Reports (all authenticated)
    Route::get('/kpi-reports', KpiReportsIndex::class)->name('kpi-reports');
    Route::get('/subjective-evaluations', SubjectiveEvaluationsIndex::class)->name('subjective-evaluations.index');
    Route::get('/subjective-evaluations/{id}', SubjectiveEvaluationsForm::class)->name('subjective-evaluations.show');

    // Director only
    Route::middleware('role:director')->group(function () {
        Route::get('/activity-logs', ActivityLogsIndex::class)->name('activity-logs');
        Route::get('/divisions', DivisionsIndex::class)->name('divisions');
        Route::get('/grade-multipliers', GradeMultipliersIndex::class)->name('grade-multipliers');
        Route::get('/settings/whatsapp', WhatsAppGatewaySettings::class)->name('settings.whatsapp');
    });
});


// --------------------------------------------------------------------- Cron / Webhook
Route::get('/cron/process-recurring-tasks', function () {
    Artisan::call('recurring:process');
    return response()->json(['status' => 'success', 'output' => Artisan::output()]);
});

Route::get('/cron/generate-subjective-evaluations', function () {
    Artisan::call('subjective-eval:generate');
    return response()->json(['status' => 'success', 'output' => Artisan::output()]);
});

