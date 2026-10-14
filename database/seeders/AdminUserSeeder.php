<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $spiDept = Department::where('code', 'SPI')->first();
        $hrdDept = Department::where('code', 'HRD')->first(); // Using another department for auditee
        $defaultPassword = Hash::make('password');

        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'it.infra@peb.co.id'],
            [
                'name'          => 'Administrator SPI',
                'password'      => $defaultPassword,
                'department_id' => $spiDept?->id,
                'position'      => 'System Administrator',
                'nip'           => 'ADM-001',
                'is_active'     => true,
            ]
        );
        $admin->assignRole('admin');

        // 2. KSPI (Manager SPI)
        $kspi = User::firstOrCreate(
            ['email' => 'kspi@spi.com'],
            [
                'name'          => 'Kepala SPI (KSPI)',
                'password'      => $defaultPassword,
                'department_id' => $spiDept?->id,
                'position'      => 'Kepala Satuan Pengawasan Internal',
                'nip'           => 'SPI-001',
                'is_active'     => true,
            ]
        );
        $kspi->assignRole('kspi');

        // 3. Auditor
        $auditor = User::firstOrCreate(
            ['email' => 'auditor@spi.com'],
            [
                'name'          => 'Auditor Internal',
                'password'      => $defaultPassword,
                'department_id' => $spiDept?->id,
                'position'      => 'Auditor Madya',
                'nip'           => 'SPI-002',
                'is_active'     => true,
            ]
        );
        $auditor->assignRole('auditor');

        // 4. Direktur
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@spi.com'],
            [
                'name'          => 'Direktur Utama',
                'password'      => $defaultPassword,
                'department_id' => null, // Direktur usually doesn't belong to a specific department in this context
                'position'      => 'Direktur',
                'nip'           => 'DIR-001',
                'is_active'     => true,
            ]
        );
        $direktur->assignRole('direktur');

        // 5. Komisaris
        $komisaris = User::firstOrCreate(
            ['email' => 'komisaris@spi.com'],
            [
                'name'          => 'Dewan Komisaris',
                'password'      => $defaultPassword,
                'department_id' => null,
                'position'      => 'Komisaris',
                'nip'           => 'KOM-001',
                'is_active'     => true,
            ]
        );
        $komisaris->assignRole('komisaris');

        $hcDept = Department::where('code', 'HC')->first(); // Using HC department for auditee

        // 6. Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@auditee.com'],
            [
                'name'          => 'Manager HC',
                'password'      => $defaultPassword,
                'department_id' => $hcDept?->id,
                'position'      => 'Manager Human Capital',
                'nip'           => 'HC-001',
                'is_active'     => true,
            ]
        );
        $manager->assignRole('manager');

        // 7. Asman
        $asman = User::firstOrCreate(
            ['email' => 'asman@auditee.com'],
            [
                'name'          => 'Asman HC',
                'password'      => $defaultPassword,
                'department_id' => $hcDept?->id,
                'position'      => 'Asman Human Capital',
                'nip'           => 'HC-002',
                'is_active'     => true,
            ]
        );
        $asman->assignRole('asman');

        // 8. Staff
        $staff = User::firstOrCreate(
            ['email' => 'staff@auditee.com'],
            [
                'name'          => 'Staff HC',
                'password'      => $defaultPassword,
                'department_id' => $hcDept?->id,
                'position'      => 'Staff Human Capital',
                'nip'           => 'HC-003',
                'is_active'     => true,
            ]
        );
        $staff->assignRole('staff');
    }
}
