<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $director = Role::firstOrCreate(['name' => 'director']);
        $manager  = Role::firstOrCreate(['name' => 'manager']);
        $staff    = Role::firstOrCreate(['name' => 'staff']);

        // Create divisions
        $taxDiv   = Division::firstOrCreate(['name' => 'Tax']);
        $accDiv   = Division::firstOrCreate(['name' => 'Accounting']);
        $hrdDiv   = Division::firstOrCreate(['name' => 'HRD']);
        $finDiv   = Division::firstOrCreate(['name' => 'Finance']);

        // Director
        $adminUser = User::firstOrCreate(
            ['email' => 'director@kpi.test'],
            [
                'name'            => 'Director',
                'password'        => Hash::make('password'),
                'division_id'     => $taxDiv->id,
                'base_point_rate' => 50000,
            ]
        );
        $adminUser->assignRole($director);

        // Manager Tax
        $mgr = User::firstOrCreate(
            ['email' => 'manager@kpi.test'],
            [
                'name'            => 'Manager Tax',
                'password'        => Hash::make('password'),
                'division_id'     => $taxDiv->id,
                'base_point_rate' => 35000,
            ]
        );
        $mgr->assignRole($manager);

        // Staff Tax
        $staff1 = User::firstOrCreate(
            ['email' => 'staff@kpi.test'],
            [
                'name'            => 'Staff Tax',
                'password'        => Hash::make('password'),
                'division_id'     => $taxDiv->id,
                'base_point_rate' => 20000,
            ]
        );
        $staff1->assignRole($staff);
    }
}
