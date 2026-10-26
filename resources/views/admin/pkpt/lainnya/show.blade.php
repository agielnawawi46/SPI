@extends('layouts.app')

@section('title', 'Detail Objek Audit (Lainnya)')
@section('page_title', 'Detail Objek Audit (Lainnya)')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-link">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.lainnya.index') }}" class="breadcrumb-link">Lainnya</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $lainnya->audit_object }}</span>
@endsection

@section('content')
<div class="space-y-6">
    
    {{-- PKPT Lainnya Header Info --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title">{{ $lainnya->audit_object }}</h2>
                <p class="text-sm text-charcoal-muted mt-2 space-y-1">
                    <span class="block">Terkait PKPT: <strong>{{ $lainnya->pkpt->title ?? '-' }} ({{ $lainnya->pkpt->period_year ?? '-' }})</strong></span>
                    <span class="block">Auditee: <strong>
                        @if($lainnya->auditeeDepartment)
                            {{ $lainnya->auditeeDepartment->code }} - {{ $lainnya->auditeeDepartment->name }}
                        @elseif($lainnya->custom_auditee_name)
                            {{ $lainnya->custom_auditee_name }} (Lainnya)
                        @else
                            -
                        @endif
                    </strong></span>
                    <span class="block">Jadwal: <strong>
                        @if($lainnya->planned_start && $lainnya->planned_end)
                            {{ $lainnya->planned_start->format('d M Y') }} s/d {{ $lainnya->planned_end->format('d M Y') }}
                        @else
                            Belum dijadwalkan
                        @endif
                    </strong></span>
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <div class="flex gap-2">
                    @can('pkpt.edit')
                    <a href="{{ route('pkpt.lainnya.edit', $lainnya) }}" class="btn-primary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @endcan
                    <a href="{{ route('pkpt.lainnya.index') }}" class="btn-secondary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($lainnya->attachment_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lainnya->attachment_path))
    {{-- Dokumen Card (jika ada file tersimpan sebelumnya) --}}
    <div class="card">
        <div class="card-header bg-primary-50 border-b border-primary-100">
            <h3 class="card-title text-primary-800">Lampiran Dokumen</h3>
            <p class="text-sm text-primary-600 mt-1">Dokumen rincian objek audit</p>
        </div>
        <div class="card-body">
            <div class="p-4 border border-green-200 bg-green-50 rounded-lg shadow-sm">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-semibold text-charcoal">Dokumen Lampiran</h4>
                        <p class="text-xs text-charcoal-muted">File PDF/Office terkait auditee & jadwal ini</p>
                    </div>
                    <span class="badge bg-green-100 text-green-700 border-green-200">Terunggah</span>
                </div>
                
                <div class="p-3 bg-white border border-green-100 rounded-md flex items-center justify-between">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="text-sm font-medium text-charcoal truncate" title="{{ basename($lainnya->attachment_path) }}">{{ basename($lainnya->attachment_path) }}</span>
                    </div>
                    <a href="{{ Storage::url($lainnya->attachment_path) }}" target="_blank" class="text-primary-600 hover:text-primary-800 text-sm font-medium ml-4 flex-shrink-0 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat Dokumen
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
