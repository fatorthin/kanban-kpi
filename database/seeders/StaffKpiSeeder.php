<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Division;
use App\Models\KpiReport;
use App\Models\Task;
use App\Models\TaskReference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffKpiSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Ensure base resources exist ────────────────────────────────
        $taxDiv = Division::firstOrCreate(['name' => 'Tax']);
        $accDiv = Division::firstOrCreate(['name' => 'Accounting']);

        $manager = User::where('email', 'manager@kpi.test')->first()
            ?? User::where('role', 'manager')->first();

        if (! $manager) {
            $this->command->warn('Manager not found. Run RoleAndUserSeeder first.');
            return;
        }

        // ── 2. Create staff accounts ──────────────────────────────────────
        $staffData = [
            ['email' => 'budi.santoso@kpi.test',  'name' => 'Budi Santoso',  'rate' => 22000, 'div' => $taxDiv->id],
            ['email' => 'sari.dewi@kpi.test',      'name' => 'Sari Dewi',     'rate' => 20000, 'div' => $taxDiv->id],
            ['email' => 'andi.prasetyo@kpi.test',  'name' => 'Andi Prasetyo', 'rate' => 18000, 'div' => $accDiv->id],
            ['email' => 'rina.kusuma@kpi.test',    'name' => 'Rina Kusuma',   'rate' => 21000, 'div' => $accDiv->id],
        ];

        $staffList = collect();
        foreach ($staffData as $s) {
            $u = User::firstOrCreate(
                ['email' => $s['email']],
                [
                    'name'            => $s['name'],
                    'password'        => Hash::make('password'),
                    'division_id'     => $s['div'],
                    'base_point_rate' => $s['rate'],
                ]
            );
            $u->syncRoles(['staff']);
            $staffList->push($u);
        }

        // ── 3. Ensure clients & task references exist ─────────────────────
        $clients = [
            ['name' => 'PT Maju Bersama',   'code' => 'CL-010', 'grade' => 'A'],
            ['name' => 'CV Karya Utama',    'code' => 'CL-011', 'grade' => 'B'],
            ['name' => 'UD Harapan Jaya',   'code' => 'CL-012', 'grade' => 'C'],
            ['name' => 'PT Sukses Mandiri', 'code' => 'CL-013', 'grade' => 'A'],
            ['name' => 'CV Pratama Abadi',  'code' => 'CL-014', 'grade' => 'B'],
        ];
        foreach ($clients as $c) {
            Client::firstOrCreate(['code' => $c['code']], $c);
        }

        $refs = [
            ['title' => 'SPT Masa PPN',        'task_type' => 'Client',   'default_difficulty_points' => 30,  'description' => 'Pelaporan SPT Masa PPN bulanan.'],
            ['title' => 'SPT Tahunan Badan',   'task_type' => 'Client',   'default_difficulty_points' => 150, 'description' => 'Penyusunan SPT Tahunan Badan.'],
            ['title' => 'Rekonsiliasi Bank',    'task_type' => 'Client',   'default_difficulty_points' => 50,  'description' => 'Rekonsiliasi mutasi bank bulanan.'],
            ['title' => 'Laporan PPh 21',       'task_type' => 'Client',   'default_difficulty_points' => 40,  'description' => 'Perhitungan dan pelaporan PPh 21.'],
            ['title' => 'Pembukuan Bulanan',    'task_type' => 'Client',   'default_difficulty_points' => 60,  'description' => 'Pencatatan jurnal dan pembukuan bulanan.'],
            ['title' => 'Koreksi Fiskal',       'task_type' => 'Client',   'default_difficulty_points' => 80,  'description' => 'Analisis dan koreksi fiskal laporan keuangan.'],
            ['title' => 'Internal Meeting',     'task_type' => 'Internal', 'default_difficulty_points' => 10,  'description' => 'Rapat koordinasi divisi.'],
            ['title' => 'Pelatihan Internal',   'task_type' => 'Internal', 'default_difficulty_points' => 15,  'description' => 'Pelatihan peningkatan kompetensi staf.'],
        ];
        foreach ($refs as $r) {
            TaskReference::firstOrCreate(['title' => $r['title']], $r);
        }

        $clientList = Client::all();
        $refList    = TaskReference::all();
        $year       = 2026;

        // ── 4. Generate completed tasks Jan–Jun per staff ─────────────────
        foreach ($staffList as $staff) {
            for ($month = 1; $month <= 6; $month++) {
                $start = Carbon::create($year, $month, 1);
                $end   = $start->copy()->endOfMonth();

                // 8–14 completed tasks per staff per month
                $taskCount    = rand(8, 14);
                $totalPoints  = 0;
                $revisionCount = 0;
                $overdueCount  = 0;

                for ($t = 0; $t < $taskCount; $t++) {
                    $ref     = $refList->random();
                    $client  = $ref->task_type === 'Internal' ? null : $clientList->random();
                    $diff    = $ref->default_difficulty_points;

                    // Randomise deadline within the month
                    $deadline = Carbon::create($year, $month, rand(5, 25), rand(8, 17));

                    // 15 % chance of revision (quality penalty)
                    $revisions = (rand(1, 100) <= 15) ? rand(1, 2) : 0;

                    // 10 % chance of overdue completion
                    $isLate    = rand(1, 100) <= 10;
                    $completedAt = $isLate
                        ? $deadline->copy()->addDays(rand(1, 4))
                        : $deadline->copy()->subDays(rand(0, 3));

                    // Points with revision penalty
                    $earnedPoints = $diff - ($revisions * 15);
                    if ($earnedPoints < 0) { $earnedPoints = 0; }

                    $task = Task::create([
                        'task_reference_id' => $ref->id,
                        'client_id'         => $client?->id,
                        'pic_id'            => $staff->id,
                        'manager_id'        => $manager->id,
                        'title'             => $ref->title . ' – ' . ($client ? $client->name : 'Internal') . ' (' . $start->format('M Y') . ')',
                        'description'       => $ref->description,
                        'task_type'         => $ref->task_type,
                        'status'            => 'Completed',
                        'difficulty_points' => $diff,
                        'revision_count'    => $revisions,
                        'deadline'          => $deadline,
                        'completed_at'      => $completedAt,
                        'created_at'        => $start->copy()->addDays(rand(0, 3)),
                        'updated_at'        => $completedAt,
                    ]);

                    $totalPoints  += $earnedPoints;
                    $revisionCount += $revisions;
                    if ($isLate) { $overdueCount++; }
                }

                // ── 5. Generate KPI report for this staff × month ─────────
                // Productivity: points earned vs expected target (target = 400 pts/month)
                $target           = 400;
                $productivityScore = min(100, round(($totalPoints / $target) * 100, 2));

                // Quality: penalty per revision
                $qualityScore = max(0, round(100 - ($revisionCount * 10), 2));

                // Timeliness: penalty per overdue task
                $timelinessScore = max(0, round(100 - ($overdueCount * 8), 2));

                // Final KPI: 40% productivity + 35% quality + 25% timeliness
                $finalKpi = round(
                    ($productivityScore * 0.40) + ($qualityScore * 0.35) + ($timelinessScore * 0.25),
                    2
                );

                // Incentive = totalPoints × base_point_rate
                $totalIncentive = $totalPoints * $staff->base_point_rate;

                KpiReport::updateOrCreate(
                    ['user_id' => $staff->id, 'month' => $month, 'year' => $year],
                    [
                        'total_load_points'  => $totalPoints,
                        'productivity_score' => $productivityScore,
                        'quality_score'      => $qualityScore,
                        'timeliness_score'   => $timelinessScore,
                        'final_kpi_score'    => $finalKpi,
                        'total_incentive'    => $totalIncentive,
                    ]
                );
            }
        }

        $this->command->info('StaffKpiSeeder: 4 staff × 6 months of tasks + KPI reports generated.');
    }
}
