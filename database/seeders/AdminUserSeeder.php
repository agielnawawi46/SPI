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
        $defaultPassword = Hash::make('password');

        // 1. Super Admin (Satu-satunya akun default tetap sistem)
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
        $admin->syncRoles(['admin']);
    }
}
