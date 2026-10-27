@extends('layouts.app')
@section('title', $document->title)
@section('page_title', 'Detail Laporan Kinerja')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('performance-reports.index') }}" class="breadcrumb-link">Laporan Kinerja</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $document->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    @php
        $user = Auth::user();
        $hasAttachment = $document->attachments->count() > 0;
        $pendingKspi = $document->approvals->where('approval_level', 1)->where('action', null)->first();
        $pendingDir  = $document->approvals->where('approval_level', 2)->where('action', null)->first();
    @endphp

    {{-- Performance Report Header Info --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $document->title }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    @if($document->report_month)
                        <span>Periode: <strong class="text-charcoal">{{ \Carbon\Carbon::create(null, $document->report_month)->translatedFormat('F') }} {{ $document->report_year }}</strong></span>
                        <span class="text-surface-300">•</span>
                    @endif
                    <span>Dibuat oleh <strong class="text-charcoal">{{ $document->creator?->name ?? '-' }}</strong> pada {{ $document->created_at->format('d F Y') }}</span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
                
                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if($document->canBeEditedBy($user))
                        <a href="{{ route('performance-reports.edit', $document) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit Lampiran
                        </a>
                    @endif
                    <a href="{{ route('performance-reports.index') }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Deskripsi / Konten Dokumen --}}
    @if(!empty($document->content))
    <div class="card shadow-sm">
        <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5">
            <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                Deskripsi Laporan
            </h3>
        </div>
        <div class="card-body p-5">
            <p class="text-sm text-charcoal leading-relaxed whitespace-pre-wrap">{{ $document->content }}</p>
        </div>
    </div>
    @endif

    {{-- Berkas Lampiran Laporan Kinerja --}}
    <div class="card shadow-sm">
        <div class="card-header bg-surface-50 border-b border-surface-200 py-3.5 px-5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-charcoal flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Berkas Lampiran Dokumen ({{ $document->attachments->count() }})
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
                    <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-auto">
                        @if($attachment->pdf_path)
                        <a href="{{ $previewUrl }}" target="_blank" class="btn-primary text-xs py-2 px-4 flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Preview PDF
                        </a>
                        @endif
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

    {{-- Approval Stepper & QR Code Container --}}
    <div class="grid grid-cols-1 {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:grid-cols-3' : '' }} gap-4">
        
        {{-- Approval History Stepper --}}
        @if($document->approvals->isNotEmpty())
        <div class="card {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:col-span-2' : '' }}">
            <div class="card-body py-3 px-4 overflow-x-auto">
                <h3 class="text-sm font-semibold text-charcoal mb-3">Riwayat Persetujuan Laporan Kinerja</h3>
                <div class="flex items-start w-full min-w-[360px]">
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
                            @if($approval->comments)
                            <p class="text-[10px] text-charcoal mt-1 bg-surface-50 p-1.5 rounded border border-surface-200 text-left font-medium">"{{ $approval->comments }}"</p>
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
                <div class="bg-white p-2.5 rounded-xl border border-emerald-200 shadow-sm flex-shrink-0">
                    <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="w-20 h-20 md:w-24 md:h-24 object-contain">
                </div>
                <div class="text-left">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Terverifikasi
                    </span>
                    <h3 class="font-bold text-emerald-900 text-sm md:text-base mt-1">Dokumen Resmi Digital</h3>
                    <p class="text-[11px] text-emerald-700 font-medium">Tgl: {{ $document->approved_at?->format('d M Y') }}</p>
                    @if($document->uuid)
                    <a href="{{ route('document.verify', $document->uuid) }}" target="_blank" class="mt-2.5 text-xs font-semibold text-emerald-800 bg-white hover:bg-emerald-50 py-1 px-3 rounded-lg shadow-sm transition-colors border border-emerald-200 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Cek Sertifikat Digital
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Multi-level Approval Actions (Submit, Review, Sign) --}}
    {{-- Submitter: Submit to KSPI --}}
    @if(in_array($document->status, ['draft','revised']) && ($document->created_by === $user->id || $user->hasRole('admin')))
    <div class="card border-l-4 border-l-amber-400 shadow-sm">
        <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-charcoal">Kirim untuk Direview</h3>
                <p class="text-sm text-charcoal-muted mt-1">Laporan kinerja ini belum diajukan. Kirim ke KSPI untuk direview dan disetujui.</p>
            </div>
            <form action="{{ route('approvals.submit', ['type' => 'document', 'id' => $document->id]) }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="btn-primary text-sm flex items-center gap-2"
                    onclick="return confirm('Kirim laporan kinerja ini ke KSPI untuk direview?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim ke KSPI
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- KSPI: Review & Approve / Revise --}}
    @if($document->status === 'pending_kspi' && $user->hasRole('kspi') && $pendingKspi)
    <div class="card border-l-4 border-l-blue-400 shadow-sm">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Review KSPI</h3>
            <p class="text-sm text-charcoal-muted mb-4">Laporan kinerja ini menunggu persetujuan Anda sebagai KSPI. Anda dapat menyetujui atau meminta revisi.</p>
            <form action="{{ route('approvals.action', $pendingKspi) }}" method="POST">
                @csrf
                <textarea name="comments" class="form-textarea w-full mb-3 text-sm" rows="2" placeholder="Catatan (opsional untuk persetujuan, wajib jika meminta revisi)"></textarea>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" name="action" value="approve" class="btn-primary text-sm flex items-center justify-center gap-2"
                        onclick="return confirm('Setujui laporan kinerja ini dan teruskan ke Direktur?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui & Teruskan ke Direktur
                    </button>
                    <button type="submit" name="action" value="reject" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex items-center justify-center gap-2"
                        onclick="return confirm('Kembalikan laporan ini untuk direvisi?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Minta Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Direktur: Review & Final Approve / Revise --}}
    @if($document->status === 'pending_direktur' && $user->hasRole('direktur') && $pendingDir)
    <div class="card border-l-4 border-l-emerald-500 shadow-sm">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Persetujuan Akhir Direktur</h3>
            <p class="text-sm text-charcoal-muted mb-4">Laporan kinerja telah direview oleh KSPI dan menunggu pengesahan digital Direktur.</p>
            <form action="{{ route('approvals.action', $pendingDir) }}" method="POST">
                @csrf
                <textarea name="comments" class="form-textarea w-full mb-3 text-sm" rows="2" placeholder="Catatan persetujuan (opsional)"></textarea>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" name="action" value="approve" class="btn-primary text-sm flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 border-emerald-600"
                        onclick="return confirm('Sahkan laporan kinerja ini secara resmi?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Sahkan Laporan (Tanda Tangan Digital)
                    </button>
                    <button type="submit" name="action" value="reject" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex items-center justify-center gap-2"
                        onclick="return confirm('Kembalikan laporan ini untuk direvisi?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Minta Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

