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
        $isSuratTugasOrKka = in_array($document->documentType?->slug, ['surat-tugas', 'kertas-kerja-audit']);
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
                    @if(!$hasAttachment && $isSuratTugasOrKka)
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
                    <a href="{{ route('assurance.index', ['audit_type' => $document->audit_type, 'type' => $document->documentType?->slug]) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION: Surat Tugas & Kertas Kerja Audit --}}
    @if($isSuratTugasOrKka)

        {{-- JIKA USER MENGUPLOAD FILE (HANYA MUNCUL BERKAS YANG DIUPLOAD SAJA) --}}
        @if($hasAttachment)

            {{-- Status & Verifikasi Pengesahan Berkas Ttd Basah --}}
            @if($document->isApproved())
                <div class="card bg-emerald-50/70 border border-emerald-200 shadow-sm">
                    <div class="card-body p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl flex-shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-emerald-950">Arsip Dokumen Bertanda Tangan Basah Sah</h3>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    Berkas fisik telah ditandatangani basah secara offline dan telah diverifikasi pada sistem oleh {{ $document->approvals->where('action', 'approve')->last()->approver->name ?? ($document->creator->name ?? 'Admin') }} pada {{ $document->approved_at ? $document->approved_at->format('d F Y, H:i') : $document->updated_at->format('d F Y, H:i') }}.
                                </p>
                            </div>
                        </div>
                        @if($document->uuid)
                        <a href="{{ route('document.verify', $document->uuid) }}" target="_blank" class="btn-secondary text-xs py-2 px-3.5 bg-white hover:bg-emerald-50 border-emerald-300 text-emerald-800 flex items-center gap-1.5 flex-shrink-0 shadow-sm self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Verifikasi Arsip
                        </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="card bg-amber-50/70 border border-amber-200 shadow-sm">
                    <div class="card-body p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl flex-shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-amber-950">Arsip Menunggu Verifikasi KSPI / Admin</h3>
                                <p class="text-xs text-amber-700 mt-0.5">
                                    Berkas fisik bertanda tangan basah telah diunggah. Pejabat KSPI atau Admin dapat memverifikasi berkas ini untuk meresmikan arsip.
                                </p>
                            </div>
                        </div>
                        @if($user->hasRole('kspi') || $user->hasRole('admin'))
                        <form action="{{ route('approvals.approve_direct', $document->id) }}" method="POST" class="flex-shrink-0 self-start sm:self-auto">
                            @csrf
                            <button type="submit" class="btn-primary text-xs py-2 px-4 flex items-center gap-1.5 shadow-sm bg-amber-600 hover:bg-amber-700 border-amber-600"
                                onclick="return confirm('Verifikasi dan sahkan berkas tanda tangan basah ini sebagai arsip resmi?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Verifikasi & Sahkan Berkas
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Berkas yang Diunggah --}}
            <div class="card shadow-sm">
                <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Berkas Dokumen yang Diunggah ({{ $document->attachments->count() }})
                    </h3>
                </div>
                <div class="card-body p-5 space-y-3">
                    @forelse($document->attachments as $attachment)
                        @php
                            $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                            $previewUrl = $attachment->pdf_path ? Storage::url($attachment->pdf_path) : Storage::url($attachment->file_path);
                            $downloadUrl = Storage::url($attachment->file_path);
                            $iconColor = match($ext) {
                                'pdf' => 'bg-red-50 text-red-600',
                                'doc', 'docx' => 'bg-blue-50 text-blue-600',
                                'xls', 'xlsx' => 'bg-emerald-50 text-emerald-600',
                                default => 'bg-surface-100 text-primary-600'
                            };
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-surface-200 rounded-xl bg-white hover:border-surface-300 transition-all">
                            <div class="flex items-center gap-3.5 overflow-hidden">
                                <div class="p-3 {{ $iconColor }} rounded-xl flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="truncate">
                                    <h4 class="text-sm font-bold text-charcoal truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</h4>
                                    <p class="text-xs text-charcoal-muted mt-1">
                                        {{ $attachment->file_size_formatted }} • Diunggah oleh {{ $attachment->uploader->name ?? '-' }} pada {{ $attachment->created_at->format('d F Y, H:i') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-auto">
                                <a href="{{ $previewUrl }}" target="_blank" class="btn-primary text-xs py-2 px-4 flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat
                                </a>
                                <a href="{{ $downloadUrl }}" download class="btn-secondary text-xs py-2 px-4 flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Unduh
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-charcoal-muted italic text-center py-4">Belum ada berkas terlampir.</p>
                    @endforelse
                </div>
            </div>

        {{-- JIKA USER MEMBUAT DARI INPUTAN (HANYA MUNCUL DOKUMEN DIGITAL SISTEM) --}}
        @else

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
                <div class="card bg-gradient-to-br from-emerald-50 to-emerald-100/60 border border-emerald-200 lg:col-span-1 h-full shadow-sm">
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
                        <button type="submit" class="btn-primary text-sm flex items-center gap-2"
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
                            <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2"
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
                            <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2"
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

        @endif

    {{-- SECTION: Dokumen Tipe Lain (PKA, LHA, Slide Entry, Pendamping) --}}
    @elseif(in_array($document->documentType?->slug, ['program-kerja', 'slide-entry', 'lha', 'lha-eksternal', 'lap-pendamping']))
        <div class="card {{ $document->isApproved() ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-amber-400' }} mt-6 shadow-sm">
            <div class="card-header bg-surface-50 border-b border-surface-200 flex justify-between items-center">
                <h3 class="card-title text-charcoal flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Dokumen Utama & Pengesahan
                </h3>
                <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
            </div>
            <div class="card-body p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- Bagian File --}}
                    <div class="lg:col-span-2">
                        <h4 class="font-semibold text-charcoal mb-3 text-sm">File Dokumen yang Diunggah:</h4>
                        @if($document->attachments->count() > 0)
                            @foreach($document->attachments as $attachment)
                            <div class="p-4 border border-surface-200 rounded-lg bg-white shadow-sm mb-4 last:mb-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="p-2 bg-primary-50 rounded-lg text-primary-600 flex-shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-charcoal truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</p>
                                            <p class="text-xs text-charcoal-muted mt-0.5">{{ $attachment->file_size_formatted }} • Diunggah oleh {{ $attachment->uploader->name ?? '-' }} pada {{ $attachment->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 flex-shrink-0">
                                        @php
                                            $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                                        @endphp
                                        @if($attachment->pdf_path)
                                            <a href="{{ Storage::url($attachment->pdf_path) }}" target="_blank" class="btn-primary btn-sm flex items-center justify-center gap-1.5 flex-shrink-0 shadow-sm py-1.5 px-3">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Preview
                                            </a>
                                        @elseif(in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']))
                                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn-primary btn-sm flex items-center justify-center gap-1.5 flex-shrink-0 shadow-sm py-1.5 px-3">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Preview
                                            </a>
                                        @endif
                                        <a href="{{ Storage::url($attachment->file_path) }}" download class="btn-secondary text-sm py-1.5 px-3 flex items-center gap-1.5 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Unduh
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-sm text-charcoal-muted mb-4">Belum ada file yang diunggah.</p>
                        @endif
                    </div>

                    {{-- Bagian Approval --}}
                    <div>
                        <h4 class="font-semibold text-charcoal mb-3 text-sm">Status Pengesahan:</h4>
                        @if(in_array($document->status, ['draft', 'revised', 'pending_kspi']))
                            @if($user->hasRole('kspi') || $user->hasRole('admin'))
                                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                                    <form action="{{ route('approvals.approve_direct', $document->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2"
                                            onclick="return confirm('Sahkan dokumen ini secara langsung?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Sahkan Dokumen
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 flex flex-col items-center text-center">
                                    <div class="p-2 bg-amber-100 text-amber-600 rounded-full mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-amber-700">Menunggu Pengesahan KSPI/Admin</p>
                                </div>
                            @endif
                        @elseif($document->isApproved())
                            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 flex items-center gap-3">
                                <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-emerald-900 text-sm">Telah Disahkan</p>
                                    <p class="text-xs text-emerald-700 mt-0.5">{{ $document->approvals->where('action', 'approve')->last()->approver->name ?? '-' }} &bull; {{ $document->approved_at ? $document->approved_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
@endsection
