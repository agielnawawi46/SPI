@extends('layouts.app')
@section('title', $document->title)
@section('page_title', 'Detail Dokumen Advisory')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('advisory.index', ['type' => $document->documentType?->slug]) }}" class="breadcrumb-link">Advisory</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $document->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Advisory Header Info --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $document->title }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    <span>Jenis: <strong class="text-charcoal">{{ $document->documentType?->name ?? '-' }}</strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Dibuat oleh <strong class="text-charcoal">{{ $document->creator->name ?? '-' }}</strong> pada {{ $document->created_at->format('d F Y') }}</span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
                
                <div class="flex items-center gap-2">
                    @if($document->documentType?->slug === 'bak')
                        <a href="{{ route('advisory.pdf', $document) }}" target="_blank" class="btn-primary text-sm bg-red-600 hover:bg-red-700 border-red-600 hover:border-red-700 text-white flex items-center shadow-sm print:hidden">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export PDF
                        </a>
                    @endif
                    @if($document->canBeEditedBy(Auth::user()))
                        <a href="{{ route('advisory.edit', $document) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    @endif
                    <a href="{{ route('advisory.index', ['type' => $document->documentType?->slug]) }}" class="btn-secondary text-sm flex items-center shadow-sm print:hidden">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($document->documentType?->slug !== 'lap-konsultasi')
    {{-- Approval Status Container & QR Code --}}
    <div class="grid grid-cols-1 {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:grid-cols-3' : '' }} gap-4 mb-4">
        
        {{-- Approval History --}}
        @if($document->approvals->isNotEmpty())
        <div class="card {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:col-span-2' : '' }}">
            <div class="card-body py-3 px-4 overflow-x-auto">
                <h3 class="text-sm font-semibold text-charcoal mb-3">Riwayat Persetujuan</h3>
                <div class="flex items-start w-full min-w-[400px]">
                    @foreach($document->approvals->sortBy('created_at') as $approval)
                    <div class="relative flex-1 text-center group px-1">
                        {{-- Connector Line --}}
                        @if(!$loop->last)
                        <div class="absolute top-3.5 left-[50%] w-full h-0.5 {{ $approval->action === 'approve' ? 'bg-emerald-400' : 'bg-surface-200' }} z-0 mt-[1px]"></div>
                        @endif

                        {{-- Step Icon --}}
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

                        {{-- Step Info --}}
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

        {{-- Approved: Show QR code info --}}
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
    @endif

    {{-- Berkas Lampiran Dokumen Advisory (Jika Ada) --}}
    @if($document->attachments->isNotEmpty())
    <div class="card shadow-sm">
        <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Berkas Lampiran Dokumen ({{ $document->attachments->count() }})
            </h3>
        </div>
        <div class="card-body p-5 space-y-3">
            @foreach($document->attachments as $attachment)
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
                    $fileSizeFormatted = method_exists($attachment, 'getFileSizeFormattedAttribute') 
                        ? $attachment->file_size_formatted 
                        : number_format($attachment->file_size / 1024, 1) . ' KB';
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-surface-200 rounded-xl bg-white hover:border-surface-300 transition-all">
                    <div class="flex items-center gap-3.5 overflow-hidden">
                        <div class="p-3 {{ $iconColor }} rounded-xl flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="truncate">
                            <h4 class="text-sm font-bold text-charcoal truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</h4>
                            <p class="text-xs text-charcoal-muted mt-1">
                                {{ $fileSizeFormatted }} • Diunggah oleh {{ $attachment->uploader->name ?? ($document->creator?->name ?? '-') }} pada {{ $attachment->created_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-auto">
                        @if($attachment->pdf_path || in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']))
                        <a href="{{ $previewUrl }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-90 shadow-sm transition-all" title="Lihat Dokumen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @endif
                        <a href="{{ $downloadUrl }}" download class="btn-icon bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all" title="Unduh Dokumen">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($document->documentType?->slug !== 'lap-konsultasi')
    {{-- Approval Action Panel --}}
    @php
        $user = Auth::user();
        $pendingKspi   = $document->approvals->where('approval_level', 1)->where('action', null)->first();
        $pendingDir    = $document->approvals->where('approval_level', 2)->where('action', null)->first();
    @endphp

    {{-- Auditor: Submit to KSPI --}}
    @if(in_array($document->status, ['draft','revised']) && $document->created_by === $user->id)
        @if($document->documentType?->slug !== 'bak' && !$document->qr_code_path_auditee)
        <div class="card border-l-4 border-l-blue-400">
            <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-charcoal">Tanda Tangani Dokumen</h3>
                    <p class="text-sm text-charcoal-muted mt-1">Dokumen ini belum ditandatangani. Klik tombol di samping untuk membubuhkan tanda tangan Pimpinan Klien (QR Code).</p>
                </div>
                <form action="{{ route('approvals.sign_auditee', ['id' => $document->id]) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn-primary text-sm flex items-center gap-2"
                        onclick="return confirm('Tandatangani dokumen ini sebagai Pimpinan Klien?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Tanda Tangan Pimpinan
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card border-l-4 border-l-amber-400">
            <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-charcoal">Kirim untuk Direview</h3>
                    <p class="text-sm text-charcoal-muted mt-1">
                        @if($document->documentType?->slug === 'bak')
                            Kirim dokumen ini ke KSPI untuk direview dan disetujui.
                        @else
                            Dokumen ini sudah ditandatangani. Kirim ke KSPI untuk direview dan disetujui.
                        @endif
                    </p>
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
    @endif

    {{-- KSPI: Approve or Request Revision --}}
    @if($document->status === 'pending_kspi' && $user->hasRole('kspi') && $pendingKspi)
    <div class="card border-l-4 border-l-blue-400">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Review KSPI</h3>
            <p class="text-sm text-charcoal-muted mb-4">Dokumen ini menunggu persetujuan Anda sebagai KSPI. Jika Anda menemukan kesalahan, Anda dapat langsung mengubahnya (Edit) sebelum menyetujui.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <form action="{{ route('approvals.action', $pendingKspi) }}" method="POST" class="flex-1">
                    @csrf
                    @if($document->documentType?->slug === 'bak')
                        <div class="mb-4 space-y-3">
                            <div class="form-group">
                                <label class="text-sm font-semibold text-charcoal">Review Permohonan Konsultasi</label>
                                <textarea name="review_permohonan" class="form-textarea w-full text-sm" rows="2" placeholder="...">{{ old('review_permohonan', $document->review_permohonan) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="text-sm font-semibold text-charcoal">Alasan</label>
                                <textarea name="alasan" class="form-textarea w-full text-sm" rows="2" placeholder="...">{{ old('alasan', $document->alasan) }}</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2"
                            onclick="return confirm('Setujui dokumen ini? Dokumen akan diteruskan ke Auditee Manager.')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approve & Teruskan ke Auditee
                        </button>
                    @else
                        <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional untuk approve, wajib untuk revisi)"></textarea>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button type="submit" name="action" value="approve" class="btn-primary text-sm flex-1 flex items-center justify-center gap-2"
                                onclick="return confirm('Setujui dokumen ini dan teruskan ke Direktur?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Approve & Teruskan ke Direktur
                            </button>
                            <button type="submit" name="action" value="reject" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex-1 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Revisi
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Direktur: Approve or Request Revision --}}
    @if($document->status === 'pending_direktur' && $user->hasRole('direktur') && $pendingDir)
    <div class="card border-l-4 border-l-purple-400">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Persetujuan Direktur</h3>
            <p class="text-sm text-charcoal-muted mb-4">Dokumen telah disetujui KSPI dan menunggu tanda tangan Direktur. Jika disetujui, sistem akan membubuhkan barcode secara otomatis.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <form action="{{ route('approvals.action', $pendingDir) }}" method="POST" class="flex-1">
                    @csrf
                    <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional untuk approve, wajib untuk revisi)"></textarea>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" name="action" value="approve" class="btn-primary text-sm flex-1 flex items-center justify-center gap-2"
                            onclick="return confirm('Setujui dan tandatangani dokumen ini? Barcode akan dibubuhkan secara otomatis.')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Approve & Barcode
                        </button>
                        <button type="submit" name="action" value="reject" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex-1 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Auditee Manager: Approve & Tandatangani --}}
    @if($document->status === 'pending_auditee' && $document->auditee_id === Auth::id())
    <div class="card border-l-4 border-l-emerald-400 mt-4 mb-4">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Persetujuan Auditee</h3>
            <p class="text-sm text-charcoal-muted mb-4">Dokumen ini telah direview dan disetujui KSPI. Silakan setujui dan bubuhkan tanda tangan Anda sebagai Auditee.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <form action="{{ route('approvals.sign_auditee', $document->id) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-primary text-sm w-full flex items-center justify-center gap-2"
                        onclick="return confirm('Setujui dan tandatangani dokumen ini? Barcode Anda akan dibubuhkan secara otomatis.')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Setujui & Tandatangani
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- Dokumen Utama & Pengesahan for Laporan Konsultasi --}}
    @if($document->documentType?->slug === 'lap-konsultasi')
    <div class="card mt-6">
        <div class="card-header bg-surface-50 border-b border-surface-200 flex justify-between items-center">
            <h3 class="card-title text-charcoal flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dokumen Laporan Konsultasi
            </h3>
        </div>
        <div class="card-body p-6">
            <div class="grid grid-cols-1 gap-6">
                
                {{-- Bagian File --}}
                <div>
                    <h4 class="font-semibold text-charcoal mb-3 text-sm">File Dokumen yang Diunggah:</h4>
                    @if($document->attachments->count() > 0)
                        @foreach($document->attachments as $attachment)
                        <div class="p-4 border border-emerald-400 rounded-xl bg-emerald-50/20 hover:bg-emerald-50/40 hover:border-emerald-500 transition-all shadow-2xs mb-4 last:mb-0">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="p-2 bg-emerald-100/80 rounded-lg text-emerald-700 flex-shrink-0 border border-emerald-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-charcoal truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</p>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Terunggah
                                            </span>
                                        </div>
                                        <p class="text-xs text-charcoal-muted mt-0.5">{{ $attachment->file_size_formatted }} • Diunggah oleh {{ $attachment->uploader->name ?? '-' }} pada {{ $attachment->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    @php
                                        $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                                    @endphp
                                    @if($attachment->pdf_path)
                                        <a href="{{ Storage::url($attachment->pdf_path) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-90 shadow-sm transition-all" title="Lihat Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    @elseif(in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']))
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-90 shadow-sm transition-all" title="Lihat Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    @endif
                                    <a href="{{ Storage::url($attachment->file_path) }}" download target="_blank" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all" title="Unduh Dokumen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-charcoal-muted mb-4">Belum ada file yang diunggah.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

    @if($document->documentType?->slug === 'bak')
    <!-- A4 Paper Container for Berita Acara Konsultasi -->
    <style>
        @media print {
            @page { size: A4; margin: 0 15mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-container { padding: 0 !important; margin: 0 !important; box-shadow: none !important; max-width: none !important; min-height: 0 !important; page-break-after: always; }
            .print-container:last-child { page-break-after: auto; }
            .page-number::after { counter-increment: page; content: counter(page); }
        }
    </style>

    <!-- HALAMAN 1 (COVER) -->
    <div class="bg-white shadow-xl mx-auto p-10 max-w-[210mm] min-h-[297mm] mb-8 text-black text-[11pt] leading-[1.3] print-container flex flex-col" style="font-family: Arial, sans-serif;">
        <table class="w-full text-left flex-grow" style="table-layout: fixed; height: 100%;">
            <thead>
                <tr>
                    <td class="pb-4">
                        <div class="flex justify-between items-center">
                            <div class="w-1/2">
                                    <img src="{{ asset('pln.jpg') }}" alt="Logo Instansi" class="h-20 object-contain">
                            </div>
                            <div class="w-1/2 flex justify-end">
                                <div class="text-[8pt] leading-tight text-[#002060] font-normal font-sans text-left">
                                    <p>No. Formulir : {{ $document->nomor_formulir ?? 'FMR-003-06' }}</p>
                                    <p>Tanggal : {{ $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') : ($document->created_at ? $document->created_at->translatedFormat('d F Y') : '-') }}</p>
                                    <p>Halaman : 1</p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td class="pt-4">
                        <div class="w-full mt-auto flex justify-center">
                            <img src="{{ asset('pln2.jpg') }}" alt="Footer Image" class="w-full object-contain">
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <td class="align-top pt-10" style="height: 100%;">
                        <div class="flex flex-col items-center text-center">
                            <h4 class="font-bold text-[21pt] uppercase text-[#006563]">{{ $document->judul_dokumen ?? 'BERITA ACARA KONSULTANSI' }}</h4>
                            <h4 class="font-bold text-[21pt] uppercase text-[#006563] mt-10">{{ $document->auditee ?? 'SATUAN PENGAWASAN INTERN' }}</h4>
                            <div class="mt-1 text-[14pt] text-[#006563]">
                                <p class="font-bold">NOMOR : {{ $document->title }}</p>
                                <p class="font-bold">TGL {{ $document->tgl_stg ? \Carbon\Carbon::parse($document->tgl_stg)->translatedFormat('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- HALAMAN 2 (FORMULIR) -->
    <div class="bg-white shadow-xl mx-auto p-10 max-w-[210mm] min-h-[297mm] mb-8 text-black text-[11pt] leading-[1.3] print-container flex flex-col" style="font-family: Arial, sans-serif;">
        <table class="w-full text-left flex-grow" style="table-layout: fixed; height: 100%;">
            <thead>
                <tr>
                    <td class="pb-4">
                        <div class="flex justify-between items-center">
                            <div class="w-1/2">
                                    <img src="{{ asset('pln.jpg') }}" alt="Logo Instansi" class="h-20 object-contain">
                            </div>
                            <div class="w-1/2 flex justify-end">
                                <div class="text-[8pt] leading-tight text-[#002060] font-normal font-sans text-left">
                                    <p>No. Formulir : {{ $document->nomor_formulir ?? 'FMR-003-06' }}</p>
                                    <p>Tanggal : {{ $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') : ($document->created_at ? $document->created_at->translatedFormat('d F Y') : '-') }}</p>
                                    <p>Halaman : 2</p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td class="pt-4">
                        <div class="w-full mt-auto flex justify-center">
                            <img src="{{ asset('pln2.jpg') }}" alt="Footer Image" class="w-full object-contain">
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <td class="pt-4 align-top" style="height: 100%;">
                        <div class="text-center mb-6">

                        <div class="text-center mb-6">
                            <h4 class="font-bold text-[12pt] uppercase underline">FORMULIR LAYANAN KONSULTASI</h4>
                            <p class="font-bold text-[11pt]">Nomor: {{ $document->title }}</p>
                        </div>

                                                <table class="w-full text-left border-collapse border border-black mb-1" style="table-layout: fixed;">
                            <tbody>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top" style="width: 38%;">Nama / Jabatan</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center" style="width: 3%;">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top" style="width: 59%;">{{ $document->nama_jabatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top">Nomor Induk Pegawai (NIP)</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top">{{ $document->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top">Bidang Tugas (Bidang/Satuan/Tim Kerja)</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top">{{ $document->bidang_tugas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top h-20">Deskripsi Permasalahan</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top text-justify">{{ $document->deskripsi_permasalahan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top h-20">Tujuan Konsultasi</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top text-justify">{{ $document->tujuan_konsultasi ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top">Waktu penyelesaian yang dibutuhkan</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top">{{ $document->waktu_penyelesaian ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top h-20">Pengungkapan Potensi Pengurangan Independensi dan Objektifitas Auditor</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top text-justify">{{ $document->pengungkapan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top h-20">Review Permohonan Konsultasi *)</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top text-justify">{{ $document->review_permohonan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-1.5 px-2 align-top h-32">Kesimpulan *)</td>
                                    <td class="border border-black py-1.5 px-1 align-top text-center">:</td>
                                    <td class="border border-black py-1.5 px-2 align-top">
                                        <div class="mb-4">
                                            <span class="font-bold">Terima (dilanjutkan)</span>
                                        </div>
                                        <div>
                                            <p class="mb-1">Alasan :</p>
                                            <p class="text-justify">{{ $document->alasan ?? '-' }}</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border border-black py-3 px-2" colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <p class="text-[10pt] italic mb-4 text-left">*) diisi oleh KSPI</p>

                        <table class="w-full text-center border-collapse border border-black" style="table-layout: fixed;">
                            <tbody>
                                <tr>
                                    <td class="border border-black align-top pt-2 pb-2 w-1/2">
                                        <p>&nbsp;</p>
                                        <p class="mb-8">Kepala Satuan Pengawasan Intern</p>
                                        <div class="my-2 flex justify-center h-20">
                                            @if($document->qr_code_path)
                                                <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code KSPI" class="h-20 w-20 object-contain">
                                            @else
                                                <div class="h-20"></div>
                                            @endif
                                        </div>
                                        <p class="font-bold uppercase">{{ $document->nama_kspi ?? '' }}</p>
                                    </td>
                                    <td class="border border-black align-top pt-2 pb-2 w-1/2">
                                        <p>Batam, {{ $document->created_at->format('d F Y') }}</p>
                                        <p class="mb-8">Pimpinan Klien</p>
                                        <div class="my-2 flex justify-center h-20">
                                            @if($document->qr_code_path_auditee)
                                                <img src="{{ asset('storage/' . $document->qr_code_path_auditee) }}" alt="QR Code Auditee" class="h-20 w-20 object-contain">
                                            @else
                                                <div class="h-20"></div>
                                            @endif
                                        </div>
                                        <p class="font-bold uppercase">{{ $document->nama_pimpinan_klien ?? (explode('/', $document->nama_jabatan)[0] ?? '______________________') }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

