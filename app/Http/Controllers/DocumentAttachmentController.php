<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAttachment;
use Illuminate\Support\Facades\Storage;

class DocumentAttachmentController extends Controller
{
    public function destroy(Document $document, DocumentAttachment $attachment)
    {
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

        return back()->with('success', 'Berkas lampiran berhasil dihapus.');
    }
}
