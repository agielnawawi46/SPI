@extends('layouts.app')
@section('title', 'Monitoring Tindak Lanjut')
@section('page_title', 'Monitoring Tindak Lanjut')

@section('content')
<div class="card" x-data="{ showModal: false, selectedDoc: '' }">
    <div class="card-header flex flex-col items-center text-center md:text-left md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="card-title">Daftar Monitoring Tindak Lanjut</h2>
            <p class="text-xs text-charcoal-muted mt-1">Kelola dokumen Monitoring Tindak Lanjut</p>
        </div>
        <div>
            <a href="{{ route('other-activities.tl.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Dokumen
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('other-activities.tl.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <x-live-search module="tindak-lanjut" placeholder="Cari dokumen TL..." />
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('other-activities.tl.index') }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    @if($documents->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/></svg>
            </div>
            <p class="empty-state-title">Belum ada dokumen</p>
            <p class="empty-state-description">Dokumen akan muncul setelah Anda membuatnya.</p>
        </div>
    @else
        {{-- Desktop Table View --}}
        <div class="hidden md:block table-wrapper">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-16 text-center">No</th>
                        <th>Judul Dokumen</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Dibuat</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $index => $doc)
                        <tr>
                            <td class="text-center text-charcoal-muted">{{ $documents->firstItem() + $index }}</td>
                            <td class="font-medium text-charcoal">
                                <a href="{{ route('other-activities.tl.show', $doc) }}" class="hover:text-indigo-600 transition-colors">
                                    {{ $doc->title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ $doc->status_color }}">
                                    {{ $doc->status_label }}
                                </span>
                            </td>
                            <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                            <td class="text-charcoal-muted text-sm">
                                {{ $doc->created_at->format('d M Y') }}
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('other-activities.tl.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if($doc->canBeEditedBy(Auth::user()))
                                    <a href="{{ route('other-activities.tl.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @endif
                                    @if($doc->canBeDeletedBy(Auth::user()))
                                    <form action="{{ route('other-activities.tl.destroy', $doc) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden space-y-4 p-4">
            @foreach($documents as $doc)
            <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                            <a href="{{ route('other-activities.tl.show', $doc) }}" class="hover:text-primary-600 transition-colors">
                                {{ $doc->title }}
                            </a>
                        </h3>
                        <span class="flex-shrink-0 badge {{ $doc->status_color }}">
                            {{ $doc->status_label }}
                        </span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Pembuat</span>
                            <span class="font-medium text-charcoal text-right">{{ $doc->creator->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Tanggal Dibuat</span>
                            <span class="font-medium text-charcoal text-right">{{ $doc->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                    <a href="{{ route('other-activities.tl.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    @if($doc->canBeEditedBy(Auth::user()))
                    <a href="{{ route('other-activities.tl.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Ubah">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @endif
                    @if($doc->canBeDeletedBy(Auth::user()))
                    <form action="{{ route('other-activities.tl.destroy', $doc) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($documents->hasPages())
        <div class="p-4 border-t border-surface-200">
            {{ $documents->links() }}
        </div>
        @endif
    @endif
</div>

{{-- Lower Table: Daftar Penugasan Tindak Lanjut --}}
@if(isset($followUps))
<div class="card mt-8">
    <div class="card-header flex flex-col items-center text-center md:text-left md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="card-title">Daftar Penugasan Tindak Lanjut Anda</h2>
            <p class="text-xs text-charcoal-muted mt-1">Daftar tugas upload bukti yang ditugaskan ke departemen Anda.</p>
        </div>
    </div>
    
    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('other-activities.tl.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="tl_search" value="{{ request('tl_search') }}" class="form-input pl-10" placeholder="Cari dokumen/temuan..." x-data x-init="if(sessionStorage.getItem('tl_search_focus') === 'true') { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); sessionStorage.removeItem('tl_search_focus'); }" @input.debounce.500ms="sessionStorage.setItem('tl_search_focus', 'true'); $event.target.form.submit()">
            </div>
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('tl_search') && request('tl_search') != '')
                <a href="{{ route('other-activities.tl.index', array_filter(['search' => request('search')])) }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>
    <div class="card-body p-0">
        <div class="hidden md:block table-wrapper">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="w-16 text-center">No</th>
                        <th>Dokumen Asal</th>
                        <th>Temuan (LHA)</th>
                        <th>PIC / Email</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Persetujuan Bukti</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($followUps as $index => $followUp)
                        <tr>
                            <td class="text-center text-charcoal-muted">{{ (method_exists($followUps, 'firstItem') ? $followUps->firstItem() : 1) + $index }}</td>
                            <td class="max-w-xs truncate" title="{{ $followUp->document->title }}">{{ Str::limit($followUp->document->title, 40) }}</td>
                            <td class="max-w-xs truncate" title="{{ $followUp->finding }}">{{ Str::limit($followUp->finding, 50) }}</td>
                            <td>
                                <div class="font-medium text-charcoal">{{ $followUp->assignee->name ?? '-' }}</div>
                                <div class="text-xs text-charcoal-muted">{{ $followUp->assignee->email ?? '-' }}</div>
                            </td>
                            <td class="{{ $followUp->status === 'overdue' ? 'text-red-600 font-medium' : 'text-charcoal-muted' }}">
                                {{ \Carbon\Carbon::parse($followUp->due_date)->format('d M Y') }}
                            </td>
                            <td>
                                @if($followUp->approval_status === 'pending' && $followUp->evidences_count > 0)
                                    <span class="badge badge-in-progress">Draf (Belum Dikirim)</span>
                                @elseif($followUp->status === 'closed' || $followUp->approval_status === 'approved')
                                    <span class="badge badge-approved">Closed</span>
                                @else
                                    <span class="badge {{ $followUp->approval_status === 'rejected' ? 'badge-rejected' : 'badge-pending' }}">
                                        {{ str_replace('_', ' ', $followUp->approval_status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('auditee.show', $followUp) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @if($followUp->status === 'closed' || Auth::user()->hasRole('admin'))
                                    <form action="{{ route('other-activities.tl.follow-up.destroy', $followUp) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas tindak lanjut ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-charcoal-muted">
                                Tidak ada penugasan tindak lanjut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden space-y-4 p-4">
            @forelse($followUps as $followUp)
            <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                            <a href="{{ route('auditee.show', $followUp) }}" class="hover:text-primary-600 transition-colors">
                                {{ $followUp->finding }}
                            </a>
                        </h3>
                        <span class="flex-shrink-0 badge 
                            @if($followUp->approval_status === 'pending' && $followUp->evidences_count > 0)
                                badge-in-progress
                            @elseif($followUp->status === 'closed' || $followUp->approval_status === 'approved')
                                badge-approved
                            @elseif($followUp->approval_status === 'rejected')
                                badge-rejected
                            @else
                                badge-pending
                            @endif">
                            @if($followUp->approval_status === 'pending' && $followUp->evidences_count > 0)
                                Draf
                            @elseif($followUp->status === 'closed' || $followUp->approval_status === 'approved')
                                Closed
                            @else
                                {{ str_replace('_', ' ', $followUp->approval_status) }}
                            @endif
                        </span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Dokumen Asal</span>
                            <span class="font-medium text-charcoal text-right max-w-[150px] truncate" title="{{ $followUp->document->title }}">{{ $followUp->document->title }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">PIC</span>
                            <span class="font-medium text-charcoal text-right">{{ $followUp->assignee->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Jatuh Tempo</span>
                            <span class="font-medium text-charcoal text-right {{ $followUp->status === 'overdue' ? 'text-red-600' : '' }}">{{ \Carbon\Carbon::parse($followUp->due_date)->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                    <a href="{{ route('auditee.show', $followUp) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>

                    @if($followUp->status === 'closed' || Auth::user()->hasRole('admin'))
                    <form action="{{ route('other-activities.tl.follow-up.destroy', $followUp) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas tindak lanjut ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
                <div class="text-center py-6 text-charcoal-muted">
                    Tidak ada penugasan tindak lanjut.
                </div>
            @endforelse
        </div>
        
        @if($followUps->hasPages())
        <div class="p-4 border-t border-surface-200">
            {{ $followUps->links() }}
        </div>
        @endif
    </div>
</div>
@endif

@endsection

