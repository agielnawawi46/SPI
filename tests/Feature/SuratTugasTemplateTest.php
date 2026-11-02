<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratTugasTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAuditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $roleStaff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $permCreate = Permission::firstOrCreate(['name' => 'assurance.create', 'guard_name' => 'web']);
        $permView = Permission::firstOrCreate(['name' => 'assurance.view', 'guard_name' => 'web']);
        $permEdit = Permission::firstOrCreate(['name' => 'assurance.edit', 'guard_name' => 'web']);

        $roleAdmin->givePermissionTo([$permCreate, $permView, $permEdit]);
        $roleAuditor->givePermissionTo([$permCreate, $permView, $permEdit]);
        $roleStaff->givePermissionTo([$permView]);

        // Ensure DocumentType for surat-tugas & st-qa exists
        DocumentType::firstOrCreate(
            ['slug' => 'surat-tugas'],
            [
                'name' => 'Surat Tugas Audit',
                'module' => 'assurance',
                'requires_approval' => true,
            ]
        );

        DocumentType::firstOrCreate(
            ['slug' => 'st-qa'],
            [
                'name' => 'Surat Tugas QA',
                'module' => 'qa',
                'requires_approval' => true,
            ]
        );
    }

    public function test_auditor_can_save_stg_header_and_numbering_template_via_ajax()
    {
        $auditor = User::create([
            'name' => 'Auditor Template Tester',
            'email' => 'auditor.template@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $payload = [
            'audit_type'        => 'terjadwal',
            'header_text'       => 'FMR-003-14-REV',
            'tanggal_formulir'  => '2026-11-05',
            'judul_dokumen'     => 'SURAT TUGAS AUDIT KHUSUS',
            'title'             => 'NO. 05.STA/XI/SPI-PEB/2026',
            'deskripsi_pembuka' => 'Menugaskan tim audit untuk melakukan pengawasan kepatuhan.',
        ];

        $response = $this->actingAs($auditor)
            ->postJson(route('assurance.save-header-template'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'header_text' => 'FMR-003-14-REV',
                    'judul_dokumen' => 'SURAT TUGAS AUDIT KHUSUS',
                    'title' => 'NO. 05.STA/XI/SPI-PEB/2026',
                ],
            ]);

        // Verify persisted in Setting model
        $saved = Setting::get('stg_template_terjadwal');
        $this->assertIsArray($saved);
        $this->assertEquals('FMR-003-14-REV', $saved['header_text']);
        $this->assertEquals('NO. 05.STA/XI/SPI-PEB/2026', $saved['title']);
        $this->assertEquals('SURAT TUGAS AUDIT KHUSUS', $saved['judul_dokumen']);
    }

    public function test_create_form_automatically_prepopulates_saved_template_values()
    {
        $auditor = User::create([
            'name' => 'Auditor Prepopulate Tester',
            'email' => 'auditor.prepopulate@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        // Pre-save template in settings
        Setting::set('stg_template_terjadwal', [
            'header_text'       => 'FMR-CUSTOM-99',
            'tanggal_formulir'  => '2026-11-10',
            'judul_dokumen'     => 'SURAT TUGAS AUDIT OPERASIONAL',
            'title'             => 'NO. 99.STA/OPS/2026',
            'deskripsi_pembuka' => 'Deskripsi pembuka kustom yang tersimpan otomatis.',
        ]);

        $response = $this->actingAs($auditor)
            ->get(route('assurance.create', ['audit_type' => 'terjadwal', 'type' => 'surat-tugas']));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('FMR-CUSTOM-99', $content);
        $this->assertStringContainsString('SURAT TUGAS AUDIT OPERASIONAL', $content);
        $this->assertStringContainsString('NO. 99.STA/OPS/2026', $content);
        $this->assertStringContainsString('Deskripsi pembuka kustom yang tersimpan otomatis.', $content);
        $this->assertStringContainsString("index === 0 ? 'Ketua Tim' : 'Anggota Tim'", $content);
    }

    public function test_user_without_permission_cannot_save_header_template()
    {
        $staff = User::create([
            'name' => 'Staff Without Permission',
            'email' => 'staff.unauthorized@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $staff->assignRole('staff');

        $response = $this->actingAs($staff)
            ->postJson(route('assurance.save-header-template'), [
                'audit_type'  => 'terjadwal',
                'header_text' => 'FMR-HACK',
            ]);

        $response->assertStatus(403);
    }

    public function test_auditor_can_save_stg_closing_and_signature_template_via_ajax()
    {
        $auditor = User::create([
            'name' => 'Auditor Closing Tester',
            'email' => 'auditor.closing@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $payload = [
            'audit_type'        => 'terjadwal',
            'deskripsi_penutup' => 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.',
            'tgl_stg'           => '2026-11-06',
            'jabatan_direktur'  => 'Direktur Utama PT PEB',
            'nama_direktur'     => 'AHMAD DAHLAN',
        ];

        $response = $this->actingAs($auditor)
            ->postJson(route('assurance.save-closing-template'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'deskripsi_penutup' => 'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.',
                    'jabatan_direktur'  => 'Direktur Utama PT PEB',
                    'nama_direktur'     => 'AHMAD DAHLAN',
                ],
            ]);

        // Verify persisted in Setting model
        $saved = Setting::get('stg_closing_template_terjadwal');
        $this->assertIsArray($saved);
        $this->assertEquals('Direktur Utama PT PEB', $saved['jabatan_direktur']);
        $this->assertEquals('AHMAD DAHLAN', $saved['nama_direktur']);
    }

    public function test_create_form_automatically_prepopulates_saved_closing_template_values()
    {
        $auditor = User::create([
            'name' => 'Auditor Closing Prepopulate Tester',
            'email' => 'auditor.closingpre@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        // Pre-save closing template in settings
        Setting::set('stg_closing_template_terjadwal', [
            'deskripsi_penutup' => 'Demikian surat tugas ini ditetapkan sebagai mandat resmi pemeriksaan.',
            'tgl_stg'           => '2026-11-15',
            'jabatan_direktur'  => 'Direktur Operasi dan Niaga',
            'nama_direktur'     => 'BUDI SANTOSO',
        ]);

        $response = $this->actingAs($auditor)
            ->get(route('assurance.create', ['audit_type' => 'terjadwal', 'type' => 'surat-tugas']));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Demikian surat tugas ini ditetapkan sebagai mandat resmi pemeriksaan.', $content);
        $this->assertStringContainsString('Direktur Operasi dan Niaga', $content);
        $this->assertStringContainsString('BUDI SANTOSO', $content);
    }

    public function test_store_document_saves_jadwal_mulai_selesai_and_formats_jadwal_audit()
    {
        $auditor = User::create([
            'name' => 'Auditor Schedule Tester',
            'email' => 'auditor.schedule@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $docType = DocumentType::where('slug', 'surat-tugas')->first();

        $payload = [
            'document_type_id' => $docType->id,
            'audit_type'       => 'terjadwal',
            'title'            => 'NO. 10.STA/VIII/SPI-PEB/2026',
            'auditee'          => 'Divisi Operasi',
            'lingkup_audit'    => 'Pengujian operasional pelabuhan',
            'tujuan_audit'     => 'Memastikan ketaatan SOP',
            'periode_audit'    => 'Semester II Tahun 2026',
            'jadwal_mulai'     => '2026-08-03',
            'jadwal_selesai'   => '2026-09-11',
            'tgl_stg'          => '2026-08-01',
            'jabatan_direktur' => 'Direktur Utama',
            'nama_direktur'    => 'Budi Karya',
        ];

        $response = $this->actingAs($auditor)
            ->post(route('assurance.store'), $payload);

        $response->assertRedirect();

        $doc = Document::where('title', 'NO. 10.STA/VIII/SPI-PEB/2026')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('2026-08-03', $doc->jadwal_mulai->format('Y-m-d'));
        $this->assertEquals('2026-09-11', $doc->jadwal_selesai->format('Y-m-d'));
        $this->assertEquals('03 Agustus 2026 s.d. 11 September 2026', $doc->jadwal_audit);
    }

    public function test_update_document_updates_jadwal_mulai_selesai_and_formats_jadwal_audit()
    {
        $auditor = User::create([
            'name' => 'Auditor Update Tester',
            'email' => 'auditor.update@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $docType = DocumentType::where('slug', 'surat-tugas')->first();

        $doc = Document::create([
            'document_type_id' => $docType->id,
            'audit_type'       => 'terjadwal',
            'title'            => 'NO. 11.STA/VIII/SPI-PEB/2026',
            'auditee'          => 'Divisi Keuangan',
            'jadwal_mulai'     => '2026-07-01',
            'jadwal_selesai'   => '2026-07-15',
            'jadwal_audit'     => '01 Juli 2026 s.d. 15 Juli 2026',
            'created_by'       => $auditor->id,
            'status'           => 'draft',
        ]);

        $updatePayload = [
            'title'          => 'NO. 11.STA/VIII/SPI-PEB/2026',
            'auditee'        => 'Divisi Keuangan & Akuntansi',
            'jadwal_mulai'   => '2026-10-05',
            'jadwal_selesai' => '2026-10-20',
        ];

        $response = $this->actingAs($auditor)
            ->put(route('assurance.update', $doc), $updatePayload);

        $response->assertRedirect(route('assurance.show', $doc));

        $doc->refresh();
        $this->assertEquals('2026-10-05', $doc->jadwal_mulai->format('Y-m-d'));
        $this->assertEquals('2026-10-20', $doc->jadwal_selesai->format('Y-m-d'));
        $this->assertEquals('05 Oktober 2026 s.d. 20 Oktober 2026', $doc->jadwal_audit);
    }

    public function test_auditor_can_save_qa_header_template_via_ajax()
    {
        $auditor = User::create([
            'name' => 'Auditor QA Header Tester',
            'email' => 'auditor.qaheader@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $payload = [
            'header_text'       => 'FMR-QA-01-REV',
            'tanggal_formulir'  => '2026-11-08',
            'judul_dokumen'     => 'SURAT TUGAS AUDIT QA KHUSUS',
            'title'             => 'NO. 08.STQ/XI/SPI-PEB/2026',
            'deskripsi_pembuka' => 'Dalam rangka audit mutu internal terpadu.',
        ];

        $response = $this->actingAs($auditor)
            ->postJson(route('other-activities.qa.save-header-template'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'header_text'       => 'FMR-QA-01-REV',
                    'judul_dokumen'     => 'SURAT TUGAS AUDIT QA KHUSUS',
                    'title'             => 'NO. 08.STQ/XI/SPI-PEB/2026',
                    'deskripsi_pembuka' => 'Dalam rangka audit mutu internal terpadu.',
                ],
            ]);

        $saved = Setting::get('qa_stg_template');
        $this->assertIsArray($saved);
        $this->assertEquals('FMR-QA-01-REV', $saved['header_text']);
        $this->assertEquals('NO. 08.STQ/XI/SPI-PEB/2026', $saved['title']);
    }

    public function test_auditor_can_save_qa_closing_template_via_ajax()
    {
        $auditor = User::create([
            'name' => 'Auditor QA Closing Tester',
            'email' => 'auditor.qaclosing@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $payload = [
            'deskripsi_penutup' => 'Demikian surat tugas QA ini dikeluarkan untuk dilaksanakan penuh tanggung jawab.',
            'tgl_stg'           => '2026-11-12',
            'jabatan_direktur'  => 'Direktur Utama PT PEB',
            'nama_direktur'     => 'H. SAMSUL BAHRI, M.M.',
        ];

        $response = $this->actingAs($auditor)
            ->postJson(route('other-activities.qa.save-closing-template'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'jabatan_direktur' => 'Direktur Utama PT PEB',
                    'nama_direktur'    => 'H. SAMSUL BAHRI, M.M.',
                ],
            ]);

        $saved = Setting::get('qa_stg_closing_template');
        $this->assertIsArray($saved);
        $this->assertEquals('H. SAMSUL BAHRI, M.M.', $saved['nama_direktur']);
    }

    public function test_qa_create_form_prepopulates_saved_template_values()
    {
        $auditor = User::create([
            'name' => 'Auditor QA Prepopulate Tester',
            'email' => 'auditor.qapre@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        Setting::set('qa_stg_template', [
            'header_text'       => 'FMR-QA-CUSTOM-77',
            'tanggal_formulir'  => '2026-11-20',
            'judul_dokumen'     => 'SURAT TUGAS AUDIT QA STANDARD',
            'title'             => 'NO. 77.STQ/XI/2026',
            'deskripsi_pembuka' => 'Deskripsi pembuka QA tersimpan otomatis.',
        ]);

        Setting::set('qa_stg_closing_template', [
            'deskripsi_penutup' => 'Penutup surat tugas QA khusus tersimpan.',
            'tgl_stg'           => '2026-11-25',
            'jabatan_direktur'  => 'Plt. Direktur Operasi',
            'nama_direktur'     => 'DEDI KURNIAWAN',
        ]);

        $response = $this->actingAs($auditor)
            ->get(route('other-activities.qa.create', ['type' => 'st-qa']));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('FMR-QA-CUSTOM-77', $content);
        $this->assertStringContainsString('SURAT TUGAS AUDIT QA STANDARD', $content);
        $this->assertStringContainsString('NO. 77.STQ/XI/2026', $content);
        $this->assertStringContainsString('Deskripsi pembuka QA tersimpan otomatis.', $content);
        $this->assertStringContainsString('Penutup surat tugas QA khusus tersimpan.', $content);
        $this->assertStringContainsString('DEDI KURNIAWAN', $content);
        $this->assertStringContainsString("index === 0 ? 'Ketua Tim' : 'Anggota Tim'", $content);
    }

    public function test_qa_store_and_update_saves_jadwal_mulai_selesai_and_formats_jadwal_audit()
    {
        $auditor = User::create([
            'name' => 'Auditor QA Schedule Tester',
            'email' => 'auditor.qasched@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $auditor->assignRole('auditor');

        $docType = DocumentType::where('slug', 'st-qa')->first();

        $storePayload = [
            'document_type_id' => $docType->id,
            'title'            => 'NO. 15.STQ/VIII/SPI-PEB/2026',
            'periode_audit'    => 'Triwulan III Tahun 2026',
            'lingkup_audit'    => 'Quality review proses pengadaan',
            'tujuan_audit'     => 'Kesesuaian standar mutu',
            'jadwal_mulai'     => '2026-08-10',
            'jadwal_selesai'   => '2026-08-28',
            'tgl_stg'          => '2026-08-05',
            'jabatan_direktur' => 'Direktur Utama',
            'nama_direktur'    => 'Samsul Bahri',
        ];

        $response = $this->actingAs($auditor)
            ->post(route('other-activities.qa.store'), $storePayload);

        $response->assertRedirect();

        $doc = Document::where('title', 'NO. 15.STQ/VIII/SPI-PEB/2026')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('2026-08-10', $doc->jadwal_mulai->format('Y-m-d'));
        $this->assertEquals('2026-08-28', $doc->jadwal_selesai->format('Y-m-d'));
        $this->assertEquals('10 Agustus 2026 s.d. 28 Agustus 2026', $doc->jadwal_audit);

        // Test Update
        $updatePayload = [
            'jadwal_mulai'   => '2026-09-01',
            'jadwal_selesai' => '2026-09-20',
        ];

        $updateResponse = $this->actingAs($auditor)
            ->put(route('other-activities.qa.update', $doc), $updatePayload);

        $updateResponse->assertRedirect(route('other-activities.qa.show', $doc));

        $doc->refresh();
        $this->assertEquals('2026-09-01', $doc->jadwal_mulai->format('Y-m-d'));
        $this->assertEquals('2026-09-20', $doc->jadwal_selesai->format('Y-m-d'));
        $this->assertEquals('01 September 2026 s.d. 20 September 2026', $doc->jadwal_audit);
    }
}
