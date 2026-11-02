<?php

namespace App\Traits;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HandlesDocumentAttachments
{
    /**
     * Menyimpan berkas lampiran (mendukung multi-file dan single-file)
     */
    protected function saveDocumentAttachments(Request $request, Document $document, bool $replaceOld = false): int
    {
        $rawFiles = $request->file('attachments') ?? $request->file('attachment');
        if (!$rawFiles) {
            return 0;
        }

        $files = is_array($rawFiles) ? $rawFiles : [$rawFiles];
        if (empty($files)) {
            return 0;
        }

        if ($replaceOld) {
            foreach ($document->attachments as $oldAtt) {
                if ($oldAtt->file_path && Storage::disk('public')->exists($oldAtt->file_path)) {
                    Storage::disk('public')->delete($oldAtt->file_path);
                }
                if ($oldAtt->pdf_path && Storage::disk('public')->exists($oldAtt->pdf_path)) {
                    Storage::disk('public')->delete($oldAtt->pdf_path);
                }
                $oldAtt->delete();
            }
        }

        $savedCount = 0;
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $uniqueId = uniqid();
            $subDir = 'attachments/' . date('Y/m');
            $fileName = time() . '_' . $uniqueId . '_' . $originalName;
            $path = $file->storeAs($subDir, $fileName, 'public');

            $document->attachments()->create([
                'uploaded_by' => Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => 'Lampiran Dokumen',
                'pdf_path'    => null,
            ]);

            $savedCount++;
        }

        return $savedCount;
    }
}
