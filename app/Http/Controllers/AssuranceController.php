<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssuranceController extends Controller
{
    public function index(Request $request)
    {
        $typeSlug = $request->get('type');
        $auditType = $request->get('audit_type'); // 'terjadwal' or 'tidak_terjadwal'

        $query = Document::with(['documentType', 'creator'])
            ->whereHas('documentType', fn($q) => $q->where('module', 'assurance'));

        if ($typeSlug) {
            $query->whereHas('documentType', fn($q) => $q->where('slug', $typeSlug));
        }

        if ($auditType) {
            $query->where('audit_type', $auditType);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "{$search}%");
        }

        $documents = $query->latest()->paginate(10);
        
        $documentType = null;
        if ($typeSlug) {
            $documentType = DocumentType::where('slug', $typeSlug)->first();
        }

        return view('assurance.index', compact('documents', 'auditType', 'documentType', 'typeSlug'));
    }

    // Upload-only document types (simple upload form)
    const UPLOAD_ONLY_TYPES = ['program-kerja', 'slide-entry', 'kertas-kerja-audit', 'lha'];

    public function create(Request $request)
    {
        $typeSlug = $request->get('type');
        $user = Auth::user();

        $canCreate = false;
        if ($user->hasRole(['admin', 'auditor', 'kspi'])) {
            $canCreate = true;
        }

        if (!$canCreate) {
            abort(403, 'Anda tidak memiliki izin untuk membuat dokumen tipe ini.');
        }

        $documentTypes = DocumentType::where('module', 'assurance')->get();
        $auditType = $request->get('audit_type', 'terjadwal');
        $selectedType = $typeSlug ? DocumentType::where('slug', $typeSlug)->first() : null;

        // For upload-only types, use simple upload view
        if ($typeSlug && in_array($typeSlug, self::UPLOAD_ONLY_TYPES)) {
            return view('assurance.create-upload', compact('documentTypes', 'auditType', 'selectedType'));
        }

        return view('assurance.create', compact('documentTypes', 'auditType', 'selectedType'));
    }

    public function store(Request $request)
    {
        $documentTypeId = $request->get('document_type_id');
        $selectedType = DocumentType::find($documentTypeId);
        $typeSlug = $selectedType ? $selectedType->slug : null;

        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki izin untuk membuat dokumen tipe ini.');

        // Handle upload-only types (program-kerja, slide-entry, kertas-kerja-audit, lha)
        if ($typeSlug && in_array($typeSlug, self::UPLOAD_ONLY_TYPES)) {
            $request->validate([
                'title'            => 'required|string|max:255',
                'document_type_id' => 'required|exists:document_types,id',
                'audit_type'       => 'required|in:terjadwal,tidak_terjadwal',
                'attachment'       => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
            ]);

            $document = Document::create([
                'title'            => $request->title,
                'document_type_id' => $request->document_type_id,
                'audit_type'       => $request->audit_type,
                'content'          => '',
                'status'           => 'approved',
                'created_by'       => Auth::id(),
            ]);

            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs('attachments', time() . '_' . $originalName, 'public');

            $pdfPath = null;
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, ['doc', 'docx'])) {
                try {
                    \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
                    \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/' . $path));
                    $pdfFilename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
                    $pdfRelativePath = 'attachments/' . $pdfFilename;
                    $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
                    $pdfWriter->save(storage_path('app/public/' . $pdfRelativePath));
                    $pdfPath = $pdfRelativePath;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error converting DOCX to PDF: ' . $e->getMessage());
                }
            }

            $document->attachments()->create([
                'uploaded_by' => Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'pdf_path'    => $pdfPath,
            ]);

            return redirect()->route('assurance.show', $document)
                ->with('success', $selectedType->name . ' berhasil diunggah.');
        }

        // Surat Tugas — full form validation
        $request->validate([
            'title'            => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'audit_type'       => 'required|in:terjadwal,tidak_terjadwal',
            'tgl_pka'          => 'nullable|date',
            'disusun_oleh'     => 'nullable|string|max:255',
            'disahkan_oleh'    => 'nullable|string|max:255',
            'tgl_entry'        => 'nullable|date',
            'tgl_kka'          => 'nullable|date',
            'direview_disahkan_oleh' => 'nullable|string|max:255',
            'tgl_lha'          => 'nullable|date',
            'distribusi_lha'   => 'nullable|string|max:255',
            'tgl_distribusi_lha' => 'nullable|date',
            'tgl_stg'          => 'nullable|date',
            'auditee'          => 'nullable|string|max:255',
            'jadwal_audit'     => 'nullable|string|max:255',
            'lingkup_audit'    => 'nullable|string',
            'ketua_tim'        => 'nullable|string|max:255',
            'anggota_tim'      => 'nullable|string',
            'tujuan_audit'     => 'nullable|string',
            'periode_audit'    => 'nullable|string|max:255',
            'penandatangan_stg'=> 'nullable|string|max:255',
            'header_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_text'      => 'nullable|string',
            'judul_dokumen'    => 'nullable|string|max:255',
            'footer_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi_pembuka'=> 'nullable|string',
            'deskripsi_penutup'=> 'nullable|string',
            'tim_audit'        => 'nullable|array',
            'jabatan_direktur' => 'nullable|string|max:255',
            'nama_direktur'    => 'nullable|string|max:255',
            'attachment'       => 'nullable|file|max:307200',
        ]);

        $dataToStore = [
            'title'            => $request->title,
            'document_type_id' => $request->document_type_id,
            'audit_type'       => $request->audit_type,
            'status'           => 'draft',
            'created_by'       => Auth::id(),
            'tgl_pka'          => $request->tgl_pka,
            'disusun_oleh'     => $request->disusun_oleh,
            'disahkan_oleh'    => $request->disahkan_oleh,
            'tgl_entry'        => $request->tgl_entry,
            'tgl_kka'          => $request->tgl_kka,
            'direview_disahkan_oleh' => $request->direview_disahkan_oleh,
            'tgl_lha'          => $request->tgl_lha,
            'distribusi_lha'   => $request->distribusi_lha,
            'tgl_distribusi_lha' => $request->tgl_distribusi_lha,
            'tgl_stg'          => $request->tgl_stg,
            'auditee'          => $request->auditee,
            'jadwal_audit'     => $request->jadwal_audit,
            'lingkup_audit'    => $request->lingkup_audit,
            'ketua_tim'        => $request->ketua_tim,
            'anggota_tim'      => $request->anggota_tim,
            'tujuan_audit'     => $request->tujuan_audit,
            'periode_audit'    => $request->periode_audit,
            'penandatangan_stg'=> $request->penandatangan_stg,
            'judul_dokumen'    => $request->judul_dokumen,
            'header_text'      => $request->header_text,
            'deskripsi_pembuka'=> $request->deskripsi_pembuka,
            'deskripsi_penutup'=> $request->deskripsi_penutup,
            'tim_audit'        => $request->tim_audit,
            'jabatan_direktur' => $request->jabatan_direktur,
            'nama_direktur'    => $request->nama_direktur,
        ];

        if ($request->hasFile('header_image')) {
            $dataToStore['header_image'] = $request->file('header_image')->store('stg_images', 'public');
        }
        if ($request->hasFile('footer_image')) {
            $dataToStore['footer_image'] = $request->file('footer_image')->store('stg_images', 'public');
        }

        $document = Document::create($dataToStore);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $path = $file->store('attachments/' . date('Y/m'), 'public');

            $pdfPath = null;
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, ['doc', 'docx'])) {
                try {
                    \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
                    \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
                    
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/' . $path));
                    $pdfFilename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
                    $pdfRelativePath = 'attachments/' . date('Y/m') . '/' . $pdfFilename;
                    $pdfAbsolutePath = storage_path('app/public/' . $pdfRelativePath);
                    
                    $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
                    $pdfWriter->save($pdfAbsolutePath);
                    
                    $pdfPath = $pdfRelativePath;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error converting DOCX to PDF: ' . $e->getMessage());
                }
            }

            \App\Models\DocumentAttachment::create([
                'document_id' => $document->id,
                'uploaded_by' => \Illuminate\Support\Facades\Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => 'Lampiran Dokumen',
                'pdf_path'    => $pdfPath,
            ]);
        }

        return redirect()->route('assurance.show', $document)
            ->with('success', 'Dokumen berhasil dibuat.');
    }

    public function show(Document $document)
    {
        if (Auth::check()) {
            Auth::user()->notifications()
                ->where('notifiable_type', Document::class)
                ->where('notifiable_id', $document->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $document->load(['documentType', 'creator', 'attachments', 'approvals.approver']);
        return view('assurance.show', compact('document'));
    }

    public function verify($uuid)
    {
        $document = Document::where('uuid', $uuid)
            ->where('status', 'approved')
            ->firstOrFail();

        return view('assurance.verify', compact('document'));
    }

    public function exportPdf(Document $document)
    {
        $document->load(['documentType']);
        $typeSlug = $document->documentType?->slug;

        if (in_array($typeSlug, ['surat-tugas', 'kertas-kerja-audit'])) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('assurance.pdf.stg', compact('document'));
            $pdf->setPaper('A4', 'portrait');
            $safeTitle = str_replace(['/', '\\'], '-', $document->title ?? 'Draft');
            $prefix = $typeSlug === 'kertas-kerja-audit' ? 'KKA-' : 'STG-';
            return $pdf->download($prefix . $safeTitle . '.pdf');
        }

        abort(404, 'PDF export not available for this document type yet.');
    }


    public function edit(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $documentTypes = DocumentType::where('module', 'assurance')->get();
        return view('assurance.edit', compact('document', 'documentTypes'));
    }

    public function update(Request $request, Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $request->validate([
            'content'       => 'nullable|string',
            'attachment'    => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
            'title'         => 'nullable|string|max:255',
            'tgl_pka'       => 'nullable|date',
            'disusun_oleh'  => 'nullable|string|max:255',
            'disahkan_oleh' => 'nullable|string|max:255',
            'tgl_entry'     => 'nullable|date',
            'peserta_entry' => 'nullable|string|max:255',
            'tgl_kka'       => 'nullable|date',
            'direview_disahkan_oleh' => 'nullable|string|max:255',
            'tgl_stg'       => 'nullable|date',
            'auditee'       => 'nullable|string|max:255',
            'jadwal_audit'  => 'nullable|string|max:255',
            'lingkup_audit' => 'nullable|string',
            'ketua_tim'     => 'nullable|string|max:255',
            'anggota_tim'   => 'nullable|string',
            'tujuan_audit'  => 'nullable|string',
            'periode_audit' => 'nullable|string|max:255',
            'penandatangan_stg' => 'nullable|string|max:255',
            'header_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_text'      => 'nullable|string',
            'judul_dokumen'    => 'nullable|string|max:255',
            'footer_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi_pembuka'=> 'nullable|string',
            'deskripsi_penutup'=> 'nullable|string',
            'tim_audit'        => 'nullable|array',
            'jabatan_direktur' => 'nullable|string|max:255',
            'nama_direktur'    => 'nullable|string|max:255',
        ]);

        $dataToUpdate = [];
        if ($request->has('content')) $dataToUpdate['content'] = $request->content;

        if ($request->has('title')) $dataToUpdate['title'] = $request->title;
        if ($request->has('tgl_pka')) $dataToUpdate['tgl_pka'] = $request->tgl_pka;
        if ($request->has('disusun_oleh')) $dataToUpdate['disusun_oleh'] = $request->disusun_oleh;
        if ($request->has('disahkan_oleh')) $dataToUpdate['disahkan_oleh'] = $request->disahkan_oleh;
        if ($request->has('tgl_entry')) $dataToUpdate['tgl_entry'] = $request->tgl_entry;
        if ($request->has('peserta_entry')) $dataToUpdate['peserta_entry'] = $request->peserta_entry;
        if ($request->has('tgl_kka')) $dataToUpdate['tgl_kka'] = $request->tgl_kka;
        if ($request->has('direview_disahkan_oleh')) $dataToUpdate['direview_disahkan_oleh'] = $request->direview_disahkan_oleh;
        if ($request->has('tgl_stg')) $dataToUpdate['tgl_stg'] = $request->tgl_stg;
        if ($request->has('auditee')) $dataToUpdate['auditee'] = $request->auditee;
        if ($request->has('jadwal_audit')) $dataToUpdate['jadwal_audit'] = $request->jadwal_audit;
        if ($request->has('lingkup_audit')) $dataToUpdate['lingkup_audit'] = $request->lingkup_audit;
        if ($request->has('ketua_tim')) $dataToUpdate['ketua_tim'] = $request->ketua_tim;
        if ($request->has('anggota_tim')) $dataToUpdate['anggota_tim'] = $request->anggota_tim;
        if ($request->has('tujuan_audit')) $dataToUpdate['tujuan_audit'] = $request->tujuan_audit;
        if ($request->has('periode_audit')) $dataToUpdate['periode_audit'] = $request->periode_audit;
        if ($request->has('penandatangan_stg')) $dataToUpdate['penandatangan_stg'] = $request->penandatangan_stg;
        if ($request->has('judul_dokumen')) $dataToUpdate['judul_dokumen'] = $request->judul_dokumen;
        if ($request->has('header_text')) $dataToUpdate['header_text'] = $request->header_text;
        if ($request->has('deskripsi_pembuka')) $dataToUpdate['deskripsi_pembuka'] = $request->deskripsi_pembuka;
        if ($request->has('deskripsi_penutup')) $dataToUpdate['deskripsi_penutup'] = $request->deskripsi_penutup;
        if ($request->has('tim_audit')) $dataToUpdate['tim_audit'] = $request->tim_audit;
        if ($request->has('jabatan_direktur')) $dataToUpdate['jabatan_direktur'] = $request->jabatan_direktur;
        if ($request->has('nama_direktur')) $dataToUpdate['nama_direktur'] = $request->nama_direktur;

        if ($request->hasFile('header_image')) {
            $dataToUpdate['header_image'] = $request->file('header_image')->store('stg_images', 'public');
        }
        if ($request->hasFile('footer_image')) {
            $dataToUpdate['footer_image'] = $request->file('footer_image')->store('stg_images', 'public');
        }

        $document->update($dataToUpdate);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs('attachments', time() . '_' . $originalName, 'public');

            $pdfPath = null;
            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, ['doc', 'docx'])) {
                try {
                    \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
                    \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
                    
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/' . $path));
                    $pdfFilename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
                    $pdfRelativePath = 'attachments/' . $pdfFilename;
                    $pdfAbsolutePath = storage_path('app/public/' . $pdfRelativePath);
                    
                    $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
                    $pdfWriter->save($pdfAbsolutePath);
                    
                    $pdfPath = $pdfRelativePath;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error converting DOCX to PDF: ' . $e->getMessage());
                }
            }

            $document->attachments()->create([
                'uploaded_by' => Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'pdf_path'    => $pdfPath,
            ]);
        }

        return redirect()->route('assurance.show', $document)->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        if (!$document->canBeDeletedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus dokumen ini.');
        }

        $document->delete();

        return redirect()->route('assurance.index', [
            'audit_type' => $document->audit_type,
            'type' => $document->documentType->slug ?? ''
        ])->with('success', 'Dokumen berhasil dihapus.');
    }
}
