<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // PERMISSIONS
        // ============================================
        $permissions = [
            // Dashboard
            'dashboard.view',

            // PKPT
            'pkpt.view', 'pkpt.create', 'pkpt.edit', 'pkpt.delete', 'pkpt.approve',

            // Assurance
            'assurance.view', 'assurance.create', 'assurance.edit', 'assurance.delete', 'assurance.approve',

            // Advisory
            'advisory.view', 'advisory.create', 'advisory.edit', 'advisory.delete', 'advisory.approve',

            // Other Activities
            'other_activity.view', 'other_activity.create', 'other_activity.edit', 'other_activity.delete', 'other_activity.approve',

            // Follow Ups / Tindak Lanjut
            'follow_up.view', 'follow_up.create', 'follow_up.edit', 'follow_up.delete',
            'follow_up.approve',        // KSPI approve TL
            'follow_up.upload_evidence',// Auditee upload bukti

            // Reports
            'report.view', 'report.create', 'report.edit', 'report.delete', 'report.approve',

            // Document Actions
            'document.download', 'document.upload_attachment',

            // Users (admin only)
            'user.view', 'user.create', 'user.edit', 'user.delete', 'user.import_csv',

            // Audit Trail
            'audit_log.view',

            // Notifications
            'notification.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ============================================
        // ROLES
        // ============================================

        // 1. Admin — full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. KSPI (Manager SPI) — full access to content + approve
        $kspi = Role::firstOrCreate(['name' => 'kspi']);
        $kspi->givePermissionTo([
            'dashboard.view',
            'pkpt.view', 'pkpt.create', 'pkpt.edit', 'pkpt.approve',
            'assurance.view', 'assurance.create', 'assurance.edit', 'assurance.approve',
            'advisory.view', 'advisory.create', 'advisory.edit', 'advisory.approve',
            'other_activity.view', 'other_activity.create', 'other_activity.edit', 'other_activity.approve',
            'follow_up.view', 'follow_up.create', 'follow_up.edit', 'follow_up.approve',
            'report.view', 'report.create', 'report.edit', 'report.approve',
            'document.download', 'document.upload_attachment',
            'audit_log.view',
            'notification.view',
        ]);

        // 3. Auditor (Staf SPI) — create/edit but no approve
        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'dashboard.view',
            'pkpt.view', 'pkpt.create', 'pkpt.edit',
            'assurance.view', 'assurance.create', 'assurance.edit',
            'advisory.view', 'advisory.create', 'advisory.edit',
            'other_activity.view', 'other_activity.create', 'other_activity.edit',
            'follow_up.view', 'follow_up.create', 'follow_up.edit',
            'report.view', 'report.create', 'report.edit',
            'document.download', 'document.upload_attachment',
            'notification.view',
        ]);

        // 4. Direktur — view all + final approval
        $direktur = Role::firstOrCreate(['name' => 'direktur']);
        $direktur->givePermissionTo([
            'dashboard.view',
            'pkpt.view', 'pkpt.approve',
            'assurance.view', 'assurance.approve',
            'advisory.view', 'advisory.approve',
            'other_activity.view', 'other_activity.approve',
            'follow_up.view',
            'report.view', 'report.approve',
            'document.download',
            'notification.view',
        ]);

        // 5. Komisaris — view reports only
        $komisaris = Role::firstOrCreate(['name' => 'komisaris']);
        $komisaris->givePermissionTo([
            'dashboard.view',
            'report.view',
            'document.download',
            'notification.view',
        ]);

        // 6. Manager — view their dept TL + approve TL
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'dashboard.view',
            'advisory.view', 'advisory.create', 'advisory.edit',
            'follow_up.view',
            'follow_up.approve',
            'follow_up.upload_evidence',
            'document.download',
            'notification.view',
        ]);

        // 7. Asman (Assistant Manager) — view + approve TL for their division
        $asman = Role::firstOrCreate(['name' => 'asman']);
        $asman->givePermissionTo([
            'dashboard.view',
            'advisory.view', 'advisory.create', 'advisory.edit',
            'follow_up.view',
            'follow_up.approve',
            'follow_up.upload_evidence',
            'document.download',
            'notification.view',
        ]);

        // 8. Staff — view + upload evidence
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'dashboard.view',
            'advisory.view', 'advisory.create', 'advisory.edit',
            'follow_up.view',
            'follow_up.upload_evidence',
            'document.download',
            'notification.view',
        ]);
    }
}
