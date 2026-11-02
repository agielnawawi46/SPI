<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\FollowUp;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(\Database\Seeders\DocumentTypeSeeder::class);
    }

    public function test_department_parent_and_child_hierarchy()
    {
        $keu = Department::where('code', 'KEU')->first();
        $this->assertNotNull($keu);
        $this->assertEquals('manager', $keu->level);

        $amRb = Department::where('code', 'AM_RB')->first();
        $this->assertNotNull($amRb);
        $this->assertEquals('asman', $amRb->level);
        $this->assertEquals($keu->id, $amRb->parent_id);

        $stBs = Department::where('code', 'ST_BS')->first();
        $this->assertNotNull($stBs);
        $this->assertEquals('staff', $stBs->level);
        $this->assertEquals($amRb->id, $stBs->parent_id);

        // Test getAllChildIds
        $keuChildren = $keu->getAllChildIds();
        $this->assertContains($keu->id, $keuChildren);
        $this->assertContains($amRb->id, $keuChildren);
        $this->assertContains($stBs->id, $keuChildren);

        // Test getAllParentIds
        $stBsParents = $stBs->getAllParentIds();
        $this->assertContains($stBs->id, $stBsParents);
        $this->assertContains($amRb->id, $stBsParents);
        $this->assertContains($keu->id, $stBsParents);

        // Manager OPS should not be in ST_BS parents
        $ops = Department::where('code', 'OPS')->first();
        $this->assertNotContains($ops->id, $stBsParents);
    }

    public function test_user_creation_page_loads_with_departments()
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get(route('admin.users.create'));
        $response->assertStatus(200);
        $response->assertSee('Form Tambah User');
        $response->assertSee('Tampilkan seluruh bidang struktur organisasi');

        // Test Edit Page
        $editResponse = $this->get(route('admin.users.edit', $admin));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Form Edit User');
        $editResponse->assertSee('Tampilkan seluruh bidang struktur organisasi');
    }

    public function test_can_create_user_with_specific_role_and_department()
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $amDept = Department::where('code', 'AM_RB')->first();

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Asman Revenue',
            'email' => 'asman.rev@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'asman',
            'department_id' => $amDept->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'asman.rev@example.com',
            'department_id' => $amDept->id,
        ]);

        $createdUser = User::where('email', 'asman.rev@example.com')->first();
        $this->assertTrue($createdUser->hasRole('asman'));
    }

    public function test_manager_and_asman_can_monitor_child_department_follow_ups()
    {
        $docType = DocumentType::where('slug', 'monitoring-tl')->first() ?? DocumentType::first();

        $admin = User::create([
            'name' => 'Admin Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $doc = Document::create([
            'title' => 'Monitoring TL 2026',
            'document_type_id' => $docType->id,
            'created_by' => $admin->id,
            'status' => 'approved',
        ]);

        $keuDept = Department::where('code', 'KEU')->first();
        $amDept = Department::where('code', 'AM_RB')->first();
        $stDept = Department::where('code', 'ST_BS')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        // Follow up assigned to staff unit (ST_BS)
        $followUp = FollowUp::create([
            'document_id' => $doc->id,
            'created_by' => $admin->id,
            'auditee_department_id' => $stDept->id,
            'finding' => 'Temuan Billing 2026',
            'recommendation' => 'Perbaiki rekonsiliasi',
            'due_date' => now()->addDays(30),
            'status' => 'open',
            'approval_status' => 'pending',
        ]);

        // 1. Manager Keuangan (Parent of Parent)
        $managerKeu = User::create([
            'name' => 'Manager Keuangan',
            'email' => 'manager.keu@example.com',
            'password' => bcrypt('password'),
            'department_id' => $keuDept->id,
            'is_active' => true,
        ]);
        $managerKeu->assignRole('manager');

        // 2. Asman Revenue (Parent)
        $asmanRev = User::create([
            'name' => 'Asman Revenue',
            'email' => 'asman.revenue@example.com',
            'password' => bcrypt('password'),
            'department_id' => $amDept->id,
            'is_active' => true,
        ]);
        $asmanRev->assignRole('asman');

        // 3. Staff Billing (Direct)
        $staffBilling = User::create([
            'name' => 'Staff Billing',
            'email' => 'staff.billing@example.com',
            'password' => bcrypt('password'),
            'department_id' => $stDept->id,
            'is_active' => true,
        ]);
        $staffBilling->assignRole('staff');

        // 4. Manager OPS (Different division)
        $managerOps = User::create([
            'name' => 'Manager OPS',
            'email' => 'manager.ops@example.com',
            'password' => bcrypt('password'),
            'department_id' => $opsDept->id,
            'is_active' => true,
        ]);
        $managerOps->assignRole('manager');

        // Manager Keu should see the follow up
        $this->actingAs($managerKeu);
        $respManager = $this->get(route('auditee.index'));
        $respManager->assertStatus(200);
        $respManager->assertSee('Temuan Billing 2026');

        // Asman Revenue should see the follow up
        $this->actingAs($asmanRev);
        $respAsman = $this->get(route('auditee.index'));
        $respAsman->assertStatus(200);
        $respAsman->assertSee('Temuan Billing 2026');

        // Staff Billing should see the follow up
        $this->actingAs($staffBilling);
        $respStaff = $this->get(route('auditee.index'));
        $respStaff->assertStatus(200);
        $respStaff->assertSee('Temuan Billing 2026');

        // Manager OPS should NOT see the follow up
        $this->actingAs($managerOps);
        $respOps = $this->get(route('auditee.index'));
        $respOps->assertStatus(200);
        $respOps->assertDontSee('Temuan Billing 2026');
    }

    public function test_csv_template_and_import_follow_organizational_structure()
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        // 1. Check Import Form Page
        $formResp = $this->get(route('admin.users.import'));
        $formResp->assertStatus(200);
        $formResp->assertSee('Daftar Kode Bidang Resmi');
        $formResp->assertSee('KEU');
        $formResp->assertSee('AM_KMR');
        $formResp->assertSee('ST_KPK');

        // 2. Check Download Template
        $downloadResp = $this->get(route('admin.users.import.template'));
        $downloadResp->assertStatus(200);
        $downloadResp->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $csvContent = $downloadResp->streamedContent();
        $this->assertStringContainsString('name,email,password,role,nip,department_code', $csvContent);
        $this->assertStringContainsString('manager', $csvContent);
        $this->assertStringContainsString('KEU', $csvContent);
        $this->assertStringContainsString('AM_KMR', $csvContent);
        $this->assertStringContainsString('ST_KPK', $csvContent);

        // 3. Test CSV Import Execution
        $csvData = "name,email,password,role,nip,department_code\n";
        $csvData .= "Manager Import,mgr.import@peb.co.id,Password123!,manager,19810101001,KEU\n";
        $csvData .= "Asman Import,asman.import@peb.co.id,Password123!,asman,19860202002,AM_KMR\n";
        $csvData .= "Staff Import,staff.import@peb.co.id,Password123!,staff,19930303003,ST_KPK\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvData);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'import_test.csv',
            'text/csv',
            null,
            true
        );

        $postResp = $this->post(route('admin.users.import.post'), [
            'csv_file' => $uploadedFile,
        ]);

        $postResp->assertRedirect(route('admin.users.index'));
        $postResp->assertSessionHas('success');

        // Verify users created with correct department
        $keu = Department::where('code', 'KEU')->first();
        $amKmr = Department::where('code', 'AM_KMR')->first();
        $stKpk = Department::where('code', 'ST_KPK')->first();

        $this->assertDatabaseHas('users', [
            'email' => 'mgr.import@peb.co.id',
            'department_id' => $keu->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'asman.import@peb.co.id',
            'department_id' => $amKmr->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'staff.import@peb.co.id',
            'department_id' => $stKpk->id,
        ]);

        @unlink($tempFile);
    }

    public function test_direktur_and_komisaris_stand_alone_without_department()
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $keu = Department::where('code', 'KEU')->first();

        // 1. Create Direktur (even if department_id was sent in form payload, it gets cleared to null)
        $respDirektur = $this->post(route('admin.users.store'), [
            'name' => 'Bapak Direktur',
            'email' => 'direktur@peb.co.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'direktur',
            'department_id' => $keu->id, // Sent accidentally
            'is_active' => 1,
        ]);
        $respDirektur->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'direktur@peb.co.id',
            'department_id' => null,
        ]);

        // 2. Create Komisaris
        $respKomisaris = $this->post(route('admin.users.store'), [
            'name' => 'Bapak Komisaris',
            'email' => 'komisaris@peb.co.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'komisaris',
            'department_id' => '',
            'is_active' => 1,
        ]);
        $respKomisaris->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'komisaris@peb.co.id',
            'department_id' => null,
        ]);

        // 3. Update existing user to Direktur clears department_id
        $user = User::where('email', 'staff.import@peb.co.id')->first();
        if ($user) {
            $updateResp = $this->put(route('admin.users.update', $user), [
                'name' => 'Promoted to Direktur',
                'email' => $user->email,
                'role' => 'direktur',
                'department_id' => $keu->id,
                'is_active' => 1,
            ]);
            $updateResp->assertRedirect(route('admin.users.index'));
            $user->refresh();
            $this->assertNull($user->department_id);
            $this->assertTrue($user->hasRole('direktur'));
        }
    }
}
