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
     * AJAX live-search across Documents.
     * Supports: no dokumen, nama/title, tanggal (YYYY-MM-DD / d M Y / d/m/Y)
     * Also supports: module filter (pkpt | assurance | advisory | pendamping | qa | all)
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

        $query = Document::with(['documentType', 'creator'])
            ->whereNull('deleted_at');

        // ── Module filter ──────────────────────────────
        if ($module === 'kegiatan-lainnya') {
            // Kegiatan Lainnya mencakup: Pendamping Eksternal, QA, Monitoring TL
            $query->whereHas('documentType', function ($q2) {
                $q2->whereIn('module', ['pendamping', 'qa', 'tindak-lanjut']);
            });
        } elseif ($module !== 'all') {
            $query->whereHas('documentType', function ($q2) use ($module) {
                $q2->where('module', $module);
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

        $query->where(function ($w) use ($q, $type, $parsedDate) {
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


        $results = $query->latest()->take(15)->get()->map(function (Document $doc) {
            // Pick the best "tanggal" field to display
            $tanggal = $doc->tgl_lha ?? $doc->tgl_pka ?? $doc->tgl_entry ?? $doc->tanggal_formulir ?? $doc->created_at;
            $mod     = $doc->documentType?->module ?? '';

            $moduleLabel = match($mod) {
                'pkpt'          => 'PKPT',
                'assurance'     => 'Assurance',
                'advisory'      => 'Advisory',
                'pendamping',
                'qa',
                'tindak-lanjut' => 'Kegiatan Lainnya',
                'report'        => 'Laporan Kinerja',
                default         => $mod,
            };

            return [
                'id'           => $doc->id,
                'uuid'         => $doc->uuid,
                'no'           => $doc->document_number ?? $doc->nomor_formulir ?? '-',
                'nama'         => $doc->title ?? $doc->judul_dokumen ?? '-',
                'type'         => $doc->documentType?->name ?? '-',
                'type_slug'    => $doc->documentType?->slug ?? '',
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
        $slug = $doc->documentType?->slug ?? '';
        $module = $doc->documentType?->module ?? '';

        try {
            return match ($module) {
                'assurance'       => route('assurance.show', $doc),
                'advisory'        => route('advisory.show', $doc),
                'other_activity'  => route('other-activities.show', $doc),
                default           => '#',
            };
        } catch (\Exception $e) {
            return '#';
        }
    }
}
