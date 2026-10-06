<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvisoryController extends Controller
{
    public function index(Request $request)
    {
        $typeSlug = $request->get('type');
        
        $query = Document::with(['documentType', 'creator'])
            ->whereHas('documentType', fn($q) => $q->where('module', 'advisory'));
            
        if ($typeSlug) {
            $query->whereHas('documentType', fn($q) => $q->where('slug', $typeSlug));
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "{$search}%");
        }
        
        $user = auth()->user();

        // Auditee tidak berhak mengakses Laporan Konsultasi
        if ($user->hasRole(['manager', 'asman', 'staff']) && $typeSlug === 'lap-konsultasi') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('auditee_id', $user->id);
            });
        }
            
        $documents = $query->latest()->paginate(10);
        
        $documentType = null;
        if ($typeSlug) {
            $documentType = DocumentType::where('slug', $typeSlug)->first();
        }

        $canCreate = $user->can('advisory.create');

        return view('advisory.index', compact('documents', 'documentType', 'typeSlug', 'canCreate'));
    }

    public function create(Request $request)
    {
        $documentTypes = DocumentType::where('module', 'advisory')->get();
        $typeSlug = $request->get('type');
        $selectedType = $typeSlug ? DocumentType::where('slug', $typeSlug)->first() : null;
        $auditeeManagers = \App\Models\User::role('manager')->get();

        return view('advisory.create', compact('documentTypes', 'selectedType', 'auditeeManagers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('advisory.create')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat dokumen ini.');
        }

        $selectedType = DocumentType::find($request->document_type_id);
        if ($selectedType && $selectedType->slug === 'lap-konsultasi') {
            abort_if(!$user->hasRole(['admin', 'auditor', 'kspi']), 403, 'Hanya Admin, Auditor, dan KSPI yang dapat mengupload Laporan Konsultasi.');
        }

        $request->validate([
            'title'                  => 'required|string|max:255',
            'document_type_id'       => 'required|exists:document_types,id',
            'attachment'             => 'nullable|file|max:307200',
            'nama_kspi'              => 'nullable|string',
            'nama_pimpinan_klien'    => 'nullable|string',
            'nama_jabatan'           => 'nullable|string',
            'nip'                    => 'nullable|string',
            'bidang_tugas'           => 'nullable|string',
            'deskripsi_permasalahan' => 'nullable|string',
            'tujuan_konsultasi'      => 'nullable|string',
            'waktu_penyelesaian'     => 'nullable|string',
            'pengungkapan'           => 'nullable|string',
            'nomor_formulir'         => 'nullable|string',
            'tanggal_formulir'       => 'nullable|date',
            'header_image'           => 'nullable|image|max:2048',
            'footer_image'           => 'nullable|image|max:2048',
            'judul_dokumen'          => 'nullable|string',
            'auditee'                => 'nullable|string',
            'auditee_id'             => 'nullable|exists:users,id',
            'tgl_stg'                => 'nullable|date',
        ]);

        $headerImagePath = null;
        if ($request->hasFile('header_image')) {
            $headerImagePath = $request->file('header_image')->store('headers', 'public');
        }

        $footerImagePath = null;
        if ($request->hasFile('footer_image')) {
            $footerImagePath = $request->file('footer_image')->store('footers', 'public');
        }

        $selectedType = DocumentType::find($request->document_type_id);
        $status = ($selectedType && !$selectedType->requires_approval) ? 'approved' : 'draft';

        $document = Document::create([
            'title'                  => $request->title,
            'document_type_id'       => $request->document_type_id,
            'content'                => '',
            'created_by'             => Auth::id(),
            'status'                 => $status,
            'judul_dokumen'          => $request->judul_dokumen,
            'auditee'                => $request->auditee,
            'auditee_id'             => $request->auditee_id,
            'tgl_stg'                => $request->tgl_stg,
            'nama_kspi'              => $request->nama_kspi,
            'nama_pimpinan_klien'    => $request->nama_pimpinan_klien,
            'nama_jabatan'           => $request->nama_jabatan,
            'nip'                    => $request->nip,
            'bidang_tugas'           => $request->bidang_tugas,
            'deskripsi_permasalahan' => $request->deskripsi_permasalahan,
            'tujuan_konsultasi'      => $request->tujuan_konsultasi,
            'waktu_penyelesaian'     => $request->waktu_penyelesaian,
            'pengungkapan'           => $request->pengungkapan,
            'nomor_formulir'         => $request->nomor_formulir,
            'tanggal_formulir'       => $request->tanggal_formulir,
            'header_image'           => $headerImagePath,
            'footer_image'           => $footerImagePath,
        ]);

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
                'uploaded_by' => Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => 'Lampiran Dokumen',
                'pdf_path'    => $pdfPath,
            ]);
        }

        return redirect()->route('advisory.show', $document)
            ->with('success', 'Dokumen Advisory berhasil dibuat.');
    }

    public function show(Document $document)
    {
        $user = Auth::user();
        $document->load(['documentType', 'creator', 'attachments', 'approvals.approver']);

        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            if ($document->documentType->slug === 'lap-konsultasi') {
                abort(403, 'Anda tidak memiliki hak akses ke dokumen Laporan Konsultasi.');
            }
            if ($document->created_by !== $user->id && $document->auditee_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses ke dokumen ini.');
            }
        }

        if (Auth::check()) {
            Auth::user()->notifications()
                ->where('notifiable_type', Document::class)
                ->where('notifiable_id', $document->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return view('advisory.show', compact('document'));
    }

    public function exportPdf(Document $document)
    {
        $document->load(['documentType']);
        $typeSlug = $document->documentType?->slug;

        if ($typeSlug === 'bak') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('advisory.pdf.bak', compact('document'));
            $pdf->setPaper('A4', 'portrait');
            $safeTitle = str_replace(['/', '\\'], '-', $document->title ?? 'Draft');
            return $pdf->download('BAK-' . $safeTitle . '.pdf');
        }

        abort(404, 'PDF export not available for this document type yet.');
    }

    public function edit(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $documentTypes = DocumentType::where('module', 'advisory')->get();
        $auditeeManagers = \App\Models\User::role('manager')->get();
        return view('advisory.edit', compact('document', 'documentTypes', 'auditeeManagers'));
    }

    public function update(Request $request, Document $document)
    {
        $user = auth()->user();
        if (!$document->canBeEditedBy($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        if ($document->documentType?->slug === 'lap-konsultasi') {
            abort_if(!$user->hasRole(['admin', 'auditor', 'kspi']), 403, 'Hanya Admin, Auditor, dan KSPI yang dapat mengubah Laporan Konsultasi.');
        }

        $request->validate([
            'title'                  => 'nullable|string|max:255',
            'attachment'             => 'nullable|file|max:307200',
            'nama_kspi'              => 'nullable|string',
            'nama_pimpinan_klien'    => 'nullable|string',
            'nama_jabatan'           => 'nullable|string',
            'nip'                    => 'nullable|string',
            'bidang_tugas'           => 'nullable|string',
            'deskripsi_permasalahan' => 'nullable|string',
            'tujuan_konsultasi'      => 'nullable|string',
            'waktu_penyelesaian'     => 'nullable|string',
            'pengungkapan'           => 'nullable|string',
            'nomor_formulir'         => 'nullable|string',
            'tanggal_formulir'       => 'nullable|date',
            'header_image'           => 'nullable|image|max:2048',
            'footer_image'           => 'nullable|image|max:2048',
            'judul_dokumen'          => 'nullable|string',
            'auditee'                => 'nullable|string',
            'auditee_id'             => 'nullable|exists:users,id',
            'tgl_stg'                => 'nullable|date',
        ]);

        $updateData = [];
        if ($request->has('title')) {
            $updateData['title'] = $request->title;
        }
        
        $fields = ['nama_jabatan', 'nip', 'bidang_tugas', 'deskripsi_permasalahan', 'tujuan_konsultasi', 'waktu_penyelesaian', 'pengungkapan', 'nomor_formulir', 'tanggal_formulir', 'judul_dokumen', 'auditee', 'auditee_id', 'tgl_stg', 'nama_kspi', 'nama_pimpinan_klien'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }
        
        if ($request->hasFile('header_image')) {
            $updateData['header_image'] = $request->file('header_image')->store('headers', 'public');
        }

        if ($request->hasFile('footer_image')) {
            $updateData['footer_image'] = $request->file('footer_image')->store('footers', 'public');
        }
        
        if (!empty($updateData)) {
            $document->update($updateData);
        }

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

            // If a new attachment is uploaded, we just append it, or we could delete the old one.
            // But since this replaces the "content", usually there is just one primary attachment.
            // Let's just create a new attachment record.
            $document->attachments()->create([
                'uploaded_by' => Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => 'Lampiran Dokumen',
                'pdf_path'    => $pdfPath,
            ]);
        }

        return redirect()->route('advisory.show', $document)->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        if (!$document->canBeDeletedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus dokumen ini.');
        }

        $document->delete();

        return redirect()->route('advisory.index')->with('success', 'Dokumen berhasil dihapus.');
    }
}
