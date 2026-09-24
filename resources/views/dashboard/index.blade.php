@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumbs')
    <span class="breadcrumb-current">Dashboard</span>
@endsection

@section('content')

{{-- ============================================
     STATS CARDS
     ============================================ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Total TL --}}
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Total Tindak Lanjut</p>
                <p class="stat-card-value">{{ number_format($followUpStats['total']) }}</p>
            </div>
            <div class="stat-card-icon bg-primary-50">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/></svg>
            </div>
        </div>
        <div class="flex gap-3 mt-3">
            <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full font-medium">{{ $followUpStats['completed'] }} selesai</span>
            <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-medium">{{ $followUpStats['inProgress'] }} proses</span>
        </div>
    </div>

    {{-- TL Open --}}
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Belum Ditindaklanjuti</p>
                <p class="stat-card-value text-orange-600">{{ number_format($followUpStats['open']) }}</p>
            </div>
            <div class="stat-card-icon bg-orange-50">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-xs text-charcoal-muted mt-3">Menunggu tindakan Auditee</p>
    </div>

    {{-- TL Overdue --}}
    <div class="stat-card {{ $followUpStats['overdue'] > 0 ? 'border-l-4 border-l-red-500' : '' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Melewati Jatuh Tempo</p>
                <p class="stat-card-value {{ $followUpStats['overdue'] > 0 ? 'text-red-600' : '' }}">{{ number_format($followUpStats['overdue']) }}</p>
            </div>
            <div class="stat-card-icon {{ $followUpStats['overdue'] > 0 ? 'bg-red-50' : 'bg-gray-50' }}">
                <svg class="w-5 h-5 {{ $followUpStats['overdue'] > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        @if($followUpStats['overdue'] > 0)
        <p class="text-xs text-red-500 mt-3 font-medium">⚠ Perlu perhatian segera</p>
        @else
        <p class="text-xs text-charcoal-muted mt-3">Tidak ada yang melewati batas</p>
        @endif
    </div>

    {{-- Realisasi PKPT --}}
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Realisasi PKPT {{ $pkptStats['year'] }}</p>
                <p class="stat-card-value text-primary-600">{{ $pkptStats['realization_pct'] }}%</p>
            </div>
            <div class="stat-card-icon bg-primary-50">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="mt-3">
            <div class="flex justify-between text-xs text-charcoal-muted mb-1">
                <span>{{ $pkptStats['completed'] }} / {{ $pkptStats['total_items'] }} item</span>
            </div>
            <div class="w-full bg-surface-100 rounded-full h-1.5">
                <div class="bg-primary-500 h-1.5 rounded-full transition-all duration-700" style="width: {{ $pkptStats['realization_pct'] }}%"></div>
            </div>
        </div>
    </div>

</div>

{{-- ============================================
     CHARTS ROW
     ============================================ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- TL Status Chart --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <h2 class="card-title">Status Tindak Lanjut</h2>
            <span class="text-xs text-charcoal-muted">Keseluruhan</span>
        </div>
        <div class="card-body">
            <canvas id="followUpStatusChart" height="120"></canvas>
        </div>
    </div>

    {{-- PKPT Realization Doughnut --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Realisasi PKPT</h2>
            <span class="text-xs text-charcoal-muted">{{ $pkptStats['year'] }}</span>
        </div>
        <div class="card-body flex flex-col items-center">
            <div class="relative w-40 h-40">
                <canvas id="pkptDonutChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-bold text-charcoal">{{ $pkptStats['realization_pct'] }}%</span>
                    <span class="text-xs text-charcoal-muted">Terealisasi</span>
                </div>
            </div>
            <div class="flex gap-4 mt-4 text-xs">
                <div class="flex items-center gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary-500"></div>
                    <span class="text-charcoal-muted">Selesai ({{ $pkptStats['completed'] }})</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-surface-200"></div>
                    <span class="text-charcoal-muted">Sisa ({{ $pkptStats['total_items'] - $pkptStats['completed'] }})</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ============================================
     UPCOMING DEADLINES TABLE
     ============================================ --}}
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Tindak Lanjut Mendekati Jatuh Tempo</h2>
        <a href="#" class="btn-outline btn-sm">Lihat Semua</a>
    </div>

    @if($followUpStats['upcomingDeadlines']->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="empty-state-title">Semua Tindak Lanjut Aman</p>
            <p class="empty-state-description">Tidak ada TL yang mendekati jatuh tempo dalam 7 hari ke depan.</p>
        </div>
    @else
        <div class="hidden md:block table-wrapper">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Temuan</th>
                        <th>Bidang</th>
                        <th>Jatuh Tempo</th>
                        <th>Sisa Hari</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($followUpStats['upcomingDeadlines'] as $tl)
                    <tr>
                        <td class="max-w-xs">
                            <p class="font-medium text-charcoal truncate">{{ $tl->finding }}</p>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $tl->auditeeDepartment?->code ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            {{ $tl->due_date->format('d M Y') }}
                        </td>
                        <td>
                            @php $days = $tl->days_remaining; @endphp
                            <span class="font-semibold {{ $days <= 3 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $days <= 0 ? 'Terlambat' : $days . ' hari' }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $tl->status_color }}">{{ $tl->status_label }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('auditee.show', $tl) }}" class="btn-outline btn-sm">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-4">
            @foreach($followUpStats['upcomingDeadlines'] as $tl)
            @php $days = $tl->days_remaining; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <h3 class="font-semibold text-charcoal text-base leading-snug">{{ $tl->finding }}</h3>
                        <span class="flex-shrink-0 {{ $tl->status_color }}">{{ $tl->status_label }}</span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Bidang</span>
                            <span class="badge badge-primary">{{ $tl->auditeeDepartment?->code ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Jatuh Tempo</span>
                            <span class="font-medium text-charcoal">{{ $tl->due_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Sisa Hari</span>
                            <span class="font-semibold {{ $days <= 3 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $days <= 0 ? 'Terlambat' : $days . ' hari' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-50 px-4 py-3 flex items-center justify-end border-t border-surface-200">
                    <a href="{{ route('auditee.show', $tl) }}" class="btn-outline btn-sm bg-white">Lihat Detail</a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
window.addEventListener('load', function () {

    // Follow-up Status Bar Chart
    new Chart(document.getElementById('followUpStatusChart'), {
        type: 'bar',
        data: {
            labels: ['Belum Ditindaklanjuti', 'Dalam Proses', 'Selesai', 'Terlambat'],
            datasets: [{
                label: 'Jumlah TL',
                data: [
                    {{ $followUpStats['open'] }},
                    {{ $followUpStats['inProgress'] }},
                    {{ $followUpStats['completed'] }},
                    {{ $followUpStats['overdue'] }},
                ],
                backgroundColor: [
                    'rgba(249, 115, 22, 0.85)',  // orange - open
                    'rgba(37, 99, 235, 0.85)',    // blue - in progress
                    'rgba(22, 163, 74, 0.85)',    // green - completed
                    'rgba(220, 38, 38, 0.85)',    // red - overdue
                ],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                },
                x: {
                    grid: { display: false },
                }
            }
        }
    });

    // PKPT Doughnut Chart
    new Chart(document.getElementById('pkptDonutChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [{{ $pkptStats['completed'] }}, {{ $pkptStats['total_items'] - $pkptStats['completed'] }}],
                backgroundColor: ['#158b9f', '#e9ecf0'],
                borderWidth: 0,
                hoverBorderWidth: 0,
            }]
        },
        options: {
            cutout: '78%',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
            }
        }
    });

});
</script>
@endpush
