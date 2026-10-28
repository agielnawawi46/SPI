@extends('layouts.app')

@section('title', 'Detail PKPT')
@section('page_title', 'Detail PKPT')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-link">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $pkpt->title }}</span>
@endsection

@section('content')
<div class="space-y-6">
    
    {{-- PKPT Header Info --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $pkpt->title }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    <span>Tahun Periode: <strong class="text-charcoal">{{ $pkpt->period_year }}</strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Dibuat oleh <strong class="text-charcoal">{{ $pkpt->creator->name ?? '-' }}</strong> pada {{ $pkpt->created_at->format('d F Y') }}</span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <span class="badge {{ match($pkpt->status) { 'draft' => 'badge-draft', 'pending_kspi', 'pending_direktur' => 'badge-pending', 'approved' => 'badge-approved', 'rejected' => 'badge-rejected', default => 'badge-draft' } }}">
                    {{ $pkpt->status_label }}
                </span>
                
                <div class="flex items-center gap-2">
                    @can('pkpt.edit')
                    <a href="{{ route('pkpt.edit', $pkpt) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @endcan
                    @can('pkpt.delete')
                    <button type="button" x-data @click="$dispatch('open-modal', 'delete-pkpt-{{ $pkpt->id }}')" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                    @endcan
                    <a href="{{ route('pkpt.index') }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Dokumen PKPT Utama --}}
    <div class="card shadow-sm">
        <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dokumen Resmi PKPT Utama
                @if(isset($attachments) && $attachments->count() > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-800">
                        {{ $attachments->count() }} Berkas
                    </span>
                @endif
            </h3>
            <div class="flex flex-wrap items-center gap-2.5">
                @if(isset($attachments) && $attachments->count() > 1)
                <a href="{{ route('pkpt.download', $pkpt) }}" class="btn-secondary text-sm py-2.5 px-4 flex items-center gap-2 shadow-sm text-emerald-700 hover:text-emerald-800 border-emerald-300 hover:bg-emerald-50 rounded-lg transition-all" title="Unduh semua berkas PKPT sekaligus dalam arsip .ZIP">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Semua (.ZIP)</span>
                </a>
                @endif
                @can('pkpt.edit')
                <a href="{{ route('pkpt.edit', $pkpt) }}" class="btn-secondary text-sm py-2.5 px-4 flex items-center gap-2 shadow-sm rounded-lg transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah / Kelola Berkas</span>
                </a>
                @endcan
                <a href="{{ route('pkpt.kertas-kerja', $pkpt) }}" class="btn-primary text-sm py-2.5 px-4 flex items-center gap-2 shadow-sm rounded-lg transition-all">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Kelola Kertas Kerja (6 Berkas)</span>
                </a>
            </div>
        </div>
        <div class="card-body p-5">
            @if(isset($attachments) && $attachments->count() > 0)
                <div class="space-y-3">
                    @foreach($attachments as $att)
                        @php
                            $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION));
                            $iconStyle = match($ext) {
                                'pdf' => 'text-red-500 bg-red-50 border-red-200',
                                'doc', 'docx' => 'text-blue-500 bg-blue-50 border-blue-200',
                                'xls', 'xlsx' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                                default => 'text-charcoal bg-surface-100 border-surface-200'
                            };
                            $badgeStyle = match($ext) {
                                'pdf' => 'bg-red-50 text-red-700 border-red-200',
                                'doc', 'docx' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'xls', 'xlsx' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                default => 'bg-surface-100 text-charcoal border-surface-200'
                            };
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-emerald-400 rounded-xl bg-emerald-50/20 hover:bg-emerald-50/40 hover:border-emerald-500 transition-all shadow-2xs">
                            <div class="flex items-center gap-3.5 overflow-hidden">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border {{ $iconStyle }}">
                                    @if($ext === 'pdf')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    @elseif(in_array($ext, ['xls', 'xlsx']))
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @endif
                                </div>
                                <div class="truncate">
                                    <h4 class="text-sm font-bold text-charcoal truncate" title="{{ $att->file_name }}">{{ $att->file_name }}</h4>
                                    <p class="text-xs text-charcoal-muted mt-0.5 flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                            <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Terunggah
                                        </span>
                                        <span class="font-medium uppercase px-1.5 py-0.2 rounded border {{ $badgeStyle }}">{{ $ext }}</span>
                                        @if($att->file_size)
                                            <span>&bull; {{ $att->file_size_formatted }}</span>
                                        @endif
                                        <span>&bull; {{ $att->created_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-auto">
                                <a href="{{ route('pkpt.view-file', [$pkpt, $att->id]) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-600 shadow-sm transition-all" title="Lihat Dokumen">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('pkpt.download', [$pkpt, $att->id]) }}" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-all" title="Unduh Dokumen">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($pkpt->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($pkpt->file_path))
                @php
                    $fileSizeFormatted = $pkpt->file_size ? number_format($pkpt->file_size / 1024, 1) . ' KB' : null;
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-emerald-400 rounded-xl bg-emerald-50/20 hover:bg-emerald-50/40 hover:border-emerald-500 transition-all shadow-2xs">
                    <div class="flex items-center gap-3.5 overflow-hidden">
                        <div class="p-3 bg-red-50 text-red-600 rounded-xl flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="truncate">
                            <h4 class="text-sm font-bold text-charcoal truncate" title="{{ $pkpt->file_name }}">{{ $pkpt->file_name }}</h4>
                            <p class="text-xs text-charcoal-muted mt-1">
                                {{ $fileSizeFormatted ? $fileSizeFormatted . ' • ' : '' }}Dokumen PDF Resmi yang Disahkan
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-auto">
                        <a href="{{ route('pkpt.view-file', $pkpt) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-600 shadow-sm transition-all" title="Lihat Dokumen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('pkpt.download', $pkpt) }}" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-all" title="Unduh Dokumen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>
            @else
                <div class="py-8 text-center bg-surface-50 rounded-xl border border-dashed border-surface-200">
                    <svg class="w-10 h-10 text-charcoal-muted/40 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm font-medium text-charcoal">Berkas dokumen PKPT belum diunggah</p>
                    <p class="text-xs text-charcoal-muted mt-1">Gunakan tombol edit di atas untuk mengunggah dokumen resmi PKPT.</p>
                </div>
            @endif
        </div>
    </div>

</div>

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
            <h3 class="card-title text-red-600">Konfirmasi Hapus PKPT</h3>
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

@endsection

