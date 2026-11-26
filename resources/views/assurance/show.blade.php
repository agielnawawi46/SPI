@extends('layouts.app')
@section('title', $document->title)
@section('page_title', 'Detail Dokumen')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('assurance.index', ['audit_type' => $document->audit_type, 'type' => $document->documentType?->slug]) }}" class="breadcrumb-link">Assurance</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $document->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    @php
        $user = Auth::user();
        $hasAttachment = $document->attachments->count() > 0;
        $isSuratTugasDigital = (!$hasAttachment && $document->documentType?->slug === 'surat-tugas');
    @endphp

    {{-- Assurance Header Info --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $document->title }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    <span>Jenis: <strong class="text-charcoal">{{ $document->documentType?->name ?? '-' }}</strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Kategori: <strong class="capitalize text-charcoal">{{ str_replace('_', ' ', $document->audit_type) }}</strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Dibuat oleh <strong class="text-charcoal">{{ $document->creator->name ?? '-' }}</strong> pada {{ $document->created_at->format('d F Y') }}</span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
                </div>
                
                {{-- Page-Level Navigation & Actions --}}
                <div class="flex items-center gap-2">
                    @if($isSuratTugasDigital)
                        <a href="{{ route('assurance.pdf', $document) }}" class="btn-primary text-sm bg-red-600 hover:bg-red-700 border-red-600 hover:border-red-700 text-white flex items-center shadow-sm" target="_blank">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export PDF
                        </a>
                    @endif
                    @if($document->canBeEditedBy($user))
                        <a href="{{ route('assurance.edit', $document) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    @endif
                    @if($document->canBeDeletedBy($user))
                        <form action="{{ route('assurance.destroy', $document) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini? Data yang dihapus tidak dapat dikembalikan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex items-center shadow-sm cursor-pointer" title="Hapus Dokumen">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('assurance.index', ['audit_type' => $document->audit_type, 'type' => $document->documentType?->slug]) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- KONDISI 1: JIKA SURAT TUGAS DIGITAL SISTEM (BELUM ADA BERKAS FISIK DIUNGGAH) --}}
    @if($isSuratTugasDigital)

        {{-- Approval Stepper & QR Code Container --}}
        <div class="grid grid-cols-1 {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:grid-cols-3' : '' }} gap-4 mb-4">
            
            {{-- Approval History --}}
            @if($document->approvals->isNotEmpty())
            <div class="card {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:col-span-2' : '' }}">
                <div class="card-body py-3 px-4 overflow-x-auto">
                    <h3 class="text-sm font-semibold text-charcoal mb-3">Riwayat Persetujuan Digital</h3>
                    <div class="flex items-start w-full min-w-[400px]">
                        @foreach($document->approvals->sortBy('created_at') as $approval)
                        <div class="relative flex-1 text-center group px-1">
                            @if(!$loop->last)
                            <div class="absolute top-3.5 left-[50%] w-full h-0.5 {{ $approval->action === 'approve' ? 'bg-emerald-400' : 'bg-surface-200' }} z-0 mt-[1px]"></div>
                            @endif

                            <div class="relative z-10 mx-auto w-7 h-7 flex items-center justify-center rounded-full border-2 border-white shadow-sm {{ 
                                $approval->action === 'approve' ? 'bg-emerald-100 text-emerald-600' : 
                                ($approval->action === 'reject' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600') 
                            }}">
                                @if($approval->action === 'approve')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($approval->action === 'reject')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>

                            <div class="mt-2">
                                <h4 class="text-[11px] font-semibold text-charcoal leading-tight">
                                    Level {{ $approval->approval_level }} &mdash; <span class="capitalize">{{ $approval->approval_role }}</span>
                                </h4>
                                <p class="text-[10px] font-bold mt-1 leading-none {{ 
                                    $approval->action === 'approve' ? 'text-emerald-600' : 
                                    ($approval->action === 'reject' ? 'text-red-600' : 'text-amber-600') 
                                }}">
                                    {{ $approval->action === 'approve' ? 'Disetujui' : ($approval->action === 'reject' ? 'Revisi' : 'Menunggu') }}
                                </p>
                                @if($approval->acted_at)
                                <p class="text-[10px] text-charcoal-muted mt-1 leading-none">{{ $approval->acted_at->format('d M Y, H:i') }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- QR Code Resmi saat Approved --}}
            @if($document->status === 'approved' && $document->qr_code_path)
            <div class="card bg-emerald-50 border border-emerald-200 lg:col-span-1 h-full shadow-sm">
                <div class="card-body p-4 md:p-5 flex flex-row justify-center items-center gap-4 h-full">
                    <div class="bg-white p-2 rounded-2xl shadow-sm flex-shrink-0">
                        <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="w-20 h-20 md:w-24 md:h-24 object-contain">
                    </div>
                    <div class="text-left">
                        <h3 class="font-bold text-emerald-800 text-sm md:text-base">Dokumen Resmi Digital</h3>
                        <p class="text-[11px] text-emerald-700 mt-1 font-medium">Tgl: {{ $document->approved_at?->format('d M Y') }}</p>
                        <a href="{{ route('document.verify', $document->uuid) }}" target="_blank" class="mt-3 text-[10px] font-bold text-emerald-700 bg-white hover:bg-emerald-50 py-1.5 px-3 rounded-full shadow-sm transition-colors border border-emerald-200 inline-flex items-center gap-1 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Verifikasi
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Multi-level Approval Actions (Submit, Review, Sign) --}}
        @php
            $pendingKspi = $document->approvals->where('approval_level', 1)->where('action', null)->first();
            $pendingDir  = $document->approvals->where('approval_level', 2)->where('action', null)->first();
        @endphp

        {{-- Auditor: Submit to KSPI --}}
        @if(in_array($document->status, ['draft','revised']) && $document->created_by === $user->id)
        <div class="card border-l-4 border-l-amber-400 mb-4 shadow-sm">
            <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-charcoal">Kirim untuk Direview</h3>
                    <p class="text-sm text-charcoal-muted mt-1">Dokumen digital ini belum diajukan. Kirim ke KSPI untuk direview dan disetujui.</p>
                </div>
                <form action="{{ route('approvals.submit', ['type' => 'document', 'id' => $document->id]) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn-primary text-sm flex items-center gap-2 cursor-pointer"
                        onclick="return confirm('Kirim dokumen ini ke KSPI untuk direview?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim ke KSPI
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- KSPI: Approve or Request Revision --}}
        @if($document->status === 'pending_kspi' && $user->hasRole('kspi') && $pendingKspi)
        <div class="card border-l-4 border-l-blue-400 mb-4 shadow-sm">
            <div class="card-body p-5">
                <h3 class="font-semibold text-charcoal mb-1">Tindakan Review KSPI</h3>
                <p class="text-sm text-charcoal-muted mb-4">Dokumen ini menunggu persetujuan Anda sebagai KSPI. Jika Anda menemukan kesalahan, Anda dapat mengubahnya sebelum menyetujui.</p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <form action="{{ route('approvals.action', $pendingKspi) }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional)"></textarea>
                        <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2 cursor-pointer"
                            onclick="return confirm('Setujui dokumen ini dan teruskan ke Direktur?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approve & Teruskan ke Direktur
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Direktur: Approve & Bubuhkan Barcode --}}
        @if($document->status === 'pending_direktur' && $user->hasRole('direktur') && $pendingDir)
        <div class="card border-l-4 border-l-purple-400 mb-4 shadow-sm">
            <div class="card-body p-5">
                <h3 class="font-semibold text-charcoal mb-1">Tindakan Persetujuan Direktur</h3>
                <p class="text-sm text-charcoal-muted mb-4">Dokumen telah disetujui KSPI dan menunggu tanda tangan Direktur. Jika disetujui, sistem akan membubuhkan barcode secara otomatis.</p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <form action="{{ route('approvals.action', $pendingDir) }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional)"></textarea>
                        <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2 cursor-pointer"
                            onclick="return confirm('Setujui dan tandatangani dokumen ini? Barcode akan dibubuhkan secara otomatis.')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Approve & Bubuhkan Barcode
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Pure Digital Document Container (Single Source of Truth) --}}
        <div class="space-y-4">
            <div class="max-w-[210mm] mx-auto">
                <h4 class="text-sm font-bold text-charcoal">Dokumen Resmi Digital Sistem</h4>
                <p class="text-xs text-charcoal-muted">Lembar formulir standar yang diterbitkan dan disahkan melalui sistem.</p>
            </div>
            @include('assurance.partials.a4-preview')
        </div>

    {{-- KONDISI 2: HASIL OUTPUT UNTUK SEMUA SUBMENU LAYANAN ASSURANCE SETELAH BERKAS DIUNGGAH (PKPT STYLE) --}}
    @else

        {{-- Status & Verifikasi Pengesahan Berkas --}}
        @if($document->isApproved())
            <div class="card bg-emerald-50/70 border border-emerald-200 shadow-sm">
                <div class="card-body p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-950">Dokumen Telah Disahkan</h3>
                            <p class="text-xs text-emerald-700 mt-0.5">
                                Berkas dokumen telah disahkan pada sistem
                                @if($document->approvals->where('action', 'approve')->isNotEmpty())
                                    oleh <strong class="text-emerald-900">{{ $document->approvals->where('action', 'approve')->last()->approver->name ?? '-' }}</strong>
                                @elseif($document->creator)
                                    oleh <strong class="text-emerald-900">{{ $document->creator->name }}</strong>
                                @endif
                                pada {{ $document->approved_at ? $document->approved_at->format('d F Y, H:i') : $document->updated_at->format('d F Y, H:i') }}.
                            </p>
                        </div>
                    </div>
                    @if($document->uuid)
                    <a href="{{ route('document.verify', $document->uuid) }}" target="_blank" class="btn-secondary text-xs py-2 px-3.5 bg-white hover:bg-emerald-50 border-emerald-300 text-emerald-800 flex items-center gap-1.5 flex-shrink-0 shadow-sm self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Verifikasi Dokumen
                    </a>
                    @endif
                </div>
            </div>
        @elseif(in_array($document->status, ['draft', 'pending_kspi', 'revised']))
            <div class="card bg-amber-50/70 border border-amber-200 shadow-sm">
                <div class="card-body p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-amber-950">Menunggu Pengesahan KSPI / Admin</h3>
                            <p class="text-xs text-amber-700 mt-0.5">
                                Berkas dokumen telah diunggah dan siap untuk diverifikasi / disahkan oleh Pejabat KSPI atau Admin.
                            </p>
                        </div>
                    </div>
                    @if($user->hasRole('kspi') || $user->hasRole('admin'))
                    <form action="{{ route('approvals.approve_direct', $document->id) }}" method="POST" class="flex-shrink-0 self-start sm:self-auto">
                        @csrf
                        <button type="submit" class="btn-primary text-xs py-2 px-4 flex items-center gap-1.5 shadow-sm bg-amber-600 hover:bg-amber-700 border-amber-600 cursor-pointer"
                            onclick="return confirm('Verifikasi dan sahkan dokumen ini sebagai arsip resmi?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Verifikasi & Sahkan Dokumen
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @endif

        {{-- Dokumen Resmi Utama (PKPT Style Full Width) --}}
        <div class="card shadow-sm">
            <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Dokumen Resmi {{ $document->documentType?->name ?? 'Dokumen' }}
                    @if($document->attachments->count() > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-800">
                            {{ $document->attachments->count() }} Berkas
                        </span>
                    @endif
                </h3>
                <div class="flex flex-wrap items-center gap-2.5">
                    @if($document->attachments->count() > 1)
                    <a href="{{ route('documents.download', $document) }}" class="btn-secondary text-sm py-2.5 px-4 flex items-center gap-2 shadow-sm text-emerald-700 hover:text-emerald-800 border-emerald-300 hover:bg-emerald-50 rounded-lg transition-all" title="Unduh semua berkas sekaligus dalam arsip .ZIP">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh Semua (.ZIP)</span>
                    </a>
                    @endif
                    @if($document->canBeEditedBy($user))
                    <a href="{{ route('assurance.edit', $document) }}" class="btn-secondary text-sm py-2.5 px-4 flex items-center gap-2 shadow-sm rounded-lg transition-all">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah / Kelola Berkas</span>
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-5">
                @if($document->attachments->count() > 0)
                    <div class="space-y-3">
                        @foreach($document->attachments as $att)
                            @php
                                $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION));
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-emerald-400 rounded-xl bg-emerald-50/20 hover:bg-emerald-50/40 hover:border-emerald-500 transition-all shadow-2xs">
                                <div class="flex items-center gap-3.5 overflow-hidden">
                                    <x-file-type-icon :ext="$ext" size="lg" />
                                    <div class="truncate">
                                        <h4 class="text-sm font-bold text-charcoal truncate" title="{{ $att->file_name }}">{{ $att->file_name }}</h4>
                                        <p class="text-xs text-charcoal-muted mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Terunggah
                                            </span>
                                            @if($att->file_size)
                                                <span>&bull; {{ $att->file_size_formatted }}</span>
                                            @endif
                                            <span>&bull; Diunggah oleh <strong class="text-charcoal">{{ $att->uploader->name ?? '-' }}</strong> pada {{ $att->created_at->format('d/m/Y H:i') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-auto">
                                    <a href="{{ route('attachments.view', $att->id) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-600 shadow-sm transition-all" title="Lihat Dokumen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('attachments.download', $att->id) }}" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-all" title="Unduh Dokumen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center bg-surface-50 rounded-xl border border-dashed border-surface-200">
                        <svg class="w-10 h-10 text-charcoal-muted/40 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm font-medium text-charcoal">Berkas dokumen {{ $document->documentType?->name ?? 'Dokumen' }} belum diunggah</p>
                        @if($document->canBeEditedBy($user))
                            <p class="text-xs text-charcoal-muted mt-1">Gunakan tombol Tambah / Kelola Berkas di atas untuk mengunggah dokumen resmi.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Catatan Tambahan (Opsional) --}}
        @php
            $catatan = is_string($document->content) ? trim(strip_tags($document->content)) : (is_array($document->content) ? trim($document->content['catatan'] ?? '') : '');
        @endphp
        @if(!empty($catatan))
        <div class="card shadow-sm">
            <div class="card-header bg-surface-50 border-b border-surface-200 py-3 px-5">
                <h4 class="text-sm font-semibold text-charcoal flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    Catatan Tambahan
                </h4>
            </div>
            <div class="card-body p-5">
                <p class="text-sm text-charcoal whitespace-pre-line">{{ $catatan }}</p>
            </div>
        </div>
        @endif

    @endif

</div>
@endsection
