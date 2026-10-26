<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pkpt;
use App\Models\DocumentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PkptController extends Controller
{
    // ==========================================
    // PKPT Utama
    // ==========================================

    public function index(Request $request)
    {
        $query = Pkpt::with('creator');

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
            'title'       => 'required|string|max:255',
            'period_year' => 'required|digits:4|integer',
            'pkpt_file'   => 'required|file|mimes:pdf', // Wajib PDF
        ]);

        $pkpt = new Pkpt($request->only('title', 'period_year'));
        $pkpt->created_by = Auth::id();
        $pkpt->status = 'approved';
        
        if ($request->hasFile('pkpt_file')) {
            $file = $request->file('pkpt_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('pkpt_files', $fileName, 'public');
            
            $pkpt->file_name = $file->getClientOriginalName();
            $pkpt->file_path = $filePath;
            $pkpt->file_type = $file->getClientMimeType();
            $pkpt->file_size = $file->getSize();
        }
        
        $pkpt->save();

        return redirect()->route('pkpt.show', $pkpt)->with('success', 'Dokumen PKPT berhasil diunggah.');
    }

    public function edit(Pkpt $pkpt)
    {
        return view('admin.pkpt.edit', compact('pkpt'));
    }

    public function update(Request $request, Pkpt $pkpt)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'period_year' => 'required|digits:4|integer',
            'pkpt_file'   => 'nullable|file|mimes:pdf', // PDF optional for update
        ]);

        $pkpt->title = $request->title;
        $pkpt->period_year = $request->period_year;

        if ($request->hasFile('pkpt_file')) {
            // Hapus file lama jika ada
            if ($pkpt->file_path && Storage::disk('public')->exists($pkpt->file_path)) {
                Storage::disk('public')->delete($pkpt->file_path);
            }

            $file = $request->file('pkpt_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('pkpt_files', $fileName, 'public');
            
            $pkpt->file_name = $file->getClientOriginalName();
            $pkpt->file_path = $filePath;
            $pkpt->file_type = $file->getClientMimeType();
            $pkpt->file_size = $file->getSize();
        }
        
        $pkpt->save();

        return redirect()->route('pkpt.show', $pkpt)->with('success', 'PKPT berhasil diperbarui.');
    }

    public function show(Pkpt $pkpt)
    {
        return view('admin.pkpt.show', compact('pkpt'));
    }

    public function destroy(Pkpt $pkpt)
    {
        if ($pkpt->file_path && Storage::disk('public')->exists($pkpt->file_path)) {
            Storage::disk('public')->delete($pkpt->file_path);
        }
        
        $pkpt->delete();
        return redirect()->route('pkpt.index')->with('success', 'PKPT berhasil dihapus.');
    }

    public function viewPkptFile(Pkpt $pkpt)
    {
        if (!$pkpt->file_path || !Storage::disk('public')->exists($pkpt->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->response(
            $pkpt->file_path, 
            $pkpt->file_name,
            ['Content-Disposition' => 'inline; filename="'.$pkpt->file_name.'"']
        );
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
        $request->validate([
            'attachments' => 'nullable|array',
        ]);

        $documentType = \App\Models\DocumentType::where('slug', 'kertas-kerja-pkpt')->firstOrFail();
        $document = \App\Models\Document::where('pkpt_id', $pkpt->id)
            ->where('document_type_id', $documentType->id)
            ->firstOrFail();

        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $uploadedCount = 0;

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $category => $files) {
                $fileList = is_array($files) ? $files : [$files];

                foreach ($fileList as $file) {
                    if (!$file || !$file->isValid()) {
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
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error converting DOCX to PDF: ' . $e->getMessage());
                        }
                    }

                    $document->attachments()->create([
                        'uploaded_by' => Auth::id(),
                        'description' => $category, // 'RJP', 'RKAP', dll
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
