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
                <h2 class="card-title">{{ $pkpt->title }}</h2>
                <p class="text-sm text-charcoal-muted mt-1 mb-2">
                    Tahun Periode: <strong>{{ $pkpt->period_year }}</strong> &bull; 
                    Dibuat oleh <strong>{{ $pkpt->creator->name ?? '-' }}</strong> pada {{ $pkpt->created_at->format('d F Y') }}
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <span class="badge {{ match($pkpt->status) { 'draft' => 'badge-draft', 'pending_kspi', 'pending_direktur' => 'badge-pending', 'approved' => 'badge-approved', 'rejected' => 'badge-rejected', default => 'badge-draft' } }}">
                    {{ $pkpt->status_label }}
                </span>
                
                <div class="flex gap-2">
                    @can('pkpt.edit')
                    <a href="{{ route('pkpt.edit', $pkpt) }}" class="btn-primary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @endcan
                    <a href="{{ route('pkpt.index') }}" class="btn-secondary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Dokumen PKPT Card --}}
    <div class="card">
        <div class="card-header bg-primary-50 border-b border-primary-100">
            <h3 class="card-title text-primary-800">Dokumen PKPT Utama</h3>
            <p class="text-sm text-primary-600 mt-1">Dokumen resmi pengesahan PKPT</p>
        </div>
        <div class="card-body">
            @if($pkpt->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($pkpt->file_path))
                <div class="p-4 border border-green-200 bg-green-50 rounded-lg shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-semibold text-charcoal">Dokumen Utama</h4>
                            <p class="text-xs text-charcoal-muted">File PDF PKPT yang disahkan</p>
                        </div>
                        <span class="badge bg-green-100 text-green-700 border-green-200">Terunggah</span>
                    </div>
                    
                    <div class="p-3 bg-white border border-green-100 rounded-md flex items-center justify-between">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-sm font-medium text-charcoal truncate" title="{{ $pkpt->file_name }}">{{ $pkpt->file_name }}</span>
                        </div>
                        <a href="{{ route('pkpt.view-file', $pkpt) }}" target="_blank" class="text-primary-600 hover:text-primary-800 text-sm font-medium ml-4 flex-shrink-0 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat Dokumen
                        </a>
                    </div>
                </div>
            @else
                <div class="p-4 border border-surface-200 bg-white rounded-lg shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-semibold text-charcoal">Dokumen Utama</h4>
                            <p class="text-xs text-charcoal-muted">File PDF PKPT yang disahkan</p>
                        </div>
                        <span class="badge bg-gray-100 text-gray-600 border-gray-200">Belum ada</span>
                    </div>
                    <div class="p-8 text-center bg-surface-50 rounded-md border border-dashed border-surface-300">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-surface-200 text-charcoal-muted mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-sm text-charcoal-muted">File PDF untuk PKPT ini belum diunggah atau tidak tersedia.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
