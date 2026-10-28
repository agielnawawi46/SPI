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
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $lainnya->audit_object }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    <span>Terkait PKPT: <strong class="text-charcoal">{{ $lainnya->pkpt->title ?? '-' }} ({{ $lainnya->pkpt->period_year ?? '-' }})</strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Auditee: <strong class="text-charcoal">
                        @if($lainnya->auditeeDepartment)
                            {{ $lainnya->auditeeDepartment->code }} - {{ $lainnya->auditeeDepartment->name }}
                        @elseif($lainnya->custom_auditee_name)
                            {{ $lainnya->custom_auditee_name }} (Lainnya)
                        @else
                            -
                        @endif
                    </strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Jadwal: <strong class="text-charcoal">
                        @if($lainnya->planned_start && $lainnya->planned_end)
                            {{ $lainnya->planned_start->format('d M Y') }} s/d {{ $lainnya->planned_end->format('d M Y') }}
                        @else
                            Belum dijadwalkan
                        @endif
                    </strong></span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <div class="flex gap-2">
                    @can('pkpt.edit')
                    <a href="{{ route('pkpt.lainnya.edit', $lainnya) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @endcan
                    <a href="{{ route('pkpt.lainnya.index') }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($lainnya->attachment_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lainnya->attachment_path))
    @php
        $fileName = basename($lainnya->attachment_path);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $iconColor = match($ext) {
            'pdf' => 'bg-red-50 text-red-600',
            'doc', 'docx' => 'bg-blue-50 text-blue-600',
            'xls', 'xlsx' => 'bg-emerald-50 text-emerald-600',
            default => 'bg-surface-100 text-primary-600'
        };
    @endphp
    <div class="card shadow-sm">
        <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Berkas Lampiran Dokumen
            </h3>
            <span class="badge badge-approved">Terunggah</span>
        </div>
        <div class="card-body p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-emerald-400 rounded-xl bg-emerald-50/20 hover:bg-emerald-50/40 hover:border-emerald-500 transition-all shadow-2xs">
                <div class="flex items-center gap-3.5 overflow-hidden">
                    <div class="p-3 {{ $iconColor }} rounded-xl flex-shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-charcoal truncate" title="{{ $fileName }}">{{ $fileName }}</h4>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Terunggah
                            </span>
                        </div>
                        <p class="text-xs text-charcoal-muted mt-1">
                            Dokumen lampiran terkait auditee dan jadwal objek audit ini
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-auto">
                    <a href="{{ Storage::url($lainnya->attachment_path) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-600 shadow-sm transition-all" title="Lihat Dokumen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <a href="{{ Storage::url($lainnya->attachment_path) }}" download class="btn-icon bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-all" title="Unduh Dokumen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
