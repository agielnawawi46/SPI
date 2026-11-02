<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pkpt;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PkptController extends Controller
{
    // ==========================================
    // PKPT Utama Helper
    // ==========================================

    protected function getOrCreatePkptDocument(Pkpt $pkpt): Document
    {
        $docType = DocumentType::firstOrCreate(
            ['slug' => 'pkpt'],
            [
                'name' => 'PKPT',
                'module' => 'pkpt',
                'sort_order' => 1,
                'requires_approval' => false,
                'is_active' => true,
            ]
        );

        $document = Document::firstOrCreate(
            [
                'pkpt_id' => $pkpt->id,
                'document_type_id' => $docType->id,
            ],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'title' => 'Dokumen PKPT: ' . $pkpt->title,
                'created_by' => $pkpt->created_by ?? Auth::id() ?? 1,
                'status' => 'approved',
                'content' => '',
            ]
        );

        // Migrate legacy single file into document_attachments if none exist yet
        if ($pkpt->file_path && $document->attachments()->count() === 0) {
            $document->attachments()->create([
                'uploaded_by' => $pkpt->created_by ?? Auth::id() ?? 1,
                'file_name'   => $pkpt->file_name ?? basename($pkpt->file_path),
                'file_path'   => $pkpt->file_path,
                'file_type'   => $pkpt->file_type ?? 'application/pdf',
                'file_size'   => $pkpt->file_size ?? 0,
                'description' => 'Dokumen PKPT',
            ]);
        }

        return $document;
    }

    // ==========================================
    // PKPT Utama
    // ==========================================

    public function index(Request $request)
    {
        $pkptDocType = DocumentType::where('slug', 'pkpt')->first();

        // Sinkronisasi otomatis: Jika berkas PKPT kosong tapi statusnya bukan draft, ubah menjadi draft
        $emptyPkpts = Pkpt::where('status', '!=', 'draft')
            ->where(function ($q) {
                $q->whereNull('file_path')->orWhere('file_path', '');
            })
            ->whereDoesntHave('documents', function ($q) use ($pkptDocType) {
                if ($pkptDocType) {
                    $q->where('document_type_id', $pkptDocType->id)->has('attachments');
                }
            })
            ->get();

        foreach ($emptyPkpts as $ep) {
            $ep->update(['status' => 'draft']);
            if ($pkptDocType) {
                Document::where('pkpt_id', $ep->id)
                    ->where('document_type_id', $pkptDocType->id)
                    ->update(['status' => 'draft']);
            }
        }

        $query = Pkpt::with(['creator', 'documents' => function($q) use ($pkptDocType) {
            if ($pkptDocType) {
                $q->where('document_type_id', $pkptDocType->id)->with('attachments');
            }
        }]);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "{$search}%")
                  ->orWhere('period_year', 'like', "{$search}%");
        }

        $pkpts = $query->latest()->paginate(10);
        return view('admin.pkpt.index', compact('pkpts'));
    }

    public function create()
    {
        return view('admin.pkpt.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'period_year'   => 'required|digits:4|integer',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx',
            'pkpt_file'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx',
        ]);

        $rawFiles = $request->file('attachments') ?? $request->file('pkpt_file');
        if (empty($rawFiles)) {
            return back()->withErrors(['attachments' => 'Wajib mengunggah minimal satu berkas dokumen PKPT.'])->withInput();
        }

        $pkpt = new Pkpt($request->only('title', 'period_year'));
        $pkpt->created_by = Auth::id();
        $pkpt->status = 'approved';
        $pkpt->save();

        $document = $this->getOrCreatePkptDocument($pkpt);

        $files = is_array($rawFiles) ? $rawFiles : [$rawFiles];
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;
            $originalName = $file->getClientOriginalName();
            $uniqueId = uniqid();
            $fileName = time() . '_' . $uniqueId . '_' . $originalName;
            $filePath = $file->storeAs('pkpt_files', $fileName, 'public');

            $document->attachments()->create([
                'uploaded_by' => Auth::id() ?? 1,
                'file_name'   => $originalName,
                'file_path'   => $filePath,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => 'Dokumen PKPT',
            ]);
        }

        $latestAtt = $document->attachments()->latest()->first();
        if ($latestAtt) {
            $pkpt->file_name = $latestAtt->file_name;
            $pkpt->file_path = $latestAtt->file_path;
            $pkpt->file_size = $latestAtt->file_size;
            $pkpt->file_type = $latestAtt->file_type;
            $pkpt->save();
        }

        return redirect()->route('pkpt.show', $pkpt)->with('success', 'Dokumen PKPT berhasil diunggah.');
    }

    public function edit(Pkpt $pkpt)
    {
        $document = $this->getOrCreatePkptDocument($pkpt);
        $document->load('attachments.uploader');
        $attachments = $document->attachments;

        return view('admin.pkpt.edit', compact('pkpt', 'document', 'attachments'));
    }

    public function update(Request $request, Pkpt $pkpt)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'period_year'   => 'required|digits:4|integer',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx',
            'pkpt_file'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx',
        ]);

        $pkpt->title = $request->title;
        $pkpt->period_year = $request->period_year;
        $pkpt->save();

        $document = $this->getOrCreatePkptDocument($pkpt);

        $rawFiles = $request->file('attachments') ?? $request->file('pkpt_file');
        if ($rawFiles) {
            $files = is_array($rawFiles) ? $rawFiles : [$rawFiles];
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;
                $originalName = $file->getClientOriginalName();
                $uniqueId = uniqid();
                $fileName = time() . '_' . $uniqueId . '_' . $originalName;
                $filePath = $file->storeAs('pkpt_files', $fileName, 'public');

                $document->attachments()->create([
                    'uploaded_by' => Auth::id() ?? 1,
                    'file_name'   => $originalName,
                    'file_path'   => $filePath,
                    'file_type'   => $file->getClientMimeType(),
                    'file_size'   => $file->getSize(),
                    'description' => 'Dokumen PKPT',
                ]);
            }

            $latestAtt = $document->attachments()->latest()->first();
            if ($latestAtt) {
                $pkpt->file_name = $latestAtt->file_name;
                $pkpt->file_path = $latestAtt->file_path;
                $pkpt->file_size = $latestAtt->file_size;
                $pkpt->file_type = $latestAtt->file_type;
                // Jika sebelumnya draft karena file kosong dan sekarang ada file diunggah, kembalikan ke approved (Closed)
                if ($pkpt->status === 'draft') {
                    $pkpt->status = 'approved';
                    $document->status = 'approved';
                    $document->save();
                }
            } else {
                $pkpt->file_name = null;
                $pkpt->file_path = null;
                $pkpt->file_size = 0;
                $pkpt->file_type = null;
                $pkpt->status = 'draft';
                $document->status = 'draft';
                $document->save();
            }
            $pkpt->save();
        } else {
            // Jika tidak ada upload file baru dan berkas kosong, pastikan status draft
            if ($document->attachments()->count() === 0 && empty($pkpt->file_path)) {
                $pkpt->file_name = null;
                $pkpt->file_path = null;
                $pkpt->file_size = 0;
                $pkpt->file_type = null;
                $pkpt->status = 'draft';
                $document->status = 'draft';
                $document->save();
                $pkpt->save();
            }
        }

        return redirect()->route('pkpt.show', $pkpt)->with('success', 'PKPT berhasil diperbarui.');
    }

    public function show(Pkpt $pkpt)
    {
        $document = $this->getOrCreatePkptDocument($pkpt);
        $document->load('attachments.uploader');
        $attachments = $document->attachments;

        return view('admin.pkpt.show', compact('pkpt', 'document', 'attachments'));
    }

    public function destroyAttachment(Pkpt $pkpt, DocumentAttachment $attachment)
    {
        $document = $this->getOrCreatePkptDocument($pkpt);
        if ($attachment->document_id !== $document->id && $attachment->document?->pkpt_id !== $pkpt->id) {
            abort(404);
        }

        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        if ($attachment->pdf_path && Storage::disk('public')->exists($attachment->pdf_path)) {
            Storage::disk('public')->delete($attachment->pdf_path);
        }

        $attachment->delete();

        $latestAtt = $document->attachments()->latest()->first();
        if ($latestAtt) {
            $pkpt->file_name = $latestAtt->file_name;
            $pkpt->file_path = $latestAtt->file_path;
            $pkpt->file_size = $latestAtt->file_size;
            $pkpt->file_type = $latestAtt->file_type;
        } else {
            $pkpt->file_name = null;
            $pkpt->file_path = null;
            $pkpt->file_size = 0;
            $pkpt->file_type = null;
            // Jika semua file sudah terhapus, kembalikan status PKPT dan Dokumen ke Draft
            $pkpt->status = 'draft';
            $document->status = 'draft';
            $document->save();
        }
        $pkpt->save();

        return redirect()->back()->with('success', 'Berkas berhasil dihapus.');
    }

    public function destroy(Pkpt $pkpt)
    {
        $document = $this->getOrCreatePkptDocument($pkpt);
        foreach ($document->attachments as $att) {
            if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                Storage::disk('public')->delete($att->file_path);
            }
        }
        $document->attachments()->delete();
        $document->delete();

        if ($pkpt->file_path && Storage::disk('public')->exists($pkpt->file_path)) {
            Storage::disk('public')->delete($pkpt->file_path);
        }
        
        $pkpt->delete();
        return redirect()->route('pkpt.index')->with('success', 'PKPT berhasil dihapus.');
    }

    public function viewPkptFile(Pkpt $pkpt, ?DocumentAttachment $attachment = null)
    {
        if ($attachment) {
            $hasPdf = $attachment->pdf_path && Storage::disk('public')->exists($attachment->pdf_path);
            $filePath = $hasPdf ? $attachment->pdf_path : $attachment->file_path;
            $fileName = $hasPdf ? pathinfo($attachment->file_name, PATHINFO_FILENAME) . '.pdf' : $attachment->file_name;
            $mimeType = $hasPdf ? 'application/pdf' : ($attachment->file_type ?? Storage::disk('public')->mimeType($filePath) ?? 'application/octet-stream');
        } else {
            $document = $this->getOrCreatePkptDocument($pkpt);
            $attachments = $document->attachments()->get();

            // Jika ada lebih dari 1 file, arahkan ke halaman show agar pengguna bisa memilih file
            if ($attachments->count() > 1) {
                return redirect()->route('pkpt.show', $pkpt);
            }

            if ($attachments->count() === 1) {
                $first = $attachments->first();
                $hasPdf = $first->pdf_path && Storage::disk('public')->exists($first->pdf_path);
                $filePath = $hasPdf ? $first->pdf_path : $first->file_path;
                $fileName = $hasPdf ? pathinfo($first->file_name, PATHINFO_FILENAME) . '.pdf' : $first->file_name;
                $mimeType = $hasPdf ? 'application/pdf' : ($first->file_type ?? Storage::disk('public')->mimeType($filePath) ?? 'application/octet-stream');
            } else {
                $hasPdf = false;
                $filePath = $pkpt->file_path;
                $fileName = $pkpt->file_name;
                $mimeType = $pkpt->file_type ?? 'application/octet-stream';
            }
        }

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return redirect()->back()->with('error', 'File fisik tidak ditemukan di server.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $isInline = $hasPdf || in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']);

        return Storage::disk('public')->response(
            $filePath, 
            $fileName,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => ($isInline ? 'inline' : 'attachment') . '; filename="'.addslashes($fileName).'"'
            ]
        );
    }

    public function downloadPkptFile(Pkpt $pkpt, ?DocumentAttachment $attachment = null)
    {
        // 1. Jika unduh berkas spesifik
        if ($attachment) {
            if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
                return redirect()->back()->with('error', 'File fisik tidak ditemukan di server.');
            }
            return Storage::disk('public')->download(
                $attachment->file_path, 
                $attachment->file_name
            );
        }

        // 2. Ambil berkas-berkas PKPT ini
        $document = $this->getOrCreatePkptDocument($pkpt);
        $attachments = $document->attachments()->get();

        // Jika tidak ada attachments, fallback ke pkpt->file_path
        if ($attachments->isEmpty()) {
            if ($pkpt->file_path && Storage::disk('public')->exists($pkpt->file_path)) {
                return Storage::disk('public')->download($pkpt->file_path, $pkpt->file_name);
            }
            return redirect()->back()->with('error', 'Belum ada berkas untuk diunduh.');
        }

        // Jika hanya 1 berkas: langsung unduh file tersebut
        if ($attachments->count() === 1) {
            $single = $attachments->first();
            if ($single->file_path && Storage::disk('public')->exists($single->file_path)) {
                return Storage::disk('public')->download($single->file_path, $single->file_name);
            }
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        // Jika lebih dari 1 berkas: kompres ke dalam file ZIP
        $cleanTitle = \Illuminate\Support\Str::slug($pkpt->title) ?: 'PKPT';
        $zipFileName = 'PKPT_' . $pkpt->period_year . '_' . $cleanTitle . '_Semua_Dokumen.zip';
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/' . uniqid('zip_') . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $usedNames = [];
            $addedCount = 0;

            foreach ($attachments as $att) {
                if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                    $realPath = storage_path('app/public/' . $att->file_path);
                    if (file_exists($realPath)) {
                        $name = $att->file_name;
                        if (in_array($name, $usedNames)) {
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            $base = pathinfo($name, PATHINFO_FILENAME);
                            $name = $base . '_' . ($addedCount + 1) . ($ext ? '.' . $ext : '');
                        }
                        $usedNames[] = $name;
                        $zip->addFile($realPath, $name);
                        $addedCount++;
                    }
                }
            }
            $zip->close();

            if ($addedCount > 0 && file_exists($zipPath)) {
                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            }
        }

        return redirect()->back()->with('error', 'Gagal membuat arsip berkas zip.');
    }


    // ==========================================
    // Kertas Kerja PKPT (6 File)
    // ==========================================

    public function kertasKerjaIndex(Request $request)
    {
        $documentType = \App\Models\DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();

        // Sinkronisasi otomatis: Jika berkas Kertas Kerja PKPT kosong tetapi statusnya bukan draft, kembalikan ke draft
        Document::where('document_type_id', $documentType->id)
            ->where('status', '!=', 'draft')
            ->doesntHave('attachments')
            ->update(['status' => 'draft']);
        
        $query = \App\Models\Document::with(['pkpt', 'creator'])->withCount('attachments')
            ->where('document_type_id', $documentType->id)
            ->whereHas('pkpt'); // Pastikan PKPT-nya tidak dihapus (soft-deleted)

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('pkpt', function($q) use ($search) {
                $q->where('title', 'like', "{$search}%")
                  ->orWhere('period_year', 'like', "{$search}%");
            });
        }

        $documents = $query->latest()->paginate(10);
        
        // Ambil PKPT yang belum dibuat Kertas Kerjanya
        $existingPkptIds = \App\Models\Document::where('document_type_id', $documentType->id)->pluck('pkpt_id');
        $availablePkpts = Pkpt::whereNotIn('id', $existingPkptIds)->latest()->get();

        return view('admin.pkpt.kertas-kerja-index', compact('documents', 'availablePkpts'));
    }

    public function kertasKerja(Pkpt $pkpt)
    {
        // Pastikan document abstraction untuk Kertas Kerja tersedia
        $documentType = \App\Models\DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();
        $document = \App\Models\Document::firstOrCreate(
            [
                'pkpt_id' => $pkpt->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'title' => 'Kertas Kerja PKPT: ' . $pkpt->title,
                'created_by' => Auth::id(),
                'status' => 'draft',
                'content' => '',
            ]
        );

        $document->load('attachments.uploader');

        return view('admin.pkpt.kertas-kerja', compact('pkpt', 'document'));
    }

    public function storeKertasKerja(Request $request, Pkpt $pkpt)
    {
        $documentType = \App\Models\DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();
        $document = \App\Models\Document::firstOrCreate(
            [
                'pkpt_id' => $pkpt->id,
                'document_type_id' => $documentType->id,
            ],
            [
                'title' => 'Kertas Kerja PKPT: ' . $pkpt->title,
                'created_by' => Auth::id(),
                'status' => 'draft',
                'content' => '',
            ]
        );

        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $uploadedCount = 0;

        // Mode 1: Submit per-category (category field + attachments[] or attachment)
        if ($request->filled('category')) {
            $category = $request->input('category');
            $rawFiles = $request->file('attachments') ?? $request->file('attachment');
            $fileList = is_array($rawFiles) ? $rawFiles : ($rawFiles ? [$rawFiles] : []);

            foreach ($fileList as $file) {
                if (!$file || !($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                    continue;
                }

                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $uniqueId = uniqid();
                $fileName = time() . '_' . $uniqueId . '_' . $originalName;
                $filePath = $file->storeAs('attachments', $fileName, 'public');

                $document->attachments()->create([
                    'uploaded_by' => Auth::id(),
                    'description' => $category,
                    'file_name'   => $originalName,
                    'file_path'   => $filePath,
                    'file_type'   => $file->getClientMimeType(),
                    'file_size'   => $file->getSize(),
                    'pdf_path'    => null,
                ]);

                $uploadedCount++;
            }
        }

        // Mode 2: Multi-category submission (attachments[category][])
        $rawAttachments = $request->file('attachments');
        if (is_array($rawAttachments)) {
            foreach ($rawAttachments as $category => $files) {
                if (is_numeric($category)) {
                    continue; // Skip numeric keys handled in Mode 1
                }

                $fileList = is_array($files) ? $files : [$files];
                foreach ($fileList as $file) {
                    if (!$file || !($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                        continue;
                    }

                    $extension = strtolower($file->getClientOriginalExtension());
                    if (!in_array($extension, $allowedExtensions)) {
                        continue;
                    }

                    $originalName = $file->getClientOriginalName();
                    $uniqueId = uniqid();
                    $fileName = time() . '_' . $uniqueId . '_' . $originalName;
                    $filePath = $file->storeAs('attachments', $fileName, 'public');

                    $document->attachments()->create([
                        'uploaded_by' => Auth::id(),
                        'description' => $category,
                        'file_name'   => $originalName,
                        'file_path'   => $filePath,
                        'file_type'   => $file->getClientMimeType(),
                        'file_size'   => $file->getSize(),
                        'pdf_path'    => null,
                    ]);

                    $uploadedCount++;
                }
            }
        }

        if ($uploadedCount > 0) {
            $msg = $uploadedCount > 1 
                ? "{$uploadedCount} berkas Kertas Kerja berhasil ditambahkan."
                : 'Berkas Kertas Kerja berhasil ditambahkan.';
            return redirect()->route('pkpt.kertas-kerja', $pkpt)->with('success', $msg);
        }

        return redirect()->route('pkpt.kertas-kerja', $pkpt)->with('error', 'Tidak ada berkas valid yang diunggah. Pastikan format file PDF, Word, atau Excel.');
    }

    public function destroyKertasKerjaAttachment(Pkpt $pkpt, DocumentAttachment $attachment)
    {
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        if ($attachment->pdf_path && Storage::disk('public')->exists($attachment->pdf_path)) {
            Storage::disk('public')->delete($attachment->pdf_path);
        }
        
        $document = $attachment->document;
        $attachment->delete();

        // Jika semua berkas Kertas Kerja terhapus (kosong), kembalikan status ke Draft
        if ($document && $document->attachments()->count() === 0 && $document->status !== 'draft') {
            $document->update(['status' => 'draft']);
        }
        
        return redirect()->route('pkpt.kertas-kerja', $pkpt)->with('success', 'File Kertas Kerja berhasil dihapus.');
    }

    public function destroyKertasKerjaDocument(Pkpt $pkpt)
    {
        $documentType = \App\Models\DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();
        $document = \App\Models\Document::where('pkpt_id', $pkpt->id)
            ->where('document_type_id', $documentType->id)
            ->first();
            
        if ($document) {
            foreach ($document->attachments as $att) {
                if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
                if ($att->pdf_path && Storage::disk('public')->exists($att->pdf_path)) {
                    Storage::disk('public')->delete($att->pdf_path);
                }
                $att->delete();
            }
            $document->delete();
        }
        
        return redirect()->route('pkpt.kertas-kerja.index')->with('success', 'Data Kertas Kerja PKPT berhasil dihapus.');
    }

    public function reopenKertasKerja(Pkpt $pkpt)
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Hanya Administrator yang memiliki akses untuk membuka kunci Kertas Kerja PKPT.');
        }

        $documentType = DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();
        $document = Document::where('pkpt_id', $pkpt->id)
            ->where('document_type_id', $documentType->id)
            ->first();

        if (!$document) {
            return redirect()->back()->with('error', 'Dokumen Kertas Kerja PKPT tidak ditemukan.');
        }

        if ($document->status === 'draft') {
            return redirect()->back()->with('warning', 'Dokumen Kertas Kerja PKPT saat ini sudah berstatus Draft.');
        }

        $document->update([
            'status' => 'draft',
        ]);

        return redirect()->back()->with('success', 'Kertas Kerja PKPT berhasil dibuka kembali (Reopen ke Draft). Berkas sekarang dapat diubah atau ditambahkan.');
    }

    public function downloadKertasKerjaZip(Pkpt $pkpt)
    {
        $documentType = DocumentType::where('slug', 'kertas-kerja-pkpt')->first();
        if (!$documentType) {
            return redirect()->back()->with('error', 'Tipe dokumen Kertas Kerja PKPT tidak ditemukan.');
        }

        $document = Document::where('pkpt_id', $pkpt->id)
            ->where('document_type_id', $documentType->id)
            ->with('attachments')
            ->first();

        if (!$document || $document->attachments->isEmpty()) {
            return redirect()->back()->with('warning', 'Belum ada berkas Kertas Kerja PKPT yang diunggah untuk diunduh.');
        }

        // Folder name mapping for the 6 categories (numbered 01 to 06 for orderly display)
        $folderMapping = [
            'RJP'             => '01_RJP - Rencana Jangka Panjang',
            'RKAP'            => '02_RKAP - Rencana Kerja & Anggaran Perusahaan',
            'Profil Risiko'   => '03_Profil Risiko - Dokumen Profil Risiko',
            'Arahan PS'       => '04_Arahan PS - Arahan Pemegang Saham',
            'Arahan Dekom'    => '05_Arahan Dekom - Arahan Dewan Komisaris',
            'Arahan Direktur' => '06_Arahan Direktur - Arahan Direktur',
        ];

        // Filter attachments that actually exist on storage
        $validAttachments = $document->attachments->filter(function ($att) {
            return $att->file_path && Storage::disk('public')->exists($att->file_path);
        });

        if ($validAttachments->isEmpty()) {
            return redirect()->back()->with('error', 'Berkas fisik Kertas Kerja PKPT tidak ditemukan di server.');
        }

        $cleanTitle = \Illuminate\Support\Str::slug($pkpt->title) ?: 'PKPT';
        $zipFileName = 'Kertas_Kerja_PKPT_' . $pkpt->period_year . '_' . $cleanTitle . '.zip';

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/' . uniqid('kk_zip_') . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat arsip ZIP.');
        }

        // 1. Create all 6 category folders in the ZIP so the structure is always complete & organized
        foreach ($folderMapping as $folder) {
            $zip->addEmptyDir($folder);
        }

        // 2. Track filenames per folder to handle duplicates cleanly
        $usedNamesPerFolder = [];
        $addedCount = 0;

        foreach ($validAttachments as $att) {
            $catKey = trim($att->description ?? '');
            $folderName = $folderMapping[$catKey] ?? '07_Lainnya';

            if (!isset($usedNamesPerFolder[$folderName])) {
                $usedNamesPerFolder[$folderName] = [];
            }

            $realPath = storage_path('app/public/' . $att->file_path);
            if (!file_exists($realPath)) {
                continue;
            }

            $fileName = $att->file_name ?: basename($att->file_path);

            // Handle filename collision inside the same category folder
            $checkName = strtolower($fileName);
            if (in_array($checkName, $usedNamesPerFolder[$folderName])) {
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $base = pathinfo($fileName, PATHINFO_FILENAME);
                $counter = 1;
                do {
                    $newFileName = $base . " ({$counter})" . ($ext ? '.' . $ext : '');
                    $counter++;
                } while (in_array(strtolower($newFileName), $usedNamesPerFolder[$folderName]));
                $fileName = $newFileName;
            }

            $usedNamesPerFolder[$folderName][] = strtolower($fileName);
            $zipPathInArchive = $folderName . '/' . $fileName;

            $zip->addFile($realPath, $zipPathInArchive);
            $addedCount++;
        }

        $zip->close();

        if ($addedCount === 0 || !file_exists($zipPath)) {
            return redirect()->back()->with('error', 'Tidak ada berkas yang berhasil dimasukkan ke dalam arsip ZIP.');
        }

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
