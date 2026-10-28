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
        if ($pkpt->file_path && $document->attachments()->count() === 0 && Storage::disk('public')->exists($pkpt->file_path)) {
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
        if ($attachment->document_id !== $document->id) {
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
            $filePath = $attachment->file_path;
            $fileName = $attachment->file_name;
            $mimeType = $attachment->file_type ?? 'application/pdf';
        } else {
            $document = $this->getOrCreatePkptDocument($pkpt);
            $attachments = $document->attachments()->get();

            // Jika ada lebih dari 1 file, arahkan ke halaman show agar pengguna bisa memilih file
            if ($attachments->count() > 1) {
                return redirect()->route('pkpt.show', $pkpt);
            }

            if ($attachments->count() === 1) {
                $filePath = $attachments->first()->file_path;
                $fileName = $attachments->first()->file_name;
                $mimeType = $attachments->first()->file_type ?? 'application/pdf';
            } else {
                $filePath = $pkpt->file_path;
                $fileName = $pkpt->file_name;
                $mimeType = $pkpt->file_type ?? 'application/pdf';
            }
        }

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return redirect()->route('pkpt.show', $pkpt)->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->response(
            $filePath, 
            $fileName,
            ['Content-Disposition' => 'inline; filename="'.$fileName.'"']
        );
    }

    public function downloadPkptFile(Pkpt $pkpt, ?DocumentAttachment $attachment = null)
    {
        // 1. Jika unduh berkas spesifik
        if ($attachment) {
            if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
                return redirect()->back()->with('error', 'File tidak ditemukan.');
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
        
        $query = \App\Models\Document::with(['pkpt', 'creator'])
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

                $pdfPath = null;
                if (in_array($extension, ['doc', 'docx'])) {
                    try {
                        \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
                        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
                        
                        $phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/' . $filePath));
                        $pdfFilename = time() . '_' . $uniqueId . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
                        $pdfRelativePath = 'attachments/' . $pdfFilename;
                        $pdfAbsolutePath = storage_path('app/public/' . $pdfRelativePath);
                        
                        $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
                        $pdfWriter->save($pdfAbsolutePath);
                        
                        $pdfPath = $pdfRelativePath;
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Error converting DOCX to PDF: ' . $e->getMessage());
                    }
                }

                $document->attachments()->create([
                    'uploaded_by' => Auth::id(),
                    'description' => $category,
                    'file_name'   => $originalName,
                    'file_path'   => $filePath,
                    'file_type'   => $file->getClientMimeType(),
                    'file_size'   => $file->getSize(),
                    'pdf_path'    => $pdfPath,
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

                    $pdfPath = null;
                    if (in_array($extension, ['doc', 'docx'])) {
                        try {
                            \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
                            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
                            
                            $phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/public/' . $filePath));
                            $pdfFilename = time() . '_' . $uniqueId . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
                            $pdfRelativePath = 'attachments/' . $pdfFilename;
                            $pdfAbsolutePath = storage_path('app/public/' . $pdfRelativePath);
                            
                            $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
                            $pdfWriter->save($pdfAbsolutePath);
                            
                            $pdfPath = $pdfRelativePath;
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Error converting DOCX to PDF: ' . $e->getMessage());
                        }
                    }

                    $document->attachments()->create([
                        'uploaded_by' => Auth::id(),
                        'description' => $category,
                        'file_name'   => $originalName,
                        'file_path'   => $filePath,
                        'file_type'   => $file->getClientMimeType(),
                        'file_size'   => $file->getSize(),
                        'pdf_path'    => $pdfPath,
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
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $attachment->delete();
        
        return redirect()->route('pkpt.kertas-kerja', $pkpt)->with('success', 'File Kertas Kerja berhasil dihapus.');
    }

    public function destroyKertasKerjaDocument(Pkpt $pkpt)
    {
        $documentType = \App\Models\DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();
        $document = \App\Models\Document::where('pkpt_id', $pkpt->id)
            ->where('document_type_id', $documentType->id)
            ->first();
            
        if ($document) {
            $document->delete();
        }
        
        return redirect()->route('pkpt.kertas-kerja.index')->with('success', 'Data Kertas Kerja PKPT berhasil dihapus.');
    }
}
