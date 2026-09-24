@extends('layouts.app')

@php
    $pageTitle = 'Layanan Advisory';
    $subtitle = 'Berita Acara Konsultasi & Laporan Konsultasi';
    $cardTitle = 'Daftar Dokumen Advisory';
    
    if (isset($documentType)) {
        $cardTitle = $documentType->name;
    }
@endphp

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
<div class="card" x-data="{ showModal: false, selectedDoc: '' }">
    <div class="card-header flex flex-col items-center text-center md:text-left md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="card-title">Daftar {{ $cardTitle }}</h2>
            <p class="text-xs text-charcoal-muted mt-1">{{ $subtitle }}</p>
        </div>
        @if($canCreate)
        <div>
            <a href="{{ route('advisory.create', isset($typeSlug) ? ['type' => $typeSlug] : []) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Dokumen
            </a>
        </div>
        @endif
    </div>
        
        <div class="p-4 border-b border-surface-200 bg-surface-50">
            <form action="{{ route('advisory.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="type" value="{{ $typeSlug ?? '' }}">
                <div class="relative flex-1 max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10" placeholder="Cari dokumen..." x-data x-init="if(sessionStorage.getItem('search_focus') === 'true') { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); sessionStorage.removeItem('search_focus'); }" @input.debounce.500ms="sessionStorage.setItem('search_focus', 'true'); $event.target.form.submit()">
                </div>
                <button type="submit" class="btn-secondary">Cari</button>
                @if(request()->has('search') && request('search') != '')
                    <a href="{{ route('advisory.index', ['type' => $typeSlug]) }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
                @endif
            </form>
        </div>
        
        <div class="table-wrapper">
            <div class="hidden md:block overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">No</th>
                            @if(isset($typeSlug) && $typeSlug === 'bak')
                                <th>No / Judul BAK</th>
                                <th>Tanggal BAK</th>
                            @elseif(isset($typeSlug) && $typeSlug === 'lap-konsultasi')
                                <th>No / Judul Laporan</th>
                                <th>Tanggal Laporan</th>
                            @else
                                <th>Judul Dokumen</th>
                                <th>Tanggal Buat</th>
                            @endif
                            <th>Disusun Oleh</th>
                            <th>Disahkan Oleh</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $index => $doc)
                        <tr>
                            <td class="text-center text-charcoal-muted">{{ $documents->firstItem() + $index }}</td>
                            <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                            <td class="text-charcoal-muted text-xs">{{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}</td>
                            <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                            <td class="text-charcoal-muted">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</td>
                            <td><span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('advisory.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    
                                    @if($doc->canBeEditedBy(Auth::user()))
                                    <a href="{{ route('advisory.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @endif
                                    
                                    @if($doc->canBeDeletedBy(Auth::user()))
                                    <button type="button" x-data @click="$dispatch('open-modal', 'delete-doc-{{ $doc->id }}')" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
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
                                    <h3 class="empty-state-title">Belum ada dokumen</h3>
                                    <p class="empty-state-description">Data kosong untuk {{ $cardTitle }}.</p>
                                    @if($canCreate)
                                    <a href="{{ route('advisory.create', isset($typeSlug) ? ['type' => $typeSlug] : []) }}" class="btn-primary mt-2">Buat Dokumen Baru</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden space-y-4 p-4">
                @forelse($documents as $doc)
                <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-4 mb-3">
                            <h3 class="font-semibold text-charcoal text-base leading-snug">
                                {{ $doc->title ?: 'Dokumen Tanpa Judul' }}
                            </h3>
                            <span class="flex-shrink-0 badge {{ $doc->status_color }}">{{ $doc->status_label }}</span>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                                <span class="text-charcoal-muted">Tanggal Buat</span>
                                <span class="font-medium text-charcoal">{{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                                <span class="text-charcoal-muted">Disusun Oleh</span>
                                <span class="font-medium text-charcoal">{{ $doc->creator->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-charcoal-muted">Disahkan Oleh</span>
                                <span class="font-medium text-charcoal">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                        <a href="{{ route('advisory.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        
                        @if($doc->canBeEditedBy(Auth::user()))
                        <a href="{{ route('advisory.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endif
                        
                        @if($doc->canBeDeletedBy(Auth::user()))
                        <button type="button" x-data @click="$dispatch('open-modal', 'delete-doc-{{ $doc->id }}')" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state border border-surface-200 rounded-xl m-4">
                    <div class="empty-state-icon">
                        <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="empty-state-title">Belum ada dokumen</h3>
                    <p class="empty-state-description">Data kosong untuk {{ $cardTitle }}.</p>
                </div>
                @endforelse
            </div>

            @if($documents->hasPages())
            <div class="pagination-wrapper p-4 border-t border-surface-200">
                <p class="text-xs text-charcoal-muted mb-3">Menampilkan {{ $documents->firstItem() }}–{{ $documents->lastItem() }} dari {{ $documents->total() }}</p>
                {{ $documents->links() }}
            </div>
            @endif
        </div>
    </div>

{{-- Delete Modals --}}
@foreach($documents as $doc)
    @if($doc->canBeDeletedBy(Auth::user()))
    <div x-data="{ show: false }" 
         x-show="show"
         @open-modal.window="if ($event.detail === 'delete-doc-{{ $doc->id }}') show = true"
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
                <p class="text-sm text-charcoal">Apakah Anda yakin ingin menghapus dokumen <strong>{{ $doc->title }}</strong>? Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button @click="show = false" class="btn-secondary">Batal</button>
                <form action="{{ route('advisory.destroy', $doc) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
