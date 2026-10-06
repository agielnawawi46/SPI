<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtherActivityController extends Controller
{
    // ── Monitoring Tindak Lanjut ──────────────────────────────────────

    public function tindakLanjutIndex(Request $request)
    {
        $user = Auth::user();
        $query = Document::with(['documentType', 'creator'])
            ->whereHas('documentType', function($q) {
                $q->where('slug', 'monitoring-tl');
            });

        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $deptId = $user->department_id;
            abort_if(!$deptId, 403, 'Akun Anda belum ditetapkan ke departemen. Hubungi administrator.');
            $query->where('status', 'approved')
                  ->whereHas('followUps', function($q) use ($deptId) {
                      $q->where('auditee_department_id', $deptId);
                  });
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "{$search}%");
        }

        $documents = $query->latest()->paginate(10, ['*'], 'doc_page');
        
        // Fetch follow ups for the lower table
        $followUpQuery = \App\Models\FollowUp::with('document')->withCount('evidences');
        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $followUpQuery->where(function ($q) use ($user) {
                $q->whereHas('document', function ($sq) use ($user) {
                    $sq->where('created_by', $user->id)
                       ->orWhere('auditee_id', $user->id);
                })
                  ->orWhere('auditee_department_id', $user->department_id)
                  ->orWhere('assigned_to', $user->id);
            });
        }
        
        if ($tlSearch = $request->get('tl_search')) {
            $followUpQuery->where(function($q) use ($tlSearch) {
                $q->where('finding', 'like', "%{$tlSearch}%")
                  ->orWhereHas('assignee', function($sq) use ($tlSearch) {
                      $sq->where('name', 'like', "%{$tlSearch}%");
                  })
                  ->orWhereHas('document', function($sq) use ($tlSearch) {
                      $sq->where('title', 'like', "%{$tlSearch}%");
                  });
            });
        }

        $followUps = $followUpQuery->latest()->paginate(10, ['*'], 'tl_page');
            
        return view('other-activities.tindak-lanjut.index', compact('documents', 'followUps'));
    }

    public function tindakLanjutCreate(Request $request)
    {
        $documentType = DocumentType::where('slug', 'monitoring-tl')->first();
        if (!$documentType) {
            abort(404, 'Tipe dokumen Monitoring Tindak Lanjut belum diatur.');
        }

        $users = \App\Models\User::orderBy('name')->get();

        return view('other-activities.tindak-lanjut.create', compact('documentType', 'users'));
    }

    public function tindakLanjutStore(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'judul_dokumen'    => 'required|string',
            'header_text'      => 'required|string',
            'deskripsi_pembuka'=> 'nullable|string',
            'periode_audit'    => 'nullable|date',
            'document_type_id' => 'required|exists:document_types,id',
            'nomor_formulir'   => 'nullable|string',
            'tanggal_formulir' => 'nullable|date',
            'header_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'items'            => 'required|array',
            'bidang_tugas'     => 'nullable|string',
            'disusun_oleh'     => 'nullable|string',
            'nama_jabatan'     => 'nullable|string',
            'nama_kspi'        => 'nullable|string',
        ]);

        $dataToStore = [
            'title'            => $request->title,
            'judul_dokumen'    => $request->judul_dokumen,
            'header_text'      => $request->header_text,
            'deskripsi_pembuka'=> $request->deskripsi_pembuka,
            'periode_audit'    => $request->periode_audit,
            'document_type_id' => $request->document_type_id,
            'nomor_formulir'   => $request->nomor_formulir,
            'tanggal_formulir' => $request->tanggal_formulir,
            'content'          => json_encode($request->items), // Store rows as JSON
            'bidang_tugas'     => $request->bidang_tugas,
            'disusun_oleh'     => $request->disusun_oleh,
            'nama_jabatan'     => $request->nama_jabatan,
            'nama_kspi'        => $request->nama_kspi,
            'created_by'       => Auth::id(),
            'status'           => 'draft',
        ];

        if ($request->hasFile('header_image')) {
            $dataToStore['header_image'] = $request->file('header_image')->store('documents/headers', 'public');
        }

        $document = Document::create($dataToStore);

        return redirect()->route('other-activities.tl.index')
            ->with('success', 'Monitoring Tindak Lanjut berhasil dibuat.');
    }

    public function tindakLanjutShow(Document $document)
    {
        if (Auth::check()) {
            Auth::user()->notifications()
                ->where('notifiable_type', Document::class)
                ->where('notifiable_id', $document->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $document->load(['documentType', 'creator', 'approvals.approver']);
        return view('other-activities.tindak-lanjut.show', compact('document'));
    }

    public function tindakLanjutEdit(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $users = \App\Models\User::orderBy('name')->get();

        return view('other-activities.tindak-lanjut.edit', compact('document', 'users'));
    }

    public function tindakLanjutUpdate(Request $request, Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $request->validate([
            'title'            => 'required|string|max:255',
            'judul_dokumen'    => 'required|string',
            'header_text'      => 'required|string',
            'deskripsi_pembuka'=> 'nullable|string',
            'periode_audit'    => 'nullable|date',
            'nomor_formulir'   => 'nullable|string',
            'tanggal_formulir' => 'nullable|date',
            'header_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'items'            => 'required|array',
            'bidang_tugas'     => 'nullable|string',
            'disusun_oleh'     => 'nullable|string',
            'nama_jabatan'     => 'nullable|string',
            'nama_kspi'        => 'nullable|string',
        ]);

        $dataToUpdate = [
            'title'            => $request->title,
            'judul_dokumen'    => $request->judul_dokumen,
            'header_text'      => $request->header_text,
            'deskripsi_pembuka'=> $request->deskripsi_pembuka,
            'periode_audit'    => $request->periode_audit,
            'nomor_formulir'   => $request->nomor_formulir,
            'tanggal_formulir' => $request->tanggal_formulir,
            'content'          => json_encode($request->items),
            'bidang_tugas'     => $request->bidang_tugas,
            'disusun_oleh'     => $request->disusun_oleh,
            'nama_jabatan'     => $request->nama_jabatan,
            'nama_kspi'        => $request->nama_kspi,
        ];

        if ($request->hasFile('header_image')) {
            // Hapus gambar lama jika ada
            if ($document->header_image && \Storage::disk('public')->exists($document->header_image)) {
                \Storage::disk('public')->delete($document->header_image);
            }
            $dataToUpdate['header_image'] = $request->file('header_image')->store('documents/headers', 'public');
        }

        $document->update($dataToUpdate);

        return redirect()->route('other-activities.tl.show', $document)
            ->with('success', 'Monitoring Tindak Lanjut berhasil diperbarui.');
    }

    public function tindakLanjutDestroy(Document $document)
    {
        if (!$document->canBeDeletedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus dokumen ini.');
        }

        $document->delete();
        return redirect()->route('other-activities.tl.index')->with('success', 'Monitoring Tindak Lanjut berhasil dihapus.');
    }

    public function tindakLanjutDestroyFollowUp(FollowUp $followUp)
    {
        $followUp->delete();
        return redirect()->back()->with('success', 'Tindak Lanjut berhasil dihapus.');
    }

    public function tindakLanjutPdf(Document $document)
    {
        $document->load(['documentType']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('other-activities.tindak-lanjut.pdf', compact('document'))
                ->setPaper('a4', 'landscape');
        
        $safeTitle = str_replace(['/', '\\'], '-', $document->title ?? 'Draft');
        return $pdf->download('Monitoring_Tindak_Lanjut_' . $safeTitle . '.pdf');
    }

    // ── Pendamping Auditor Eksternal ──────────────────────────────────

    public function pendampingIndex(Request $request)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        $typeSlug = $request->get('type');
        $search = $request->get('search');
        
        $query = Document::with(['documentType', 'creator'])
            ->whereHas('documentType', fn($q) => $q->where('module', 'pendamping'));
            
        if ($typeSlug) {
            $query->whereHas('documentType', fn($q) => $q->where('slug', $typeSlug));
        }

        if ($search) {
            $query->where('title', 'like', "{$search}%");
        }
            
        $documents = $query->latest()->paginate(10);
        
        $documentType = null;
        if ($typeSlug) {
            $documentType = DocumentType::where('slug', $typeSlug)->first();
        }

        return view('other-activities.pendamping.index', compact('documents', 'documentType', 'typeSlug'));
    }

    public function pendampingCreate(Request $request)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        $documentTypes = DocumentType::where('module', 'pendamping')->get();
        $typeSlug = $request->get('type');
        $selectedType = $typeSlug ? DocumentType::where('slug', $typeSlug)->first() : null;
        
        return view('other-activities.pendamping.create', compact('documentTypes', 'selectedType'));
    }

    public function pendampingStore(Request $request)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        $request->validate([
            'title'            => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'content'          => 'nullable|string',
            'attachment'       => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
        ]);

        $selectedType = DocumentType::find($request->document_type_id);

        $document = Document::create([
            'title'            => $request->title,
            'document_type_id' => $request->document_type_id,
            'content'          => $request->content ?? '',
            'created_by'       => Auth::id(),
            'status'           => ($selectedType && !$selectedType->requires_approval) ? 'approved' : 'draft',
        ]);

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

        return redirect()->route('other-activities.pendamping.index')
            ->with('success', 'Dokumen dan lampiran berhasil diunggah.');
    }

    public function pendampingEdit(Document $document)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');
        
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }
        $documentTypes = DocumentType::where('module', 'pendamping')->get();
        return view('other-activities.pendamping.edit', compact('document', 'documentTypes'));
    }

    public function pendampingUpdate(Request $request, Document $document)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
        ]);

        $document->update([
            'title'   => $request->title,
            'content' => $request->content ?? ''
        ]);

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

        return redirect()->route('other-activities.pendamping.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function pendampingDestroy(Document $document)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        if (!$document->canBeDeletedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus dokumen ini.');
        }

        $document->delete();

        return redirect()->route('other-activities.pendamping.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    // ==========================================
    // 3. Quality Assurance (QA)
    // ==========================================

    // QA upload-only types (no slide-entry in QA)
    const QA_UPLOAD_ONLY_TYPES = ['pk-qa', 'kk-qa', 'lap-qa'];

    public function qaIndex(Request $request)
    {
        $typeSlug = $request->get('type');
        $search   = $request->get('search');

        $query = Document::with(['documentType', 'creator'])
            ->whereHas('documentType', fn($q) => $q->where('module', 'qa'));

        if ($typeSlug) {
            $query->whereHas('documentType', fn($q) => $q->where('slug', $typeSlug));
        }

        if ($search) {
            $query->where('title', 'like', "{$search}%");
        }

        $documents = $query->latest()->paginate(10);

        $documentType = null;
        if ($typeSlug) {
            $documentType = DocumentType::where('slug', $typeSlug)->first();
        }

        $documentTypes = DocumentType::where('module', 'qa')->orderBy('sort_order')->get();

        return view('other-activities.qa.index', compact('documents', 'documentType', 'typeSlug', 'documentTypes'));
    }

    public function qaCreate(Request $request)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        $documentTypes = DocumentType::where('module', 'qa')->orderBy('sort_order')->get();
        $typeSlug      = $request->get('type');
        $selectedType  = $typeSlug ? DocumentType::where('slug', $typeSlug)->first() : null;

        // For upload-only types use simple upload view
        if ($typeSlug && in_array($typeSlug, self::QA_UPLOAD_ONLY_TYPES)) {
            return view('other-activities.qa.create-upload', compact('documentTypes', 'selectedType'));
        }

        return view('other-activities.qa.create', compact('documentTypes', 'selectedType'));
    }

    public function qaStore(Request $request)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        $documentTypeId = $request->get('document_type_id');
        $selectedType   = DocumentType::find($documentTypeId);
        $typeSlug       = $selectedType ? $selectedType->slug : null;

        // Upload-only types
        if ($typeSlug && in_array($typeSlug, self::QA_UPLOAD_ONLY_TYPES)) {
            $request->validate([
                'title'            => 'required|string|max:255',
                'document_type_id' => 'required|exists:document_types,id',
                'attachment'       => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
            ]);

            $document = Document::create([
                'title'            => $request->title,
                'document_type_id' => $request->document_type_id,
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

            return redirect()->route('other-activities.qa.show', $document)
                ->with('success', ($selectedType->name ?? 'Dokumen') . ' berhasil diunggah.');
        }

        // Surat Tugas QA — full form
        $request->validate([
            'title'            => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'tgl_stg'          => 'nullable|date',
            'auditee'          => 'nullable|string|max:255',
            'jadwal_audit'     => 'nullable|string|max:255',
            'lingkup_audit'    => 'nullable|string',
            'tujuan_audit'     => 'nullable|string',
            'periode_audit'    => 'nullable|string|max:255',
            'header_text'      => 'nullable|string',
            'judul_dokumen'    => 'nullable|string|max:255',
            'tanggal_formulir' => 'nullable|date',
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
            'status'           => ($selectedType && !$selectedType->requires_approval) ? 'approved' : 'draft',
            'created_by'       => Auth::id(),
            'tgl_stg'          => $request->tgl_stg,
            'auditee'          => $request->auditee,
            'jadwal_audit'     => $request->jadwal_audit,
            'lingkup_audit'    => $request->lingkup_audit,
            'tujuan_audit'     => $request->tujuan_audit,
            'periode_audit'    => $request->periode_audit,
            'judul_dokumen'    => $request->judul_dokumen,
            'header_text'      => $request->header_text,
            'tanggal_formulir' => $request->tanggal_formulir,
            'deskripsi_pembuka'=> $request->deskripsi_pembuka,
            'deskripsi_penutup'=> $request->deskripsi_penutup,
            'tim_audit'        => $request->tim_audit,
            'jabatan_direktur' => $request->jabatan_direktur,
            'nama_direktur'    => $request->nama_direktur,
        ];

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
                    $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
                    $pdfWriter->save(storage_path('app/public/' . $pdfRelativePath));
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

        return redirect()->route('other-activities.qa.show', $document)
            ->with('success', 'Dokumen QA berhasil dibuat.');
    }

    public function qaShow(Document $document)
    {
        if (Auth::check()) {
            Auth::user()->notifications()
                ->where('notifiable_type', Document::class)
                ->where('notifiable_id', $document->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $document->load(['documentType', 'creator', 'attachments', 'approvals.approver']);
        return view('other-activities.qa.show', compact('document'));
    }

    public function qaEdit(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }
        $documentTypes = DocumentType::where('module', 'qa')->orderBy('sort_order')->get();
        return view('other-activities.qa.edit', compact('document', 'documentTypes'));
    }

    public function qaUpdate(Request $request, Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $request->validate([
            'title'            => 'nullable|string|max:255',
            'content'          => 'nullable|string',
            'attachment'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
            'tgl_stg'          => 'nullable|date',
            'auditee'          => 'nullable|string|max:255',
            'jadwal_audit'     => 'nullable|string|max:255',
            'lingkup_audit'    => 'nullable|string',
            'tujuan_audit'     => 'nullable|string',
            'periode_audit'    => 'nullable|string|max:255',
            'header_text'      => 'nullable|string',
            'judul_dokumen'    => 'nullable|string|max:255',
            'tanggal_formulir' => 'nullable|date',
            'deskripsi_pembuka'=> 'nullable|string',
            'deskripsi_penutup'=> 'nullable|string',
            'tim_audit'        => 'nullable|array',
            'jabatan_direktur' => 'nullable|string|max:255',
            'nama_direktur'    => 'nullable|string|max:255',
        ]);

        $dataToUpdate = [];
        if ($request->has('title'))             $dataToUpdate['title']             = $request->title;
        if ($request->has('content'))           $dataToUpdate['content']           = $request->content;
        if ($request->has('tgl_stg'))           $dataToUpdate['tgl_stg']           = $request->tgl_stg;
        if ($request->has('auditee'))           $dataToUpdate['auditee']           = $request->auditee;
        if ($request->has('jadwal_audit'))      $dataToUpdate['jadwal_audit']      = $request->jadwal_audit;
        if ($request->has('lingkup_audit'))     $dataToUpdate['lingkup_audit']     = $request->lingkup_audit;
        if ($request->has('tujuan_audit'))      $dataToUpdate['tujuan_audit']      = $request->tujuan_audit;
        if ($request->has('periode_audit'))     $dataToUpdate['periode_audit']     = $request->periode_audit;
        if ($request->has('judul_dokumen'))     $dataToUpdate['judul_dokumen']     = $request->judul_dokumen;
        if ($request->has('header_text'))       $dataToUpdate['header_text']       = $request->header_text;
        if ($request->has('tanggal_formulir'))  $dataToUpdate['tanggal_formulir']  = $request->tanggal_formulir;
        if ($request->has('deskripsi_pembuka')) $dataToUpdate['deskripsi_pembuka'] = $request->deskripsi_pembuka;
        if ($request->has('deskripsi_penutup')) $dataToUpdate['deskripsi_penutup'] = $request->deskripsi_penutup;
        if ($request->has('tim_audit'))         $dataToUpdate['tim_audit']         = $request->tim_audit;
        if ($request->has('jabatan_direktur'))  $dataToUpdate['jabatan_direktur']  = $request->jabatan_direktur;
        if ($request->has('nama_direktur'))     $dataToUpdate['nama_direktur']     = $request->nama_direktur;

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

        return redirect()->route('other-activities.qa.show', $document)
            ->with('success', 'Dokumen QA berhasil diperbarui.');
    }

    public function qaDestroy(Document $document)
    {
        abort_if(!Auth::user()->hasRole(['admin', 'auditor', 'kspi']), 403, 'Anda tidak memiliki hak akses ke halaman ini.');

        if (!$document->canBeDeletedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus dokumen ini.');
        }

        $document->delete();
        return redirect()->route('other-activities.qa.index', ['type' => $document->documentType->slug ?? ''])
            ->with('success', 'Dokumen QA berhasil dihapus.');
    }

    public function qaExportPdf(Document $document)
    {
        $document->load(['documentType']);
        $typeSlug = $document->documentType?->slug;

        if ($typeSlug === 'st-qa') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('assurance.pdf.stg', compact('document'));
            $pdf->setPaper('A4', 'portrait');
            $safeTitle = str_replace(['/', '\\'], '-', $document->title ?? 'Draft');
            return $pdf->download('STQ-' . $safeTitle . '.pdf');
        }

        abort(404, 'PDF export not available for this document type.');
    }
}
