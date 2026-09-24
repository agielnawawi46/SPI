@extends('layouts.app')
@section('title', 'Laporan Kinerja')
@section('page_title', 'Laporan Kinerja Bulanan')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card" style="border-top: 3px solid #ec4899;">
        <div class="card-header flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h2 class="card-title">Laporan Kinerja Bulanan</h2>
                    <p class="text-xs text-charcoal-muted">Rekap laporan kinerja SPI per bulan</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge badge-primary hidden md:inline-flex">{{ $reports->total() }} laporan</span>
                @can('report.create')
                <a href="{{ route('performance-reports.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Laporan
                </a>
                @endcan
            </div>
        </div>
        <div class="table-wrapper">
            @if($reports->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="empty-state-title">Belum ada laporan kinerja</p>
                    <p class="empty-state-description">Mulai rekam performa tim SPI dengan membuat Laporan Kinerja bulanan pertama.</p>
                    @can('report.create')
                    <a href="{{ route('performance-reports.create') }}" class="btn-primary btn-sm mt-2">+ Buat Laporan</a>
                    @endcan
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="data-table w-full">
                        <thead><tr>
                            <th>Judul Laporan</th><th>Periode</th><th>Dibuat Oleh</th><th>Status</th><th>Tanggal Buat</th><th class="text-right">Aksi</th>
                        </tr></thead>
                        <tbody>
                        @foreach($reports as $report)
                            <tr>
                                <td class="font-medium text-charcoal">{{ $report->title }}</td>
                                <td class="text-charcoal-muted font-medium">
                                    {{ $report->report_month ? \Carbon\Carbon::create(null, $report->report_month)->format('F') . ' ' . $report->report_year : '-' }}
                                </td>
                                <td class="text-charcoal-muted">{{ $report->creator?->name ?? '-' }}</td>
                                <td><span class="badge {{ $report->status_color }}">{{ $report->status_label }}</span></td>
                                <td class="text-xs text-charcoal-muted">{{ $report->created_at->format('d M Y') }}</td>
                                <td class="text-right"><a href="{{ route('performance-reports.show', $report) }}" class="btn-outline btn-sm">Lihat</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-4">
                    @foreach($reports as $report)
                    <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                        <div class="p-4">
                            <div class="flex justify-between items-start gap-4 mb-3">
                                <h3 class="font-semibold text-charcoal text-base leading-snug">{{ $report->title }}</h3>
                                <span class="flex-shrink-0 badge {{ $report->status_color }}">{{ $report->status_label }}</span>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                                    <span class="text-charcoal-muted">Periode</span>
                                    <span class="font-medium text-charcoal">{{ $report->report_month ? \Carbon\Carbon::create(null, $report->report_month)->format('F') . ' ' . $report->report_year : '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                                    <span class="text-charcoal-muted">Dibuat Oleh</span>
                                    <span class="font-medium text-charcoal">{{ $report->creator?->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-charcoal-muted">Tanggal Buat</span>
                                    <span class="font-medium text-charcoal">{{ $report->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-surface-50 px-4 py-3 flex items-center justify-end border-t border-surface-200">
                            <a href="{{ route('performance-reports.show', $report) }}" class="btn-outline btn-sm bg-white">Lihat Detail</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="pagination-wrapper">
                    <p class="text-xs text-charcoal-muted">Menampilkan {{ $reports->firstItem() }}–{{ $reports->lastItem() }} dari {{ $reports->total() }}</p>
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
