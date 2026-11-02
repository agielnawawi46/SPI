<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAttachment;
use Illuminate\Support\Facades\Storage;

class DocumentAttachmentController extends Controller
{
    /**
     * Preview / Stream attachment file in browser.
     */
    public function view(DocumentAttachment $attachment)
    {
        // Prefer converted PDF if available so browser can render it inline
        $hasPdf = $attachment->pdf_path && Storage::disk('public')->exists($attachment->pdf_path);
        $filePath = $hasPdf ? $attachment->pdf_path : $attachment->file_path;
        $fileName = $hasPdf ? pathinfo($attachment->file_name, PATHINFO_FILENAME) . '.pdf' : $attachment->file_name;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return back()->with('error', 'Berkas fisik tidak ditemukan di server.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $isInline = $hasPdf || in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']);

        $mimeType = $hasPdf ? 'application/pdf' : ($attachment->file_type ?? Storage::disk('public')->mimeType($filePath) ?? 'application/octet-stream');

        return Storage::disk('public')->response(
            $filePath,
            $fileName,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => ($isInline ? 'inline' : 'attachment') . '; filename="' . addslashes($fileName) . '"'
            ]
        );
    }

    /**
     * Download attachment file with original clean filename.
     */
    public function download(DocumentAttachment $attachment)
    {
        if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
            return back()->with('error', 'Berkas fisik tidak ditemukan di server.');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Download all attachments of a document (single file or ZIP if multiple).
     */
    public function downloadDocument(Document $document)
    {
        $attachments = $document->attachments()->get();

        if ($attachments->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada berkas untuk diunduh.');
        }

        if ($attachments->count() === 1) {
            $single = $attachments->first();
            if ($single->file_path && Storage::disk('public')->exists($single->file_path)) {
                return Storage::disk('public')->download($single->file_path, $single->file_name);
            }
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        $cleanTitle = \Illuminate\Support\Str::slug($document->title) ?: 'Dokumen';
        $zipFileName = $cleanTitle . '_Semua_Dokumen.zip';
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

        // Jika semua berkas lampiran sudah terhapus (kosong), kembalikan status ke Draft
        if ($document->attachments()->count() === 0 && $document->status !== 'draft') {
            $document->update(['status' => 'draft']);

            // Jika dokumen terkait dengan PKPT utama, sinkronkan juga model PKPT
            if ($document->pkpt_id && $document->pkpt) {
                $document->pkpt->update([
                    'status'    => 'draft',
                    'file_name' => null,
                    'file_path' => null,
                    'file_size' => 0,
                    'file_type' => null,
                ]);
            }
        }

        return back()->with('success', 'Berkas lampiran berhasil dihapus.');
    }
}
