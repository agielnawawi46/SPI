<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Traits\HandlesDocumentAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceReportController extends Controller
{
    use HandlesDocumentAttachments;

    public function index(Request $request)
    {
        $query = Document::with(['creator', 'documentType'])
            ->whereHas('documentType', fn($q) => $q->where('module', 'report'));

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $reports = $query->latest()->paginate(10);

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
            'content'          => 'nullable|string',
            'attachments'      => 'nullable|array',
            'attachments.*'    => 'file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'attachment'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
        ]);

        if (!$request->hasFile('attachments') && !$request->hasFile('attachment')) {
            return back()->withErrors(['attachments' => 'Berkas lampiran wajib diunggah.'])->withInput();
        }

        $documentType = DocumentType::where('module', 'report')->first();

        $document = Document::create([
            'title'            => $request->title,
            'document_type_id' => $documentType ? $documentType->id : null,
            'content'          => $request->content ?? '',
            'report_month'     => date('n'),
            'report_year'      => date('Y'),
            'created_by'       => Auth::id(),
            'status'           => 'approved', // Marking as approved since it's just an upload
        ]);

        $this->saveDocumentAttachments($request, $document);

        return redirect()->route('performance-reports.index')
            ->with('success', 'Laporan Kinerja berhasil diunggah.');
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
            'title'         => 'required|string|max:255',
            'content'       => 'nullable|string',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'attachment'    => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
        ]);

        $document->update([
            'title'   => $request->title,
            'content' => $request->content ?? '',
        ]);

        $this->saveDocumentAttachments($request, $document);

        return redirect()->route('performance-reports.show', $document)->with('success', 'Laporan berhasil diperbarui.');
    }
}
