<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DepartmentSeeder::class);
    }

    public function test_incomplete_user_is_redirected_to_complete_profile_and_sees_role_selector(): void
    {
        $user = User::create([
            'name' => 'Pegawai Baru',
            'email' => 'pegawai.baru@peb.co.id',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.complete'));

        $response = $this->actingAs($user)->get(route('profile.complete'));
        $response->assertStatus(200);
        $response->assertSee('Pilih Role terlebih dahulu');
        $response->assertSee('Wajib Role');
        $response->assertSee('Manager (Kepala Bidang / Divisi)');
        $response->assertDontSee('Admin (Administrator Sistem)');
    }

    public function test_user_cannot_self_assign_admin_role(): void
    {
        $user = User::create([
            'name' => 'Pegawai Biasa',
            'email' => 'biasa@peb.co.id',
            'is_active' => true,
        ]);

        $dept = Department::where('code', 'ST_KAP')->first();

        $this->actingAs($user)
            ->post(route('profile.complete.store'), [
                'nip' => 'PEB-9001',
                'role' => 'admin',
                'department_id' => $dept->id,
            ])
            ->assertSessionHasErrors('role');

        $this->assertFalse($user->fresh()->hasRole('admin'));
    }

    public function test_non_standalone_role_requires_department(): void
    {
        $user = User::create([
            'name' => 'Pegawai Staff',
            'email' => 'staff.baru@peb.co.id',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('profile.complete.store'), [
                'nip' => 'PEB-9002',
                'role' => 'staff',
                'department_id' => '',
            ])
            ->assertSessionHasErrors('department_id');
    }

    public function test_user_can_complete_profile_with_role_and_department(): void
    {
        $user = User::create([
            'name' => 'Manager Baru',
            'email' => 'manager.baru@peb.co.id',
            'is_active' => true,
        ]);

        $keuDept = Department::where('code', 'KEU')->first();

        $this->actingAs($user)
            ->post(route('profile.complete.store'), [
                'nip' => 'PEB-9003',
                'role' => 'manager',
                'department_id' => $keuDept->id,
                'position' => 'Manager Keuangan',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals('PEB-9003', $user->nip);
        $this->assertEquals($keuDept->id, $user->department_id);
        $this->assertEquals('Manager Keuangan', $user->position);
        $this->assertTrue($user->hasRole('manager'));
        $this->assertTrue($user->isProfileComplete());
    }

    public function test_standalone_role_direktur_can_complete_profile_without_department(): void
    {
        $user = User::create([
            'name' => 'Direktur Baru',
            'email' => 'direktur.baru@peb.co.id',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('profile.complete.store'), [
                'nip' => 'PEB-9004',
                'role' => 'direktur',
                'department_id' => '',
                'position' => 'Direktur Utama',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertEquals('PEB-9004', $user->nip);
        $this->assertNull($user->department_id);
        $this->assertTrue($user->hasRole('direktur'));
        $this->assertTrue($user->isProfileComplete());

        // Visiting complete-profile again should redirect straight to dashboard (no loop)
        $this->actingAs($user)
            ->get(route('profile.complete'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_select_custom_department_via_lain(): void
    {
        $user = User::create([
            'name' => 'Pegawai Unit Khusus',
            'email' => 'khusus@peb.co.id',
            'is_active' => true,
        ]);

        $lainDept = Department::where('code', 'LAIN')->first();

        $this->actingAs($user)
            ->post(route('profile.complete.store'), [
                'nip' => 'PEB-9005',
                'role' => 'staff',
                'department_id' => $lainDept->id,
                'custom_department' => 'Unit Transformasi Digital',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertTrue($user->hasRole('staff'));
        $this->assertNotNull($user->department);
        $this->assertEquals('Unit Transformasi Digital', $user->department->name);
        $this->assertTrue($user->isProfileComplete());
    }

    public function test_admin_user_create_and_edit_pages_still_load_structured_departments(): void
    {
        $spiDept = Department::where('code', 'SPI')->first();

        $admin = User::create([
            'name' => 'Admin SPI',
            'email' => 'admin.spi@peb.co.id',
            'password' => bcrypt('password'),
            'nip' => 'ADM-999',
            'department_id' => $spiDept->id,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertStatus(200)
            ->assertSee('Form Tambah User')
            ->assertSee('Pilih Role terlebih dahulu');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $admin))
            ->assertStatus(200)
            ->assertSee('Form Edit User');
    }
}
