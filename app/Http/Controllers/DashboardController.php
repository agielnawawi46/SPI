<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Follow-up statistics
        $followUpStats = $this->getFollowUpStats($user);

        // PKPT realization
        $pkptStats = $this->getPkptStats();

        // Recent activity
        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        $isAuditee   = $user->isAuditee();
        $isKomisaris = $user->isKomisaris();

        return view('dashboard.index', compact(
            'user',
            'followUpStats',
            'pkptStats',
            'recentAuditLogs',
            'isAuditee',
            'isKomisaris'
        ));
    }

    private function getFollowUpStats($user): array
    {
        $query = \App\Models\FollowUp::query();

        // Auditee can only see their department's follow-ups
        if ($user->isAuditee() && $user->department_id) {
            $query->where('auditee_department_id', $user->department_id);
        }

        $total      = (clone $query)->count();
        $open       = (clone $query)->where('status', 'open')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $completed  = (clone $query)->where('status', 'closed')->count();
        $overdue    = (clone $query)->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($q2) {
                  $q2->whereNotIn('status', ['closed'])
                     ->where('due_date', '<', now());
              });
        })->count();

        // Upcoming deadlines (overdue + due in 7 days)
        $upcomingDeadlines = (clone $query)
            ->whereNotIn('status', ['closed'])
            ->where('due_date', '<=', now()->addDays(7))
            ->with('auditeeDepartment')
            ->orderBy('due_date')
            ->take(10)
            ->get();

        return compact('total', 'open', 'inProgress', 'completed', 'overdue', 'upcomingDeadlines');
    }

    private function getPkptStats(): array
    {
        $currentYear = now()->year;
        $pkpts       = \App\Models\Pkpt::where('period_year', $currentYear)->get();

        $totalItems     = \App\Models\PkptItem::whereHas('pkpt', fn($q) => $q->where('period_year', $currentYear))->count();
        $completedItems = \App\Models\AuditAssignment::where('status', 'completed')
            ->whereYear('created_at', $currentYear)->count();

        $realization = $totalItems > 0
            ? round(($completedItems / $totalItems) * 100, 1)
            : 0;

        return [
            'year'           => $currentYear,
            'total_items'    => $totalItems,
            'completed'      => $completedItems,
            'realization_pct'=> $realization,
            'pkpts'          => $pkpts,
        ];
    }

    /**
     * AJAX live-search across Documents + Pkpt (PKPT Utama).
     * Supports: no dokumen, nama/title, tanggal (YYYY-MM-DD / d M Y / d/m/Y)
     * Also supports: module filter (pkpt | assurance | advisory | pendamping | qa | all)
     * PKPT Utama (model Pkpt) dan Kertas Kerja PKPT (Document) dibedakan labelnya.
     */
    public function search(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $type   = $request->get('type', 'all');   // all | no | nama | tanggal
        $module = $request->get('module', 'all'); // all | pkpt | assurance | advisory | pendamping | qa

        if (strlen($q) < 2) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        $user = Auth::user();

        // ── Auditee restriction ─────────────────────────
        // Auditee hanya boleh melihat dokumen Advisory
        if ($user->isAuditee()) {
            $module = 'advisory'; // paksa, tidak bisa di-override
        }

        // ── 1. Search Documents ────────────────────────
        $docQuery = Document::with(['documentType', 'creator'])
            ->whereNull('deleted_at');

        // Module filter untuk Documents
        if ($module === 'pkpt') {
            $docQuery->whereHas('documentType', function ($q2) {
                $q2->where('module', 'pkpt');
            });
        } elseif ($module === 'kertas-kerja-pkpt') {
            $docQuery->whereHas('documentType', function ($q2) {
                $q2->where('module', 'pkpt')->where('slug', 'kertas-kerja-pkpt');
            });
        } elseif ($module === 'pkpt-utama') {
            $docQuery->whereRaw('1 = 0');
        } elseif ($module === 'kegiatan-lainnya') {
            $docQuery->whereHas('documentType', function ($q2) {
                $q2->whereIn('module', ['pendamping', 'qa', 'tindak-lanjut']);
            });
        } elseif ($module !== 'all') {
            $docQuery->whereHas('documentType', function ($q2) use ($module) {
                $q2->where('module', $module);
            });
        } else {
            // Saat 'all': exclude slug 'pkpt' dari Document karena PKPT asli
            // dicari via model Pkpt langsung (di bawah)
            $docQuery->whereHas('documentType', function ($q2) {
                $q2->where('slug', '!=', 'pkpt');
            });
        }

        // ── Try to parse as date for tanggal search ───
        $parsedDate = null;
        if ($type === 'tanggal' || $type === 'all') {
            try {
                $parsedDate = \Carbon\Carbon::parse($q)->format('Y-m-d');
            } catch (\Exception $e) {
                $parsedDate = null;
            }
        }

        $docQuery->where(function ($w) use ($q, $type, $parsedDate) {
            if ($type === 'no' || $type === 'all') {
                $w->orWhere('document_number', 'like', "%{$q}%");
                $w->orWhere('nomor_formulir',  'like', "%{$q}%");
            }
            if ($type === 'nama' || $type === 'all') {
                $w->orWhere('title',         'like', "%{$q}%");
                $w->orWhere('judul_dokumen', 'like', "%{$q}%");
            }
            if (($type === 'tanggal' || $type === 'all') && $parsedDate) {
                $w->orWhereDate('tgl_lha',          $parsedDate);
                $w->orWhereDate('tgl_pka',          $parsedDate);
                $w->orWhereDate('tgl_entry',        $parsedDate);
                $w->orWhereDate('tanggal_formulir', $parsedDate);
                $w->orWhereDate('created_at',       $parsedDate);
            }
        });

        $docResults = $docQuery->latest()->take(12)->get()->map(function (Document $doc) {
            $tanggal = $doc->tgl_lha ?? $doc->tgl_pka ?? $doc->tgl_entry ?? $doc->tanggal_formulir ?? $doc->created_at;
            $mod     = $doc->documentType?->module ?? '';
            $slug    = $doc->documentType?->slug ?? '';

            // Label yang jelas membedakan Kertas Kerja PKPT vs dokumen lainnya
            $moduleLabel = match(true) {
                $mod === 'pkpt' && $slug === 'kertas-kerja-pkpt' => 'Kertas Kerja PKPT',
                $mod === 'pkpt'                                  => 'PKPT',
                $mod === 'assurance'                             => 'Assurance',
                $mod === 'advisory'                              => 'Advisory',
                in_array($mod, ['pendamping', 'qa', 'tindak-lanjut']) => 'Kegiatan Lainnya',
                $mod === 'report'                                => 'Laporan Kinerja',
                default                                          => $mod,
            };

            return [
                'id'           => $doc->id,
                'uuid'         => $doc->uuid,
                'no'           => $doc->document_number ?? $doc->nomor_formulir ?? '-',
                'nama'         => $doc->title ?? $doc->judul_dokumen ?? '-',
                'type'         => $doc->documentType?->name ?? '-',
                'type_slug'    => $slug,
                'module'       => $mod,
                'module_label' => $moduleLabel,
                'status'       => $doc->status,
                'status_label' => $doc->status_label,
                'status_color' => $doc->status_color,
                'tanggal'      => $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y') : '-',
                'creator'      => $doc->creator?->name ?? '-',
                'url'          => $this->resolveDocumentUrl($doc),
            ];
        });

        // ── 2. Search Pkpt model (PKPT Utama) ─────────
        // PKPT Utama disimpan di tabel `pkpts` (bukan `documents`)
        $pkptResults = collect();
        if (!$user->isAuditee() && in_array($module, ['all', 'pkpt', 'pkpt-utama'])) {
            $pkptQuery = \App\Models\Pkpt::with('creator')->whereNull('deleted_at');

            $pkptQuery->where(function ($w) use ($q, $type, $parsedDate) {
                if ($type === 'no' || $type === 'nama' || $type === 'all') {
                    $w->orWhere('title', 'like', "%{$q}%")
                      ->orWhere('period_year', 'like', "%{$q}%");
                }
                if (($type === 'tanggal' || $type === 'all') && $parsedDate) {
                    $w->orWhereDate('created_at', $parsedDate);
                }
            });

            $pkptResults = $pkptQuery->latest()->take(5)->get()->map(function (\App\Models\Pkpt $pkpt) {
                return [
                    'id'           => 'pkpt-' . $pkpt->id,
                    'uuid'         => null,
                    'no'           => (string) $pkpt->period_year,
                    'nama'         => $pkpt->title,
                    'type'         => 'PKPT Utama',
                    'type_slug'    => 'pkpt',
                    'module'       => 'pkpt',
                    'module_label' => 'PKPT Utama',
                    'status'       => $pkpt->status,
                    'status_label' => $pkpt->status_label,
                    'status_color' => 'badge-draft',
                    'tanggal'      => $pkpt->created_at?->format('d M Y') ?? '-',
                    'creator'      => $pkpt->creator?->name ?? '-',
                    'url'          => route('pkpt.show', $pkpt),
                ];
            });
        }

        // Gabungkan: PKPT Utama tampil di atas, lalu dokumen lainnya
        $results = $pkptResults->concat($docResults)->take(15)->values();

        return response()->json([
            'results' => $results,
            'total'   => $results->count(),
            'query'   => $q,
        ]);
    }

    /**
     * Resolve the correct detail URL based on document type module.
     */
    private function resolveDocumentUrl(Document $doc): string
    {
        $module = $doc->documentType?->module ?? '';
        $slug   = $doc->documentType?->slug ?? '';

        try {
            return match ($module) {
                'assurance'     => route('assurance.show', $doc),
                'advisory'      => route('advisory.show', $doc),
                'tindak-lanjut' => route('other-activities.tl.show', $doc),
                'pendamping'    => route('other-activities.pendamping.index'),
                'qa'            => route('other-activities.qa.show', $doc),
                'report'        => route('performance-reports.show', $doc),
                'pkpt'          => $slug === 'kertas-kerja-pkpt' && $doc->pkpt_id
                                    ? route('pkpt.kertas-kerja', $doc->pkpt_id)
                                    : route('pkpt.show', $doc->pkpt_id ?? $doc->id),
                default         => '#',
            };
        } catch (\Exception $e) {
            return '#';
        }
    }
}
