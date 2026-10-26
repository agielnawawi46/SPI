@extends('layouts.app')

@php
    $pageTitle = 'Layanan Assurance';
    $subtitle = 'Kelola penugasan audit terjadwal & tidak terjadwal';
    
    if ($auditType === 'terjadwal') {
        $pageTitle = 'Audit Terjadwal';
        $subtitle = 'Penugasan berdasarkan PKPT tahunan';
    } elseif ($auditType === 'tidak_terjadwal') {
        $pageTitle = 'Audit Tidak Terjadwal';
        $subtitle = 'Penugasan khusus di luar PKPT';
    }

    if ($documentType) {
        $pageTitle = $documentType->name;
    }
@endphp

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
<div class="card" x-data="{ showModal: false, selectedDoc: '' }">
    <div class="card-header flex flex-col items-start text-left gap-3">
        <div>
            <h2 class="card-title">Daftar {{ $pageTitle }}</h2>
            <p class="text-xs text-charcoal-muted mt-1">{{ $subtitle }}</p>
        </div>
        @php
            $user = auth()->user();
            $canCreate = false;
            if ($user->hasRole('admin')) {
                $canCreate = true;
            } elseif (in_array($typeSlug, ['slide-entry', 'kertas-kerja-audit', 'lha', 'surat-tugas']) && $user->hasRole('auditor')) {
                $canCreate = true;
            } elseif (!in_array($typeSlug, ['slide-entry', 'kertas-kerja-audit', 'lha', 'surat-tugas']) && $user->hasRole('kspi')) {
                $canCreate = true;
            }
        @endphp
        @if($canCreate)
        <div>
            <a href="{{ route('assurance.create', ['audit_type' => $auditType ?? 'terjadwal', 'type' => $typeSlug]) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Dokumen
            </a>
        </div>
        @endif
    </div>

    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('assurance.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="audit_type" value="{{ $auditType ?? '' }}">
            <input type="hidden" name="type" value="{{ $typeSlug ?? '' }}">
            <x-live-search module="assurance" placeholder="Cari judul dokumen..." />
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('assurance.index', ['audit_type' => $auditType, 'type' => $typeSlug]) }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block table-wrapper">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="w-16 text-center">No</th>
                    @if($typeSlug === 'program-kerja')
                        <th>No PKA</th>
                        <th>Tgl PKA</th>
                        <th>Disusun Oleh</th>
                        <th>Disahkan Oleh</th>
                    @elseif($typeSlug === 'slide-entry')
                        <th>Judul Dokumen</th>
                        <th>Tgl Entry</th>
                        <th>Disusun Oleh</th>
                        <th>Disahkan Oleh</th>
                    @elseif($typeSlug === 'lha')
                        <th>No LHA</th>
                        <th>Disusun Oleh</th>
                        <th>Disahkan Oleh</th>
                    @elseif(in_array($typeSlug, ['surat-tugas', 'kertas-kerja-audit']))
                        <th>No Dokumen</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Dibuat</th>
                    @else
                        <th>Judul Dokumen</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                    @endif
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $index => $doc)
                <tr>
                    <td class="text-center text-charcoal-muted">{{ $documents->firstItem() + $index }}</td>
                    
                    @if($typeSlug === 'program-kerja')
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->tgl_pka ? $doc->tgl_pka->format('d/m/Y') : '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</td>
                    @elseif($typeSlug === 'slide-entry')
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->tgl_entry ? $doc->tgl_entry->format('d/m/Y') : '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</td>
                    @elseif($typeSlug === 'lha')
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</td>
                    @elseif(in_array($typeSlug, ['surat-tugas', 'kertas-kerja-audit']))
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">
                            {{ $doc->title ?? '-' }}
                        </td>
                        <td><span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span></td>
                        <td class="text-charcoal-muted">{{ $doc->creator?->name ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->created_at->format('d/m/Y') }}</td>
                    @else
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->documentType?->name ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs capitalize">{{ str_replace('_', ' ', $doc->audit_type) }}</td>
                        <td><span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span></td>
                        <td class="text-charcoal-muted">{{ $doc->creator?->name ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs whitespace-nowrap">{{ $doc->created_at->format('d/m/Y') }}</td>
                    @endif
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('assurance.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            
                            @if($doc->canBeEditedBy(Auth::user()))
                            <a href="{{ route('assurance.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
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
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h3 class="empty-state-title">Belum ada dokumen</h3>
                            <p class="empty-state-description">Data kosong untuk {{ $pageTitle }}.</p>
                            @if($canCreate)
                            <a href="{{ route('assurance.create', ['audit_type' => $auditType ?? 'terjadwal', 'type' => $typeSlug]) }}" class="btn-primary mt-2">Buat Dokumen Baru</a>
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
                    <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                        <a href="{{ route('assurance.show', $doc) }}" class="hover:text-primary-600 transition-colors">
                            @if($typeSlug === 'program-kerja')
                                {{ $doc->title ?: 'PKA Tanpa Nomor' }}
                            @elseif($typeSlug === 'slide-entry')
                                {{ $doc->title ?: 'Auditee Tidak Diketahui' }}
                            @elseif($typeSlug === 'kertas-kerja-audit')
                                {{ $doc->title ?: 'KKA Tanpa Nomor' }}
                            @elseif($typeSlug === 'lha')
                                {{ $doc->title ?: 'LHA Tanpa Nomor' }}
                            @elseif($typeSlug === 'surat-tugas')
                                {{ $doc->title ?: 'STG Tanpa Nomor' }}
                            @else
                                {{ $doc->title }}
                            @endif
                        </a>
                    </h3>
                    @if(!in_array($typeSlug, ['program-kerja', 'slide-entry', 'kertas-kerja-audit', 'lha']))
                        <span class="flex-shrink-0 badge {{ $doc->status_color }}">{{ $doc->status_label }}</span>
                    @endif
                </div>
                
                <div class="flex flex-col gap-2">
                    @if($typeSlug === 'program-kerja')
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Tgl PKA</span>
                            <span class="font-medium text-charcoal">{{ $doc->tgl_pka ? $doc->tgl_pka->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Disusun Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->creator->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Disahkan Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</span>
                        </div>
                    @elseif($typeSlug === 'slide-entry')
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Tgl Entry</span>
                            <span class="font-medium text-charcoal">{{ $doc->tgl_entry ? $doc->tgl_entry->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Disusun Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->creator->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Disahkan Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</span>
                        </div>
                    @elseif($typeSlug === 'lha')
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Disusun Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->creator->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Disahkan Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</span>
                        </div>
                    @elseif(in_array($typeSlug, ['surat-tugas', 'kertas-kerja-audit']))
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Tgl Dokumen</span>
                            <span class="font-medium text-charcoal">{{ $doc->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Dibuat Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->creator?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Status</span>
                            <span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span>
                        </div>
                    @else
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Jenis</span>
                            <span class="font-medium text-charcoal">{{ $doc->documentType?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Kategori</span>
                            <span class="font-medium text-charcoal capitalize">{{ str_replace('_', ' ', $doc->audit_type) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Dibuat Oleh</span>
                            <span class="font-medium text-charcoal">{{ $doc->creator?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Tanggal</span>
                            <span class="font-medium text-charcoal">{{ $doc->created_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                <a href="{{ route('assurance.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                
                @if($doc->canBeEditedBy(Auth::user()))
                <a href="{{ route('assurance.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
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
            <p class="empty-state-description">Data kosong untuk {{ $pageTitle }}.</p>
        </div>
        @endforelse
    </div>

    @if($documents->hasPages())
    <div class="pagination-wrapper p-4 border-t border-surface-200">
        {{ $documents->links() }}
    </div>
    @endif
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
                <form action="{{ route('assurance.destroy', $doc) }}" method="POST" class="inline-block">
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
