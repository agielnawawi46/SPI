<?php

namespace App\Services;

use App\Models\Document;
use App\Models\FollowUp;
use App\Models\Pkpt;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BackupArchiveService
{
    public const MODULES = [
        'pkpt' => [
            'label'  => 'Penyusunan PKPT',
            'folder' => '01_Penyusunan_PKPT',
            'desc'   => 'PKPT Utama dan 6 kategori Kertas Kerja PKPT (RJP, RKAP, Profil Risiko, Arahan).',
        ],
        'assurance' => [
            'label'  => 'Layanan Assurance',
            'folder' => '02_Layanan_Assurance',
            'desc'   => 'Audit Terjadwal & Tidak Terjadwal (Surat Tugas PDF, Program Kerja, Slide Entry, KKA, LHA).',
        ],
        'advisory' => [
            'label'  => 'Layanan Advisory',
            'folder' => '03_Layanan_Advisory',
            'desc'   => 'Berita Acara Konsultasi (PDF & lampiran) serta Laporan Konsultasi.',
        ],
        'kegiatan-lainnya' => [
            'label'  => 'Kegiatan Lainnya',
            'folder' => '04_Kegiatan_Lainnya',
            'desc'   => 'Monitoring Tindak Lanjut (PDF & Bukti Auditee), Pendamping Eksternal, dan Quality Assurance.',
        ],
        'report' => [
            'label'  => 'Laporan Kinerja',
            'folder' => '05_Laporan_Kinerja',
            'desc'   => 'Arsip Laporan Kinerja Bulanan SPI yang dikelompokkan per tahun dan bulan.',
        ],
    ];

    private const KK_PKPT_FOLDERS = [
        'RJP'             => '01_RJP',
        'RKAP'            => '02_RKAP',
        'Profil Risiko'   => '03_Profil_Risiko',
        'Arahan PS'       => '04_Arahan_PS',
        'Arahan Dekom'    => '05_Arahan_Dekom',
        'Arahan Direktur' => '06_Arahan_Direktur',
    ];

    /**
     * Get dashboard inventory summary (total files, generated PDFs, sizes, missing files, and available years).
     */
    public function getInventorySummary(?int $yearFilter = null): array
    {
        $allEntries = $this->collectEntries(null, null);

        $availableYears = $allEntries
            ->pluck('year')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $entries = $yearFilter
            ? $allEntries->where('year', $yearFilter)->values()
            : $allEntries;

        $modulesSummary = [];
        foreach (self::MODULES as $key => $meta) {
            $modEntries = $entries->where('module', $key);
            $fileEntries = $modEntries->where('kind', 'file');
            $pdfEntries = $modEntries->where('kind', 'pdf');
            $existingFiles = $fileEntries->where('exists', true);
            $missingFiles = $fileEntries->where('exists', false);

            $modulesSummary[$key] = [
                'key'           => $key,
                'label'         => $meta['label'],
                'folder'        => $meta['folder'],
                'desc'          => $meta['desc'],
                'file_count'    => $existingFiles->count(),
                'missing_count' => $missingFiles->count(),
                'pdf_count'     => $pdfEntries->count(),
                'total_items'   => $existingFiles->count() + $pdfEntries->count(),
                'total_size'    => $existingFiles->sum('file_size'),
            ];
        }

        $totalFiles = collect($modulesSummary)->sum('file_count');
        $totalMissing = collect($modulesSummary)->sum('missing_count');
        $totalPdfs = collect($modulesSummary)->sum('pdf_count');
        $totalSize = collect($modulesSummary)->sum('total_size');

        return [
            'modules'         => $modulesSummary,
            'available_years' => $availableYears,
            'total_files'     => $totalFiles,
            'total_missing'   => $totalMissing,
            'total_pdfs'      => $totalPdfs,
            'total_items'     => $totalFiles + $totalPdfs,
            'total_size'      => $totalSize,
        ];
    }

    /**
     * Build a ZIP archive containing active uploaded files and generated form PDFs grouped by menu folders.
     * Returns ['path' => string, 'filename' => string, 'file_count' => int, 'pdf_count' => int, 'error_count' => int] or null if empty.
     */
    public function buildZipArchive(string $moduleFilter = 'all', ?int $yearFilter = null): ?array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $entries = $this->collectEntries(
            $moduleFilter === 'all' ? null : $moduleFilter,
            $yearFilter
        );

        // Keep only exportable items (existing physical files or PDFs)
        $exportable = $entries->filter(fn (array $item) => $item['kind'] === 'pdf' || $item['exists'])->values();

        if ($exportable->isEmpty()) {
            return null;
        }

        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempDir = realpath($tempDir) ?: $tempDir;

        $modPart = $moduleFilter === 'all'
            ? 'Semua_Menu'
            : ($this->sanitizeSegment(self::MODULES[$moduleFilter]['label'] ?? $moduleFilter, 30));
        $yearPart = $yearFilter ? (string) $yearFilter : 'Semua_Tahun';
        $zipFileName = 'SIMAK_Backup_' . $modPart . '_' . $yearPart . '_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('backup_', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat arsip ZIP pada server.');
        }

        Carbon::setLocale('id');

        $usedPaths = [];
        $fileCount = 0;
        $pdfCount = 0;
        $errorCount = 0;

        foreach ($exportable as $item) {
            $zipFolder = trim($item['zip_folder'], '/');

            if ($item['kind'] === 'file') {
                $realPath = Storage::disk('public')->path($item['disk_path']);
                if (!is_file($realPath)) {
                    continue;
                }

                $entryPath = $this->uniqueZipEntryPath($zipFolder, $item['file_name'], $usedPaths);
                $zip->addFile($realPath, $entryPath);
                $fileCount++;
            } elseif ($item['kind'] === 'pdf') {
                try {
                    $pdfBytes = $this->renderDocumentPdf($item['document'], $item['pdf_type']);
                    $entryPath = $this->uniqueZipEntryPath($zipFolder, $item['file_name'], $usedPaths);
                    $zip->addFromString($entryPath, $pdfBytes);
                    $pdfCount++;
                } catch (\Throwable $e) {
                    $errorCount++;
                    Log::warning('Backup PDF generation failed for Document #' . $item['document']->id . ': ' . $e->getMessage());
                    $errEntryPath = $this->uniqueZipEntryPath(
                        $zipFolder,
                        'GAGAL_GENERATE_PDF_' . $item['document']->id . '.txt',
                        $usedPaths
                    );
                    $zip->addFromString(
                        $errEntryPath,
                        "Gagal men-generate PDF untuk dokumen #{$item['document']->id} ({$item['document']->title}):\n" . $e->getMessage()
                    );
                }
            }
        }

        $zip->close();

        if (($fileCount + $pdfCount) === 0 || !is_file($zipPath)) {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
            return null;
        }

        return [
            'path'        => realpath($zipPath) ?: $zipPath,
            'filename'    => $zipFileName,
            'file_count'  => $fileCount,
            'pdf_count'   => $pdfCount,
            'error_count' => $errorCount,
        ];
    }

    /**
     * Collect all active (non-soft-deleted) uploaded files and printable PDF documents.
     */
    public function collectEntries(?string $moduleFilter = null, ?int $yearFilter = null): Collection
    {
        $entries = collect();

        if ($moduleFilter === null || $moduleFilter === 'pkpt') {
            $entries = $entries->merge($this->collectPkptEntries());
        }

        if ($moduleFilter === null || in_array($moduleFilter, ['assurance', 'advisory', 'kegiatan-lainnya', 'report'], true)) {
            $entries = $entries->merge($this->collectDocumentModuleEntries($moduleFilter));
        }

        if ($moduleFilter === null || $moduleFilter === 'kegiatan-lainnya') {
            $entries = $entries->merge($this->collectOrphanFollowUpEntries());
        }

        if ($yearFilter !== null) {
            $entries = $entries->where('year', $yearFilter)->values();
        }

        return $entries;
    }

    private function collectPkptEntries(): Collection
    {
        $entries = collect();
        $rootFolder = self::MODULES['pkpt']['folder'];

        // 1. Active PKPT Utama (excludes soft-deleted PKPT)
        $pkpts = Pkpt::with([
            'documents' => function ($q) {
                $q->whereHas('documentType', fn ($dt) => $dt->where('slug', 'pkpt'))
                  ->with('attachments');
            },
        ])->orderByDesc('period_year')->get();

        foreach ($pkpts as $pkpt) {
            $year = (int) ($pkpt->period_year ?: ($pkpt->created_at?->year ?? now()->year));
            $pkptFolderName = $year . ' - ' . $this->sanitizeSegment($pkpt->title, 55) . ' #' . $pkpt->id;
            $baseFolder = $rootFolder . '/PKPT_Utama/' . $pkptFolderName;

            $pkptDoc = $pkpt->documents->first();
            $attachments = $pkptDoc ? $pkptDoc->attachments : collect();

            if ($attachments->isNotEmpty()) {
                foreach ($attachments as $att) {
                    $entries->push($this->makeFileEntry(
                        'pkpt',
                        $year,
                        $baseFolder,
                        $att->file_name ?: basename($att->file_path),
                        $att->file_path,
                        (int) $att->file_size
                    ));
                }
            } elseif (!empty($pkpt->file_path)) {
                // Legacy single-file PKPT fallback
                $entries->push($this->makeFileEntry(
                    'pkpt',
                    $year,
                    $baseFolder,
                    $pkpt->file_name ?: basename($pkpt->file_path),
                    $pkpt->file_path,
                    (int) ($pkpt->file_size ?? 0)
                ));
            }
        }

        // 2. Active Kertas Kerja PKPT (both Document and parent Pkpt must not be soft-deleted)
        $kkDocs = Document::with(['pkpt', 'attachments'])
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'kertas-kerja-pkpt'))
            ->whereHas('pkpt')
            ->latest()
            ->get();

        foreach ($kkDocs as $doc) {
            $pkpt = $doc->pkpt;
            $year = (int) ($pkpt->period_year ?: $this->resolveDocumentYear($doc));
            $kkBase = $rootFolder . '/Kertas_Kerja_PKPT/' . $year . ' - ' . $this->sanitizeSegment($pkpt->title, 55) . ' #' . $pkpt->id;

            foreach ($doc->attachments as $att) {
                $catKey = trim((string) $att->description);
                $catFolder = self::KK_PKPT_FOLDERS[$catKey] ?? '07_Lainnya';

                $entries->push($this->makeFileEntry(
                    'pkpt',
                    $year,
                    $kkBase . '/' . $catFolder,
                    $att->file_name ?: basename($att->file_path),
                    $att->file_path,
                    (int) $att->file_size
                ));
            }
        }

        return $entries;
    }

    private function collectDocumentModuleEntries(?string $moduleFilter): Collection
    {
        $entries = collect();

        $dbModules = match ($moduleFilter) {
            'assurance'        => ['assurance'],
            'advisory'         => ['advisory'],
            'kegiatan-lainnya' => ['pendamping', 'qa', 'tindak-lanjut'],
            'report'           => ['report'],
            default            => ['assurance', 'advisory', 'pendamping', 'qa', 'tindak-lanjut', 'report'],
        };

        $documents = Document::with([
            'documentType',
            'creator.department',
            'attachments',
            'followUps.evidences',
            'followUps.auditeeDepartment',
        ])
            ->whereHas('documentType', fn ($q) => $q->whereIn('module', $dbModules))
            ->latest()
            ->get();

        foreach ($documents as $doc) {
            $dbMod = $doc->documentType?->module ?? '';
            $slug  = $doc->documentType?->slug ?? '';
            $year  = $this->resolveDocumentYear($doc);
            $docFolder = $this->sanitizeSegment($doc->title ?: 'Dokumen', 55) . ' #' . $doc->id;

            $menuKey = match ($dbMod) {
                'assurance' => 'assurance',
                'advisory'  => 'advisory',
                'report'    => 'report',
                default     => 'kegiatan-lainnya',
            };

            $baseFolder = $this->resolveDocumentZipFolder($doc, $dbMod, $slug, $year, $docFolder);

            // Uploaded document attachments
            foreach ($doc->attachments as $att) {
                $attFolder = ($slug === 'monitoring-tl')
                    ? $baseFolder . '/Lampiran_Dokumen'
                    : $baseFolder;

                $entries->push($this->makeFileEntry(
                    $menuKey,
                    $year,
                    $attFolder,
                    $att->file_name ?: basename($att->file_path),
                    $att->file_path,
                    (int) $att->file_size
                ));
            }

            // Form-generated PDFs
            $pdfSpec = $this->resolvePdfSpec($doc, $slug);
            if ($pdfSpec !== null) {
                $entries->push([
                    'kind'       => 'pdf',
                    'module'     => $menuKey,
                    'year'       => $year,
                    'zip_folder' => $baseFolder,
                    'file_name'  => $pdfSpec['file_name'],
                    'disk_path'  => null,
                    'file_size'  => 0,
                    'exists'     => true,
                    'document'   => $doc,
                    'pdf_type'   => $pdfSpec['pdf_type'],
                ]);
            }

            // Auditee follow-up evidences linked to Monitoring TL document (only active FollowUps)
            if ($slug === 'monitoring-tl') {
                foreach ($doc->followUps as $fu) {
                    if ($fu->evidences->isEmpty()) {
                        continue;
                    }
                    $picFolder = $this->resolveFollowUpPicFolder($fu);
                    $evFolder  = $baseFolder . '/Bukti_Auditee/' . $picFolder;

                    foreach ($fu->evidences as $ev) {
                        $entries->push($this->makeFileEntry(
                            'kegiatan-lainnya',
                            $year,
                            $evFolder,
                            $ev->file_name ?: basename($ev->file_path),
                            $ev->file_path,
                            (int) $ev->file_size
                        ));
                    }
                }
            }
        }

        return $entries;
    }

    private function collectOrphanFollowUpEntries(): Collection
    {
        $entries = collect();
        $rootFolder = self::MODULES['kegiatan-lainnya']['folder'];

        $orphanFollowUps = FollowUp::whereNull('document_id')
            ->with(['evidences', 'auditeeDepartment'])
            ->get();

        foreach ($orphanFollowUps as $fu) {
            if ($fu->evidences->isEmpty()) {
                continue;
            }
            $year = (int) ($fu->due_date?->year ?? $fu->created_at?->year ?? now()->year);
            $picFolder = $this->resolveFollowUpPicFolder($fu);
            $evFolder = $rootFolder . '/Tindak_Lanjut_Lainnya/' . $picFolder;

            foreach ($fu->evidences as $ev) {
                $entries->push($this->makeFileEntry(
                    'kegiatan-lainnya',
                    $year,
                    $evFolder,
                    $ev->file_name ?: basename($ev->file_path),
                    $ev->file_path,
                    (int) $ev->file_size
                ));
            }
        }

        return $entries;
    }

    private function resolveDocumentZipFolder(Document $doc, string $dbMod, string $slug, int $year, string $docFolder): string
    {
        if ($dbMod === 'assurance') {
            $auditSub = $doc->audit_type === 'tidak_terjadwal' ? 'Audit_Tidak_Terjadwal' : 'Audit_Terjadwal';
            $typeSub = match ($slug) {
                'surat-tugas'        => '01_Surat_Tugas_Audit',
                'program-kerja'      => '02_Program_Kerja_Audit',
                'slide-entry'        => '03_Slide_Entry_Meeting',
                'kertas-kerja-audit' => '04_Kertas_Kerja_Audit',
                'lha'                => '05_Laporan_Hasil_Audit',
                default              => $this->sanitizeSegment($doc->documentType?->name ?? 'Lainnya', 40),
            };
            return self::MODULES['assurance']['folder'] . '/' . $auditSub . '/' . $typeSub . '/' . $docFolder;
        }

        if ($dbMod === 'advisory') {
            $typeSub = match ($slug) {
                'bak'            => '01_Berita_Acara_Konsultasi',
                'lap-konsultasi' => '02_Laporan_Konsultasi',
                default          => $this->sanitizeSegment($doc->documentType?->name ?? 'Lainnya', 40),
            };
            return self::MODULES['advisory']['folder'] . '/' . $typeSub . '/' . $docFolder;
        }

        if ($dbMod === 'pendamping') {
            $typeSub = match ($slug) {
                'lha-eksternal'  => 'LHA_Auditor_Eksternal',
                'lap-pendamping' => 'Laporan_Pendamping',
                default          => $this->sanitizeSegment($doc->documentType?->name ?? 'Lainnya', 40),
            };
            return self::MODULES['kegiatan-lainnya']['folder'] . '/Pendamping_Eksternal/' . $typeSub . '/' . $docFolder;
        }

        if ($dbMod === 'qa') {
            $typeSub = match ($slug) {
                'st-qa'  => '01_Surat_Tugas_QA',
                'pk-qa'  => '02_Program_Kerja_QA',
                'kk-qa'  => '03_Kertas_Kerja_QA',
                'lap-qa' => '04_Laporan_QA',
                default  => $this->sanitizeSegment($doc->documentType?->name ?? 'Lainnya', 40),
            };
            return self::MODULES['kegiatan-lainnya']['folder'] . '/Quality_Assurance/' . $typeSub . '/' . $docFolder;
        }

        if ($dbMod === 'tindak-lanjut') {
            return self::MODULES['kegiatan-lainnya']['folder'] . '/Monitoring_Tindak_Lanjut/' . $docFolder;
        }

        if ($dbMod === 'report') {
            $month = (int) ($doc->report_month ?: $this->parseMonth($doc->tanggal_formulir) ?: ($doc->created_at?->month ?? 1));
            $monthPadded = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
            return self::MODULES['report']['folder'] . '/' . $year . '/' . $monthPadded . '_' . $docFolder;
        }

        return '99_Lainnya/' . $docFolder;
    }

    private function resolvePdfSpec(Document $doc, string $slug): ?array
    {
        $safeTitle = $this->sanitizeSegment($doc->title ?: 'Dokumen', 60);

        if ($slug === 'surat-tugas' || $slug === 'st-qa') {
            $hasFormContent = $doc->attachments->isEmpty()
                || !empty($doc->tim_audit)
                || !empty($doc->lingkup_audit)
                || !empty($doc->tujuan_audit)
                || !empty($doc->tgl_stg)
                || !empty($doc->deskripsi_pembuka);

            if ($hasFormContent) {
                $prefix = $slug === 'st-qa' ? 'STQ-' : 'STG-';
                return [
                    'pdf_type'  => 'stg',
                    'file_name' => $prefix . $safeTitle . '.pdf',
                ];
            }
        }

        if ($slug === 'bak') {
            $hasFormContent = $doc->attachments->isEmpty()
                || !empty($doc->deskripsi_permasalahan)
                || !empty($doc->tujuan_konsultasi)
                || !empty($doc->bidang_tugas);

            if ($hasFormContent) {
                return [
                    'pdf_type'  => 'bak',
                    'file_name' => 'BAK-' . $safeTitle . '.pdf',
                ];
            }
        }

        if ($slug === 'monitoring-tl') {
            return [
                'pdf_type'  => 'monitoring-tl',
                'file_name' => 'Monitoring_Tindak_Lanjut_' . $safeTitle . '.pdf',
            ];
        }

        return null;
    }

    private function renderDocumentPdf(Document $document, string $pdfType): string
    {
        return match ($pdfType) {
            'stg' => \Barryvdh\DomPDF\Facade\Pdf::loadView('assurance.pdf.stg', compact('document'))
                ->setPaper('A4', 'portrait')
                ->output(),
            'bak' => \Barryvdh\DomPDF\Facade\Pdf::loadView('advisory.pdf.bak', compact('document'))
                ->setPaper('A4', 'portrait')
                ->output(),
            'monitoring-tl' => \Barryvdh\DomPDF\Facade\Pdf::loadView('other-activities.tindak-lanjut.pdf', compact('document'))
                ->setPaper('a4', 'landscape')
                ->output(),
            default => throw new \InvalidArgumentException("Unsupported PDF type: {$pdfType}"),
        };
    }

    private function makeFileEntry(
        string $menuKey,
        int $year,
        string $zipFolder,
        string $fileName,
        ?string $diskPath,
        int $recordedSize
    ): array {
        $exists = !empty($diskPath) && Storage::disk('public')->exists($diskPath);
        $actualSize = $exists ? (int) Storage::disk('public')->size($diskPath) : $recordedSize;

        return [
            'kind'       => 'file',
            'module'     => $menuKey,
            'year'       => $year,
            'zip_folder' => $zipFolder,
            'file_name'  => $this->sanitizeFileName($fileName),
            'disk_path'  => $diskPath,
            'file_size'  => $actualSize,
            'exists'     => $exists,
            'document'   => null,
            'pdf_type'   => null,
        ];
    }

    private function resolveFollowUpPicFolder(FollowUp $fu): string
    {
        $deptCode = $fu->auditeeDepartment?->code;
        $picLabel = $fu->pic_name ?: ($fu->pic_email ?: 'PIC');
        $raw = $deptCode ? "{$deptCode} - {$picLabel}" : $picLabel;

        return $this->sanitizeSegment($raw, 45) . ' #' . $fu->id;
    }

    private function resolveDocumentYear(Document $doc): int
    {
        if (!empty($doc->report_year)) {
            return (int) $doc->report_year;
        }

        return $this->parseYear($doc->tanggal_formulir)
            ?? $this->parseYear($doc->tgl_stg)
            ?? $this->parseYear($doc->tgl_lha)
            ?? $this->parseYear($doc->created_at)
            ?? (int) now()->year;
    }

    private function parseYear(mixed $value): ?int
    {
        if (empty($value)) {
            return null;
        }
        try {
            return (int) Carbon::parse($value)->year;
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseMonth(mixed $value): ?int
    {
        if (empty($value)) {
            return null;
        }
        try {
            return (int) Carbon::parse($value)->month;
        } catch (\Throwable) {
            return null;
        }
    }

    private function uniqueZipEntryPath(string $folder, string $fileName, array &$usedPaths): string
    {
        $folder = trim($folder, '/');
        $safeFileName = $this->sanitizeFileName($fileName);
        $fullPath = $folder !== '' ? $folder . '/' . $safeFileName : $safeFileName;
        $key = strtolower($fullPath);

        if (!isset($usedPaths[$key])) {
            $usedPaths[$key] = true;
            return $fullPath;
        }

        $ext  = pathinfo($safeFileName, PATHINFO_EXTENSION);
        $base = pathinfo($safeFileName, PATHINFO_FILENAME);
        $counter = 1;

        do {
            $candidate = $base . " ({$counter})" . ($ext !== '' ? '.' . $ext : '');
            $fullPath  = $folder !== '' ? $folder . '/' . $candidate : $candidate;
            $key       = strtolower($fullPath);
            $counter++;
        } while (isset($usedPaths[$key]));

        $usedPaths[$key] = true;
        return $fullPath;
    }

    private function sanitizeSegment(string $value, int $maxLength = 60): string
    {
        $clean = preg_replace('/[\\\\\/:\*\?"<>\|\x00-\x1F]+/', '-', $value);
        $clean = preg_replace('/\s+/', ' ', (string) $clean);
        $clean = trim((string) $clean, " .-\t\n\r\0\x0B");
        $clean = Str::limit($clean, $maxLength, '');
        $clean = trim($clean, " .-");

        return $clean !== '' ? $clean : 'Dokumen';
    }

    private function sanitizeFileName(string $fileName): string
    {
        $ext  = pathinfo($fileName, PATHINFO_EXTENSION);
        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $safeBase = $this->sanitizeSegment($base, 100);
        $safeExt  = preg_replace('/[^A-Za-z0-9]/', '', (string) $ext);

        return $safeExt !== '' ? $safeBase . '.' . $safeExt : $safeBase;
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return round($bytes / 1073741824, 2) . ' GB';
    }
}
