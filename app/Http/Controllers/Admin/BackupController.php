<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\BackupArchiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BackupController extends Controller
{
    public function __construct(private readonly BackupArchiveService $backupService)
    {
    }

    /**
     * Display the Admin Backup & Archive page with inventory breakdown per menu.
     */
    public function index(Request $request)
    {
        $yearParam = $request->get('year');
        $yearFilter = ($yearParam && $yearParam !== 'all' && ctype_digit((string) $yearParam))
            ? (int) $yearParam
            : null;

        $inventory = $this->backupService->getInventorySummary($yearFilter);

        return view('admin.backup.index', compact('inventory', 'yearFilter'));
    }

    /**
     * Generate and download a ZIP archive of active files and generated PDFs.
     */
    public function download(Request $request)
    {
        $allowedModules = array_merge(['all'], array_keys(BackupArchiveService::MODULES));

        $validated = $request->validate([
            'module' => ['required', 'string', Rule::in($allowedModules)],
            'year'   => ['nullable', 'string'],
        ]);

        $moduleFilter = $validated['module'];
        $rawYear = $validated['year'] ?? null;
        $yearFilter = ($rawYear && $rawYear !== 'all' && ctype_digit((string) $rawYear))
            ? (int) $rawYear
            : null;

        try {
            $result = $this->backupService->buildZipArchive($moduleFilter, $yearFilter);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membuat arsip backup: ' . $e->getMessage());
        }

        if (!$result) {
            return redirect()->back()->with('warning', 'Tidak ada berkas atau dokumen aktif untuk kriteria yang dipilih.');
        }

        $moduleLabel = $moduleFilter === 'all'
            ? 'Semua Menu'
            : (BackupArchiveService::MODULES[$moduleFilter]['label'] ?? $moduleFilter);
        $yearLabel = $yearFilter ? "Tahun {$yearFilter}" : 'Semua Tahun';

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'backup',
            'description' => "Unduh backup ZIP ({$moduleLabel}, {$yearLabel}): {$result['file_count']} file lampiran & {$result['pdf_count']} PDF",
            'ip_address'  => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 500),
            'url'         => $request->fullUrl(),
        ]);

        $zipPath = $result['path'];
        $fileSize = @filesize($zipPath);
        $headers = ['Content-Type' => 'application/zip'];
        if ($fileSize !== false) {
            $headers['Content-Length'] = (string) $fileSize;
        }

        return response()->streamDownload(function () use ($zipPath) {
            $stream = fopen($zipPath, 'rb');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
            @unlink($zipPath);
        }, $result['filename'], $headers);
    }
}
