@extends('layouts.app')

@php
    $pageTitle = 'Pelaksanaan Quality Assurance (QA)';
    $subtitle  = 'Kelola dokumen Surat Tugas, Program Kerja, Kertas Kerja & Laporan QA';
    if ($documentType) { $pageTitle = $documentType->name; }
@endphp

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
<div class="card" x-data="{ showModal: false, selectedDoc: '' }">
    <div class="card-header flex flex-col items-center text-center md:text-left md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="card-title">Daftar {{ $pageTitle }}</h2>
            <p class="text-xs text-charcoal-muted mt-1">{{ $subtitle }}</p>
        </div>
        @php $user = auth()->user(); $canCreate = $user->hasRole(['admin', 'auditor', 'kspi']); @endphp
        @if($canCreate)
        <div>
            <a href="{{ route('other-activities.qa.create', isset($typeSlug) ? ['type' => $typeSlug] : []) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Dokumen
            </a>
        </div>
        @endif
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('other-activities.qa.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="type" value="{{ $typeSlug ?? '' }}">
            <x-live-search module="qa" placeholder="Cari judul dokumen..." />
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('other-activities.qa.index', ['type' => $typeSlug]) }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block table-wrapper">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="w-16 text-center">No</th>
                    @if($typeSlug === 'pk-qa')
                        <th>No PKA</th>
                        <th>Tgl PKA</th>
                        <th>Disusun Oleh</th>
                        <th>Disahkan Oleh</th>
                    @elseif($typeSlug === 'lap-qa')
                        <th>No LHA</th>
                        <th>Disusun Oleh</th>
                        <th>Disahkan Oleh</th>
                    @elseif(in_array($typeSlug, ['st-qa', 'kk-qa']))
                        <th>No Dokumen</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal Dibuat</th>
                    @else
                        <th>Judul Dokumen</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                    @endif
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td class="text-center text-charcoal-muted text-sm">{{ $documents->firstItem() + $loop->index }}</td>
                    
                    @if($typeSlug === 'pk-qa')
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->tgl_pka ? $doc->tgl_pka->format('d/m/Y') : '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</td>
                    @elseif($typeSlug === 'lap-qa')
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->creator->name ?? '-' }}</td>
                        <td class="text-charcoal-muted">{{ $doc->isApproved() ? ($doc->approvals->where('action', 'approve')->last()->approver->name ?? '-') : '-' }}</td>
                    @elseif(in_array($typeSlug, ['st-qa', 'kk-qa']))
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title ?? '-' }}</td>
                        <td><span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span></td>
                        <td class="text-charcoal-muted">{{ $doc->creator?->name ?? '-' }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->created_at->format('d/m/Y') }}</td>
                    @else
                        <td class="font-medium text-charcoal max-w-xs truncate" title="{{ $doc->title }}">{{ $doc->title }}</td>
                        <td class="text-charcoal-muted text-xs">{{ $doc->documentType?->name ?? '-' }}</td>
                        <td><span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span></td>
                        <td class="text-charcoal-muted">{{ $doc->creator?->name ?? '-' }}</td>
                        <td class="text-xs text-charcoal-muted whitespace-nowrap">{{ $doc->created_at->format('d M Y') }}</td>
                    @endif

                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('other-activities.qa.show', $doc) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            
                            @if($doc->canBeEditedBy(Auth::user()))
                            <a href="{{ route('other-activities.qa.edit', $doc) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                            
                            @if($doc->canBeDeletedBy(Auth::user()))
                            <form action="{{ route('other-activities.qa.destroy', $doc) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen QA ini?');">
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
                    <td colspan="7" class="text-center py-12">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-7 h-7 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <p class="empty-state-title">Belum ada dokumen</p>
                            <p class="empty-state-description">Buat dokumen baru untuk memulai proses Quality Assurance.</p>
                            @if($canCreate)
                            <a href="{{ route('other-activities.qa.create', isset($typeSlug) ? ['type' => $typeSlug] : []) }}" class="btn-primary btn-sm mt-2">+ Buat Dokumen</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-4 p-4">
        @forelse($documents as $doc)
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-start gap-4 mb-3">
                    <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                        <a href="{{ route('other-activities.qa.show', $doc) }}" class="hover:text-primary-600 transition-colors">
                            {{ $doc->title }}
                        </a>
                    </h3>
                    <span class="flex-shrink-0 badge {{ $doc->status_color }}">{{ $doc->status_label }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Jenis</span>
                        <span class="font-medium text-charcoal">{{ $doc->documentType?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Dibuat Oleh</span>
                        <span class="font-medium text-charcoal">{{ $doc->creator?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-charcoal-muted">Tanggal</span>
                        <span class="font-medium text-charcoal">{{ $doc->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-surface-50 px-4 py-3 flex items-center justify-end border-t border-surface-200">
                <a href="{{ route('other-activities.qa.show', $doc) }}" class="btn-outline btn-sm bg-white">Lihat Detail</a>
            </div>
        </div>
        @empty
        <div class="empty-state p-8">
            <p class="empty-state-title">Belum ada dokumen QA</p>
        </div>
        @endforelse
    </div>

    @if($documents->hasPages())
    <div class="pagination-wrapper p-4 border-t border-surface-200">
        {{ $documents->links() }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success mx-4 mb-4">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
</div>
@endsection
