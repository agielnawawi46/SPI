@extends('layouts.app')
@section('title', 'Laporan Kinerja')
@section('page_title', 'Laporan Kinerja Bulanan')

@section('content')
<div class="card" x-data="{ showModal: false, selectedDoc: '' }">
    <div class="card-header flex flex-col items-start text-left gap-3">
        <div>
            <h2 class="card-title">Daftar Laporan Kinerja</h2>
            <p class="text-xs text-charcoal-muted mt-1">Rekap laporan kinerja SPI per bulan</p>
        </div>
        @can('report.create')
        <div>
            <a href="{{ route('performance-reports.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Laporan
            </a>
        </div>
        @endcan
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('performance-reports.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <x-live-search module="report" placeholder="Cari laporan kinerja..." />
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('performance-reports.index') }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block table-wrapper">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="w-16 text-center">No</th>
                    <th>Judul Laporan</th>
                    <th>Periode</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    <th>Tanggal Buat</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $index => $report)
                <tr>
                    <td class="text-center text-charcoal-muted">{{ $reports->firstItem() + $index }}</td>
                    <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $report->title }}">{{ $report->title }}</td>
                    <td class="text-charcoal-muted font-medium">
                        {{ $report->report_month ? \Carbon\Carbon::create(null, $report->report_month)->format('F') . ' ' . $report->report_year : '-' }}
                    </td>
                    <td class="text-charcoal-muted">{{ $report->creator?->name ?? '-' }}</td>
                    <td><span class="badge {{ $report->status_color }}">{{ $report->status_label }}</span></td>
                    <td class="text-charcoal-muted text-xs whitespace-nowrap">{{ $report->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('performance-reports.show', $report) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            
                            @if($report->canBeEditedBy(Auth::user()))
                            <a href="{{ route('performance-reports.edit', $report) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h3 class="empty-state-title">Belum ada laporan</h3>
                            <p class="empty-state-description">Data kosong untuk Laporan Kinerja.</p>
                            @can('report.create')
                            <a href="{{ route('performance-reports.create') }}" class="btn-primary mt-2">Buat Laporan Baru</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden space-y-4 p-4">
        @forelse($reports as $report)
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-start gap-4 mb-3">
                    <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                        <a href="{{ route('performance-reports.show', $report) }}" class="hover:text-primary-600 transition-colors">
                            {{ $report->title }}
                        </a>
                    </h3>
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
                        <span class="font-medium text-charcoal">{{ $report->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                <a href="{{ route('performance-reports.show', $report) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                
                @if($report->canBeEditedBy(Auth::user()))
                <a href="{{ route('performance-reports.edit', $report) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state border border-surface-200 rounded-xl m-4">
            <div class="empty-state-icon">
                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="empty-state-title">Belum ada laporan</h3>
            <p class="empty-state-description">Data kosong untuk Laporan Kinerja.</p>
        </div>
        @endforelse
    </div>

    @if($reports->hasPages())
    <div class="pagination-wrapper p-4 border-t border-surface-200">
        {{ $reports->links() }}
    </div>
    @endif
</div>
@endsection
