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
    });

    // User Management (Director only)
    Route::middleware('role:director')->group(function () {
        Route::get('/staff', StaffIndex::class)->name('staff');
    });

    // Reports (all authenticated)
    Route::get('/kpi-reports', KpiReportsIndex::class)->name('kpi-reports');

    // Director only
    Route::middleware('role:director')->group(function () {
        Route::get('/activity-logs', ActivityLogsIndex::class)->name('activity-logs');
    });
});
