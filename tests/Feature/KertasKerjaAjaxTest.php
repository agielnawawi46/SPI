<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Pkpt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KertasKerjaAjaxTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Pkpt $pkpt;
    protected DocumentType $documentType;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $dept = Department::create([
            'name' => 'SPI',
            'code' => 'SPI',
        ]);

        $this->user = User::factory()->create([
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pkpt.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pkpt.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pkpt.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pkpt.delete', 'guard_name' => 'web']);

        $role->givePermissionTo(['pkpt.view', 'pkpt.create', 'pkpt.edit', 'pkpt.delete']);
        $this->user->assignRole($role);

        $this->documentType = DocumentType::firstOrCreate(
            ['slug' => 'kertas-kerja-pkpt'],
            ['name' => 'Kertas Kerja PKPT', 'module' => 'pkpt', 'sort_order' => 2, 'requires_approval' => false]
        );

        $this->pkpt = Pkpt::create([
            'title' => 'PKPT Tahunan 2026',
            'period_year' => 2026,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_upload_kertas_kerja_via_ajax_without_page_reload(): void
    {
        $file = UploadedFile::fake()->create('RJP_Dokumen_2026.docx', 150, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('pkpt.store-kertas-kerja', $this->pkpt), [
                'category' => 'RJP',
                'attachments' => [$file],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'category' => 'RJP',
            'uploaded_count' => 1,
            'total_count' => 1,
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['attachments']);
        $this->assertEquals('RJP_Dokumen_2026.docx', $data['attachments'][0]['file_name']);
        $this->assertEquals('docx', $data['attachments'][0]['ext']);
    }

    public function test_can_delete_kertas_kerja_attachment_via_ajax(): void
    {
        $document = Document::create([
            'pkpt_id' => $this->pkpt->id,
            'document_type_id' => $this->documentType->id,
            'title' => 'Kertas Kerja PKPT: ' . $this->pkpt->title,
            'created_by' => $this->user->id,
            'status' => 'draft',
            'content' => '',
        ]);

        $attachment = $document->attachments()->create([
            'uploaded_by' => $this->user->id,
            'description' => 'RKAP',
            'file_name' => 'RKAP_2026.xlsx',
            'file_path' => 'attachments/fake_rkap.xlsx',
            'file_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->delete(route('pkpt.destroy-kertas-kerja-attachment', [$this->pkpt, $attachment]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_count' => 0,
            'category' => 'RKAP',
        ]);

        $this->assertDatabaseMissing('document_attachments', [
            'id' => $attachment->id,
        ]);
    }

    public function test_fallback_non_ajax_redirects_with_category_anchor(): void
    {
        $file = UploadedFile::fake()->create('Profil_Risiko.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('pkpt.store-kertas-kerja', $this->pkpt), [
                'category' => 'Profil Risiko',
                'attachments' => [$file],
            ]);

        $response->assertRedirect(route('pkpt.kertas-kerja', $this->pkpt) . '#category-profil-risiko');
    }

    public function test_can_upload_pkpt_documents_via_standard_form(): void
    {
        $file1 = UploadedFile::fake()->create('Dokumen_PKPT_Utama.pdf', 300, 'application/pdf');
        $file2 = UploadedFile::fake()->create('Lampiran_PKPT.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->user)
            ->post(route('pkpt.store'), [
                'title' => 'PKPT 2027 Komprehensif',
                'period_year' => 2027,
                'attachments' => [$file1, $file2],
            ]);

        $createdPkpt = Pkpt::where('title', 'PKPT 2027 Komprehensif')->first();
        $this->assertNotNull($createdPkpt);

        $response->assertRedirect(route('pkpt.show', $createdPkpt));

        $doc = Document::where('pkpt_id', $createdPkpt->id)->first();
        $this->assertNotNull($doc);
        $this->assertEquals(2, $doc->attachments()->count());
    }

    public function test_can_append_attachments_to_existing_pkpt(): void
    {
        $file = UploadedFile::fake()->create('Revisi_Lampiran.xlsx', 120, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->user)
            ->put(route('pkpt.update', $this->pkpt), [
                'title' => $this->pkpt->title,
                'period_year' => $this->pkpt->period_year,
                'attachments' => [$file],
            ]);

        $response->assertRedirect(route('pkpt.show', $this->pkpt));

        $doc = Document::where('pkpt_id', $this->pkpt->id)->first();
        $this->assertNotNull($doc);
        $this->assertTrue($doc->attachments()->where('file_name', 'Revisi_Lampiran.xlsx')->exists());
    }
}
