<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReportController extends Controller
{
    public function index()
    {
        $reports = Document::with(['creator'])
            ->whereHas('documentType', fn($q) => $q->where('module', 'report'))
            ->latest()
            ->paginate(10);

        return view('performance-reports.index', compact('reports'));
    }

    public function create()
    {
        $documentTypes = DocumentType::where('module', 'report')->get();
        return view('performance-reports.create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'report_month'     => 'required|integer|between:1,12',
            'report_year'      => 'required|integer|min:2020|max:2099',
        ]);

        $document = Document::create([
            'title'            => $request->title,
            'document_type_id' => $request->document_type_id,
            'content'          => '',
            'report_month'     => $request->report_month,
            'report_year'      => $request->report_year,
            'created_by'       => Auth::id(),
            'status'           => 'draft',
        ]);

        return redirect()->route('performance-reports.show', $document)
            ->with('success', 'Laporan Kinerja berhasil dibuat. Anda dapat mengisi Kertas Kerja Dokumen ini.');
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
        return view('performance-reports.show', compact('document'));
    }

    public function edit(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $documentTypes = DocumentType::where('module', 'report')->get();
        return view('performance-reports.edit', compact('document', 'documentTypes'));
    }

    public function update(Request $request, Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah dokumen ini.');
        }

        $request->validate([
            'content'    => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:307200',
        ]);

        $document->update(['content' => $request->content]);

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

        return redirect()->route('performance-reports.show', $document)->with('success', 'Laporan berhasil diperbarui.');
    }
}
