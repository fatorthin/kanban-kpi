<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Task;
use App\Models\TaskReference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Add some more staff for testing
        $manager = User::where('email', 'manager@kpi.test')->first();
        $taxDivId = $manager->division_id;

        $staff2 = User::firstOrCreate(
            ['email' => 'staff2@kpi.test'],
            ['name' => 'Alice Tax', 'password' => bcrypt('password'), 'division_id' => $taxDivId, 'base_point_rate' => 20000]
        );
        $staff2->assignRole('staff');

        $staff3 = User::firstOrCreate(
            ['email' => 'staff3@kpi.test'],
            ['name' => 'Bob Tax', 'password' => bcrypt('password'), 'division_id' => $taxDivId, 'base_point_rate' => 20000]
        );
        $staff3->assignRole('staff');

        // Create Clients
        $clients = [
            ['name' => 'PT Megah Sejahtera', 'code' => 'CL-001', 'grade' => 'A'],
            ['name' => 'CV Bintang Abadi', 'code' => 'CL-002', 'grade' => 'B'],
            ['name' => 'UD Sumber Rejeki', 'code' => 'CL-003', 'grade' => 'C'],
            ['name' => 'PT Global Logistik', 'code' => 'CL-004', 'grade' => 'A'],
        ];

        foreach ($clients as $c) {
            Client::firstOrCreate(['code' => $c['code']], $c);
        }

        // Create Task References (SOPs)
        $refs = [
            ['title' => 'SPT Masa PPN', 'description' => 'Persiapan dan pelaporan SPT Masa PPN.', 'task_type' => 'Client', 'default_difficulty_points' => 30],
            ['title' => 'SPT Tahunan Badan', 'description' => 'Penyusunan SPT Tahunan Badan beserta lampirannya.', 'task_type' => 'Client', 'default_difficulty_points' => 150],
            ['title' => 'Rekonsiliasi Bank', 'description' => 'Rekonsiliasi mutasi bank bulanan.', 'task_type' => 'Client', 'default_difficulty_points' => 50],
            ['title' => 'Internal Meeting', 'description' => 'Rapat koordinasi divisi tax.', 'task_type' => 'Internal', 'default_difficulty_points' => 10],
        ];

        foreach ($refs as $r) {
            TaskReference::firstOrCreate(['title' => $r['title']], $r);
        }

        // Get created resources
        $clientList = Client::all();
        $refList = TaskReference::all();
        $staffList = User::role('staff')->get();

        if ($staffList->isEmpty() || $clientList->isEmpty() || $refList->isEmpty()) {
            return;
        }

        // Create dummy tasks for Kanban
        $tasksData = [
            // New tasks
            ['status' => 'New', 'title' => 'SPT Masa PPN April - PT Megah', 'deadline' => Carbon::now()->addDays(2), 'diff' => 30],
            ['status' => 'New', 'title' => 'Review Dokumen Pajak', 'deadline' => Carbon::now()->addDays(5), 'diff' => 20],
            
            // In Progress
            ['status' => 'In_Progress', 'title' => 'Rekonsiliasi Bank Q1', 'deadline' => Carbon::now()->addDays(1), 'diff' => 50],
            ['status' => 'In_Progress', 'title' => 'Draft SPT Tahunan CV Bintang', 'deadline' => Carbon::now()->subDays(1), 'diff' => 150], // Overdue
            
            // Review
            ['status' => 'Review', 'title' => 'Laporan PPh 21 Maret', 'deadline' => Carbon::now()->addDays(3), 'diff' => 40],
            
            // Revision
            ['status' => 'Revision', 'title' => 'Koreksi Fiskal UD Sumber', 'deadline' => Carbon::now()->addDays(4), 'diff' => 80],
            
            // Completed
            ['status' => 'Completed', 'title' => 'Setup Database Klien Baru', 'deadline' => Carbon::now()->subDays(5), 'diff' => 100],
        ];

        foreach ($tasksData as $i => $td) {
            $ref = $refList->random();
            $client = $clientList->random();
            $pic = $staffList->random();

            Task::create([
                'task_reference_id' => $ref->id,
                'client_id' => $client->id,
                'pic_id' => $pic->id,
                'manager_id' => $manager->id,
                'title' => $td['title'],
                'description' => 'Ini adalah deskripsi otomatis untuk ' . $td['title'],
                'task_type' => 'Client',
                'status' => $td['status'],
                'difficulty_points' => $td['diff'],
                'deadline' => $td['deadline'],
                'revision_count' => $td['status'] === 'Revision' ? 1 : 0,
            ]);
        }
    }
}
