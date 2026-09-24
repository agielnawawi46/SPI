<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

        return view('dashboard.index', compact(
            'user',
            'followUpStats',
            'pkptStats',
            'recentAuditLogs'
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
        $overdue    = (clone $query)->where('status', 'overdue')
                        ->orWhere(function ($q) {
                            $q->whereNotIn('status', ['closed'])
                              ->where('due_date', '<', now());
                        })->count();

        // Upcoming deadlines (due in 7 days)
        $upcomingDeadlines = (clone $query)
            ->whereNotIn('status', ['closed'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
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
}
