@extends('layouts.app')

@section('title', 'Program Kerja Pengawasan Tahunan (PKPT)')
@section('page_title', 'Program Kerja Pengawasan Tahunan (PKPT)')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">PKPT</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="card-title">Daftar PKPT</h2>
            <p class="text-xs text-charcoal-muted mt-1">Kelola Program Kerja Pengawasan Tahunan (PKPT) di sini.</p>
        </div>
        @can('pkpt.create')
        <div>
            <a href="{{ route('pkpt.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah PKPT
            </a>
        </div>
        @endcan
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('pkpt.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <x-live-search module="pkpt-utama" placeholder="Cari judul atau tahun PKPT..." />
            
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('pkpt.index') }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-16 text-center">No</th>
                    <th>Judul PKPT</th>
                    <th>Tahun</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pkpts as $index => $pkpt)
                @php
                    $fileCount = $pkpt->file_count;
                @endphp
                <tr>
                    <td class="text-center text-charcoal-muted">{{ $pkpts->firstItem() + $index }}</td>
                    <td class="font-medium text-charcoal">
                        <div class="flex flex-col items-start gap-1">
                            <span class="text-charcoal font-semibold text-sm">{{ $pkpt->title }}</span>
                            @if($fileCount > 1)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-primary-50 text-primary-700 border border-primary-200/80 shadow-2xs">
                                    <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    {{ $fileCount }} Berkas
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $pkpt->period_year }}</td>
                    <td>
                        <span class="badge {{ match($pkpt->status) { 'draft' => 'badge-draft', 'pending_kspi', 'pending_direktur' => 'badge-pending', 'approved' => 'badge-approved', 'rejected' => 'badge-rejected', default => 'badge-draft' } }}">
                            {{ $pkpt->status_label }}
                        </span>
                    </td>
                    <td>{{ $pkpt->creator->name ?? '-' }}</td>
                    <td class="text-charcoal-muted">{{ $pkpt->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($fileCount === 1)
                                <a href="{{ route('pkpt.view-file', $pkpt) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Berkas (1 Dokumen)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('pkpt.download', $pkpt) }}" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-80 shadow-sm transition-all" title="Unduh Berkas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                            @elseif($fileCount > 1)
                                <a href="{{ route('pkpt.show', $pkpt) }}" class="btn-icon relative bg-primary-600 text-white hover:bg-primary-500 hover:opacity-90 shadow-sm transition-all" title="Lihat Detail ({{ $fileCount }} Berkas)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span class="absolute -top-1 -right-1 px-1 min-w-[15px] h-[15px] text-[9.5px] font-bold bg-amber-400 text-charcoal rounded-full flex items-center justify-center border border-white leading-none shadow-2xs">{{ $fileCount }}</span>
                                </a>
                                <a href="{{ route('pkpt.download', $pkpt) }}" class="btn-icon relative bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all" title="Unduh Semua ({{ $fileCount }} Berkas .ZIP)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span class="absolute -top-1 -right-1 px-1 min-w-[15px] h-[15px] text-[9.5px] font-bold bg-amber-400 text-charcoal rounded-full flex items-center justify-center border border-white leading-none shadow-2xs">ZIP</span>
                                </a>
                            @else
                                <a href="{{ route('pkpt.show', $pkpt) }}" class="btn-icon bg-surface-200 text-charcoal-muted hover:bg-surface-300 shadow-sm transition-all" title="Lihat Detail (Belum ada berkas)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            @endif
                            
                            @can('pkpt.edit')
                            <a href="{{ route('pkpt.edit', $pkpt) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endcan
                            
                            @can('pkpt.delete')
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-pkpt-{{ $pkpt->id }}')" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
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
                            <h3 class="empty-state-title">Belum ada data PKPT</h3>
                            <p class="empty-state-description">Data PKPT yang ditambahkan akan muncul di sini.</p>
                            @can('pkpt.create')
                            <a href="{{ route('pkpt.create') }}" class="btn-primary mt-2">Tambah PKPT Baru</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden space-y-4">
        @forelse($pkpts as $index => $pkpt)
        @php
            $fileCount = $pkpt->file_count;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-start gap-4 mb-3">
                    <div class="flex flex-col items-start gap-1">
                        <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                            <a href="{{ route('pkpt.show', $pkpt) }}" class="hover:text-primary-600 transition-colors">
                                {{ $pkpt->title }}
                            </a>
                        </h3>
                        @if($fileCount > 1)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-primary-50 text-primary-700 border border-primary-200/80 shadow-2xs">
                                <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                {{ $fileCount }} Berkas
                            </span>
                        @endif
                    </div>
                    <span class="flex-shrink-0 badge {{ match($pkpt->status) { 'draft' => 'badge-draft', 'pending_kspi', 'pending_direktur' => 'badge-pending', 'approved' => 'badge-approved', 'rejected' => 'badge-rejected', default => 'badge-draft' } }}">
                        {{ $pkpt->status_label }}
                    </span>
                </div>
                
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Tahun</span>
                        <span class="font-medium text-charcoal">{{ $pkpt->period_year }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Dibuat Oleh</span>
                        <span class="font-medium text-charcoal">{{ $pkpt->creator->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-charcoal-muted">Tanggal Dibuat</span>
                        <span class="font-medium text-charcoal">{{ $pkpt->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-2 border-t border-surface-200">
                @if($fileCount === 1)
                <a href="{{ route('pkpt.view-file', $pkpt) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Berkas (1 Dokumen)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="{{ route('pkpt.download', $pkpt) }}" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-80 shadow-sm transition-all" title="Unduh Berkas">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
                @elseif($fileCount > 1)
                <a href="{{ route('pkpt.show', $pkpt) }}" class="btn-icon relative bg-primary-600 text-white hover:bg-primary-500 hover:opacity-90 shadow-sm transition-all" title="Lihat Detail ({{ $fileCount }} Berkas)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span class="absolute -top-1 -right-1 px-1 min-w-[15px] h-[15px] text-[9.5px] font-bold bg-amber-400 text-charcoal rounded-full flex items-center justify-center border border-white leading-none shadow-2xs">{{ $fileCount }}</span>
                </a>
                <a href="{{ route('pkpt.download', $pkpt) }}" class="btn-icon relative bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all" title="Unduh Semua ({{ $fileCount }} Berkas .ZIP)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="absolute -top-1 -right-1 px-1 min-w-[15px] h-[15px] text-[9.5px] font-bold bg-amber-400 text-charcoal rounded-full flex items-center justify-center border border-white leading-none shadow-2xs">ZIP</span>
                </a>
                @else
                <a href="{{ route('pkpt.show', $pkpt) }}" class="btn-icon bg-surface-200 text-charcoal-muted hover:bg-surface-300 shadow-sm transition-all" title="Lihat Detail (Belum ada berkas)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                @endif
                
                @can('pkpt.edit')
                <a href="{{ route('pkpt.edit', $pkpt) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                @endcan
                
                @can('pkpt.delete')
                <button type="button" x-data @click="$dispatch('open-modal', 'delete-pkpt-{{ $pkpt->id }}')" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                @endcan
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="empty-state-title">Belum ada data PKPT</h3>
            <p class="empty-state-description">Data PKPT yang ditambahkan akan muncul di sini.</p>
        </div>
        @endforelse
    </div>
    
    @if($pkpts->hasPages())
    <div class="pagination-wrapper">
        {{ $pkpts->links() }}
    </div>
    @endif
</div>

{{-- Delete Modals --}}
@foreach($pkpts as $pkpt)
    @can('pkpt.delete')
    <div x-data="{ show: false }" 
         x-show="show"
         @open-modal.window="if ($event.detail === 'delete-pkpt-{{ $pkpt->id }}') show = true"
         @close-modal.window="show = false"
         @keydown.escape.window="show = false"
         class="modal-overlay"
         style="display: none;">
        
        <div class="modal-box max-w-md" @click.stop x-transition>
            <div class="modal-header">
                <h3 class="card-title text-red-600">Konfirmasi Hapus</h3>
                <button @click="show = false" class="text-charcoal-muted hover:text-charcoal"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-charcoal">Apakah Anda yakin ingin menghapus PKPT <strong>{{ $pkpt->title }}</strong>? Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button @click="show = false" class="btn-secondary">Batal</button>
                <form action="{{ route('pkpt.destroy', $pkpt) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endforeach

@endsection
