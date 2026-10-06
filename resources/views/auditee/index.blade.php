@extends('layouts.app')
@section('title', 'Tindak Lanjut')
@section('page_title', 'Tindak Lanjut Rekomendasi')

@section('content')
<div class="space-y-6">
    <div class="page-header">
        <div>
            <h2 class="page-title">Tindak Lanjut Rekomendasi</h2>
            <p class="page-subtitle">Daftar rekomendasi audit yang perlu ditindaklanjuti oleh bidang Anda</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="stat-card-icon bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="stat-card-value text-orange-600 mt-0">{{ $stats['open'] }}</p>
                    <p class="stat-card-label">Belum Ditangani</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="stat-card-icon bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <p class="stat-card-value text-blue-600 mt-0">{{ $stats['in_progress'] }}</p>
                    <p class="stat-card-label">Sedang Berjalan</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="stat-card-icon bg-emerald-100 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="stat-card-value text-emerald-600 mt-0">{{ $stats['completed'] }}</p>
                    <p class="stat-card-label">Selesai</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-4">
                <div class="stat-card-icon bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="stat-card-value text-red-600 mt-0">{{ $stats['overdue'] }}</p>
                    <p class="stat-card-label">Melewati Jatuh Tempo</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Daftar Tindak Lanjut</h2>
                <p class="text-xs text-charcoal-muted mt-1">Silakan upload bukti penyelesaian untuk setiap rekomendasi.</p>
            </div>
        </div>

        <div class="p-4 border-b border-surface-200 bg-surface-50">
            <form action="{{ route('auditee.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10" placeholder="Cari temuan atau rekomendasi..." x-data x-init="if(sessionStorage.getItem('search_focus') === 'true') { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); sessionStorage.removeItem('search_focus'); }" @input.debounce.500ms="sessionStorage.setItem('search_focus', 'true'); $event.target.form.submit()">
                </div>
                <button type="submit" class="btn-secondary">Cari</button>
                @if(request()->has('search') && request('search') != '')
                    <a href="{{ route('auditee.index') }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrapper">
            <div class="hidden md:block overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">No</th>
                            <th>Temuan</th>
                            <th>Rekomendasi</th>
                            <th>Jatuh Tempo</th>
                            <th>Status Persetujuan Bukti</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($followUps as $index => $fu)
                            <tr>
                                <td class="text-center text-charcoal-muted">{{ $followUps->firstItem() + $index }}</td>
                                <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $fu->finding }}">{{ $fu->finding }}</td>
                                <td class="text-charcoal-muted max-w-xs truncate" title="{{ $fu->recommendation }}">{{ $fu->recommendation }}</td>
                                <td class="{{ $fu->status === 'overdue' ? 'text-red-600 font-medium' : 'text-charcoal-muted' }}">
                                    {{ \Carbon\Carbon::parse($fu->due_date)->format('d M Y') }}
                                </td>
                                <td>
                                    @if($fu->approval_status === 'pending' && $fu->evidences_count > 0)
                                        <span class="badge badge-in-progress">Draf (Belum Dikirim)</span>
                                    @elseif($fu->status === 'closed' || $fu->approval_status === 'approved')
                                        <span class="badge badge-approved">Closed</span>
                                    @else
                                        <span class="badge {{ $fu->approval_status === 'rejected' ? 'badge-rejected' : 'badge-pending' }}">
                                            {{ ucwords(str_replace('_', ' ', $fu->approval_status)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('auditee.show', $fu) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 shadow-sm transition-all inline-flex" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <h3 class="empty-state-title">Tidak ada tindak lanjut</h3>
                                        <p class="empty-state-description">Tidak ada tindak lanjut untuk bidang Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden space-y-4 p-4">
                @forelse($followUps as $fu)
                <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-4 mb-3">
                            <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                                <a href="{{ route('auditee.show', $fu) }}" class="hover:text-primary-600 transition-colors">
                                    {{ $fu->finding }}
                                </a>
                            </h3>
                            <span class="flex-shrink-0">
                                @if($fu->approval_status === 'pending' && $fu->evidences_count > 0)
                                    <span class="badge badge-in-progress">Draf</span>
                                @elseif($fu->status === 'closed' || $fu->approval_status === 'approved')
                                    <span class="badge badge-approved">Closed</span>
                                @else
                                    <span class="badge {{ $fu->approval_status === 'rejected' ? 'badge-rejected' : 'badge-pending' }}">
                                        {{ ucwords(str_replace('_', ' ', $fu->approval_status)) }}
                                    </span>
                                @endif
                            </span>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                                <span class="text-charcoal-muted">Rekomendasi</span>
                                <span class="font-medium text-charcoal max-w-[150px] truncate" title="{{ $fu->recommendation }}">{{ $fu->recommendation }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-charcoal-muted">Jatuh Tempo</span>
                                <span class="font-medium {{ $fu->status === 'overdue' ? 'text-red-600 font-medium' : 'text-charcoal' }}">{{ \Carbon\Carbon::parse($fu->due_date)->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                        <a href="{{ route('auditee.show', $fu) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 shadow-sm transition-all" title="Lihat Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-state border border-surface-200 rounded-xl m-4">
                    <div class="empty-state-icon">
                        <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="empty-state-title">Tidak ada tindak lanjut</h3>
                    <p class="empty-state-description">Tidak ada tindak lanjut untuk bidang Anda.</p>
                </div>
                @endforelse
            </div>

            @if($followUps->hasPages())
                <div class="pagination-wrapper p-4 border-t border-surface-200">
                    {{ $followUps->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
