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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                <p class="text-xs text-charcoal-muted">Silakan upload bukti penyelesaian untuk setiap rekomendasi.</p>
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-charcoal font-semibold border-b border-surface-200">
                        <tr>
                            <th class="w-16 text-center px-4 py-3">No</th>
                            <th class="px-4 py-3">Temuan</th>
                            <th class="px-4 py-3">Rekomendasi</th>
                            <th class="px-4 py-3">Jatuh Tempo</th>
                            <th class="px-4 py-3">Status Persetujuan Bukti</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-200">
                        @forelse($followUps as $index => $fu)
                            <tr class="hover:bg-surface-50 transition-colors">
                                <td class="px-4 py-3 text-center text-charcoal-muted">{{ $followUps->firstItem() + $index }}</td>
                                <td class="px-4 py-3 font-medium text-charcoal max-w-xs truncate" title="{{ $fu->finding }}">{{ $fu->finding }}</td>
                                <td class="px-4 py-3 text-charcoal-muted max-w-xs truncate" title="{{ $fu->recommendation }}">{{ $fu->recommendation }}</td>
                                <td class="px-4 py-3 {{ $fu->status === 'overdue' ? 'text-red-600 font-medium' : 'text-charcoal-muted' }}">
                                    {{ \Carbon\Carbon::parse($fu->due_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
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
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('auditee.show', $fu) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 shadow-sm transition-all inline-flex" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-charcoal-muted">
                                    Tidak ada tindak lanjut untuk bidang Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($followUps->hasPages())
                <div class="p-4 border-t border-surface-200">
                    {{ $followUps->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
