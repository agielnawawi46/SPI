@extends('layouts.app')
@section('title', $document->title)
@section('page_title', 'Detail Dokumen QA')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.qa.index', ['type' => $document->documentType?->slug]) }}" class="breadcrumb-link">Quality Assurance</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $document->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $document->title }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    <span>Jenis: <strong class="text-charcoal">{{ $document->documentType?->name ?? '-' }}</strong></span>
                    <span class="text-surface-300">•</span>
                    <span>Dibuat oleh <strong class="text-charcoal">{{ $document->creator?->name ?? '-' }}</strong> pada {{ $document->created_at->format('d F Y') }}</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
                <div class="flex items-center gap-2">
                    @if($document->documentType?->slug === 'st-qa')
                        <a href="{{ route('other-activities.qa.pdf', $document) }}" class="btn-primary text-sm bg-red-600 hover:bg-red-700 border-red-600 hover:border-red-700 text-white flex items-center shadow-sm" target="_blank">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export PDF
                        </a>
                    @endif
                    @if($document->canBeEditedBy(Auth::user()))
                        <a href="{{ route('other-activities.qa.edit', $document) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    @endif

                    <a href="{{ route('other-activities.qa.index', ['type' => $document->documentType?->slug]) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval section for STQ --}}
    @if($document->documentType?->slug === 'st-qa')
    @php
        $pendingKspi = $document->approvals->where('approval_level', 1)->where('action', null)->first();
        $pendingDir  = $document->approvals->where('approval_level', 2)->where('action', null)->first();
        $user = Auth::user();
    @endphp

    <div class="grid grid-cols-1 {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:grid-cols-3' : '' }} gap-4 mb-4">
        
        {{-- Approval History --}}
        @if($document->approvals->isNotEmpty())
        <div class="card {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:col-span-2' : '' }}">
            <div class="card-body py-3 px-4 overflow-x-auto">
                <h3 class="text-sm font-semibold text-charcoal mb-3">Riwayat Persetujuan</h3>
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
                            <h4 class="text-[11px] font-semibold text-charcoal leading-tight">Level {{ $approval->approval_level }} &mdash; <span class="capitalize">{{ $approval->approval_role }}</span></h4>
                            <p class="text-[10px] font-bold mt-1 leading-none {{ $approval->action === 'approve' ? 'text-emerald-600' : ($approval->action === 'reject' ? 'text-red-600' : 'text-amber-600') }}">
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
                <div class="bg-white p-2.5 rounded-xl border border-emerald-200 shadow-sm flex-shrink-0">
                    <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="w-20 h-20 md:w-24 md:h-24 object-contain">
                </div>
                <div class="text-left">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Terverifikasi
                    </span>
                    <h3 class="font-bold text-emerald-900 text-sm md:text-base mt-1">Dokumen Resmi</h3>
                    <p class="text-[11px] text-emerald-700 font-medium">Disahkan: {{ $document->approved_at?->format('d M Y') }}</p>
                    <a href="{{ route('document.verify', $document->uuid) }}" target="_blank" class="mt-2.5 text-xs font-semibold text-emerald-800 bg-white hover:bg-emerald-50 py-1 px-3 rounded-lg shadow-sm transition-colors border border-emerald-200 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Cek Sertifikat Digital
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(in_array($document->status, ['draft','revised']) && $document->created_by === $user->id)
    <div class="card border-l-4 border-l-amber-400">
        <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-charcoal">Kirim untuk Direview</h3>
                <p class="text-sm text-charcoal-muted mt-1">Dokumen ini belum diajukan. Kirim ke KSPI untuk direview dan disetujui.</p>
            </div>
            <form action="{{ route('approvals.submit', ['type' => 'document', 'id' => $document->id]) }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="btn-primary text-sm" onclick="return confirm('Kirim dokumen ini ke KSPI untuk direview?')">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim ke KSPI
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($document->status === 'pending_kspi' && $user->hasRole('kspi') && $pendingKspi)
    <div class="card border-l-4 border-l-blue-400">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Review KSPI</h3>
            <p class="text-sm text-charcoal-muted mb-4">Dokumen ini menunggu persetujuan Anda sebagai KSPI.</p>
            <form action="{{ route('approvals.action', $pendingKspi) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="approve">
                <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional)"></textarea>
                <button type="submit" class="btn-primary text-sm w-full" onclick="return confirm('Setujui dokumen ini?')">
                    Approve & Teruskan ke Direktur
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($document->status === 'pending_direktur' && $user->hasRole('direktur') && $pendingDir)
    <div class="card border-l-4 border-l-purple-400">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Persetujuan Direktur</h3>
            <p class="text-sm text-charcoal-muted mb-4">Dokumen menunggu tanda tangan Direktur.</p>
            <form action="{{ route('approvals.action', $pendingDir) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="approve">
                <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional)"></textarea>
                <button type="submit" class="btn-primary text-sm w-full" onclick="return confirm('Setujui dan tandatangani dokumen ini?')">
                    Approve & Bubuhkan Barcode
                </button>
            </form>
        </div>
    </div>
    @endif



    {{-- STQ Document Preview (Hanya tampil jika tidak ada berkas unggahan) --}}
    @if($document->attachments->count() === 0)
    <div class="bg-white shadow-xl mx-auto p-10 max-w-[210mm] min-h-[297mm] mb-8 text-black text-[11pt] flex flex-col leading-[1.15]" style="font-family: Arial, sans-serif;">
        <div class="flex items-center justify-between mb-4 pb-2">
            <div class="w-1/4">
                <img src="{{ asset('pln.jpg') }}" alt="Header" class="max-h-24 object-contain">
            </div>
            <div class="w-3/4 text-right text-[8pt] text-[#002060] leading-snug">
                @if($document->header_text)
                <p>No. Formulir : {{ $document->header_text }}</p>
                @endif
                @if($document->tanggal_formulir)
                <p>Tanggal Formulir : {{ \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') }}</p>
                @endif
                <p>Tanggal : {{ $document->tgl_stg ? $document->tgl_stg->translatedFormat('d F Y') : $document->created_at->translatedFormat('d F Y') }}</p>
                <p>Halaman : 1</p>
            </div>
        </div>
        <div class="text-center mb-6">
            <h4 class="font-bold text-[12pt] uppercase">{{ $document->judul_dokumen ?? 'SURAT TUGAS AUDIT QA' }}</h4>
            <p class="font-medium text-[12pt]">{{ $document->title }}</p>
        </div>
        <div class="mb-4">
            <p class="whitespace-pre-line text-justify">{{ $document->deskripsi_pembuka ?? '-' }}</p>
        </div>
        <div class="mt-4 mb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse border border-black" style="table-layout: fixed; margin-bottom: -1px;">
                    <thead>
                        <tr>
                            <th class="border border-black px-2 py-2 text-center font-semibold bg-gray-50" style="width: 5%;">No.</th>
                            <th class="border border-black px-4 py-2 text-center font-semibold bg-gray-50" style="width: 70%;">Identitas Tim QA</th>
                            <th class="border border-black px-4 py-2 text-center font-semibold bg-gray-50" style="width: 25%;">Jabatan Tim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @if($document->tim_audit && is_array($document->tim_audit))
                            @foreach($document->tim_audit as $tim)
                            <tr>
                                <td class="border border-black px-2 py-2 text-center align-top">{{ $counter++ }}.</td>
                                <td class="border border-black px-4 py-2 align-top">
                                    <div class="grid grid-cols-[70px_10px_1fr] gap-x-2">
                                        <div>Nama</div><div>:</div><div class="uppercase break-words" style="word-break: break-all;">{{ $tim['nama'] ?? '-' }}</div>
                                        <div>NIP</div><div>:</div><div class="break-words" style="word-break: break-all;">{{ $tim['nip'] ?? '-' }}</div>
                                        <div>Jabatan</div><div>:</div><div class="capitalize break-words" style="word-break: break-all;">{{ $tim['jabatan_pegawai'] ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="border border-black px-4 py-2 text-center align-middle break-words" style="word-break: break-all;">{{ $tim['jabatan_tim'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <table class="w-full text-left border-collapse border border-black" style="table-layout: fixed;">
                    <tbody>
                        <tr>
                            <td class="border border-black px-4 py-2 align-top" style="width: 32%;">Periode QA</td>
                            <td class="border border-black px-2 py-2 text-center align-top" style="width: 2%;">:</td>
                            <td class="border border-black px-4 py-2 align-top break-words" style="width: 66%; word-break: break-all;">{{ $document->periode_audit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-black px-4 py-2 align-top">Lingkup QA</td>
                            <td class="border border-black px-2 py-2 text-center align-top">:</td>
                            <td class="border border-black px-4 py-2 align-top text-justify break-words" style="word-break: break-all;">{{ $document->lingkup_audit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-black px-4 py-2 align-top">Tujuan QA</td>
                            <td class="border border-black px-2 py-2 text-center align-top">:</td>
                            <td class="border border-black px-4 py-2 align-top text-justify break-words" style="word-break: break-all;">{{ $document->tujuan_audit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-black px-4 py-2 align-top">Waktu Pelaksanaan QA</td>
                            <td class="border border-black px-2 py-2 text-center align-top">:</td>
                            <td class="border border-black px-4 py-2 align-top break-words" style="word-break: break-all;">{{ $document->jadwal_audit ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <p class="whitespace-pre-line text-justify">{{ $document->deskripsi_penutup ?? '-' }}</p>
        </div>
        <div class="flex flex-col items-center mt-12 mb-8">
            <p>{{ $document->tgl_stg ? $document->tgl_stg->format('d F Y') : '-' }}</p>
            <p class="font-bold mb-4">{{ $document->jabatan_direktur ?? '-' }}</p>
            @if($document->qr_code_path)
            <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="w-24 h-24 object-contain my-2">
            @else
            <div class="h-20"></div>
            @endif
            <div class="h-4"></div>
            <p class="font-bold">{{ $document->nama_direktur ?? '-' }}</p>
        </div>
        <div class="flex justify-center mt-auto">
            <img src="{{ asset('pln2.jpg') }}" alt="Footer" class="max-h-24 object-contain">
        </div>
    </div>
    @endif
    @endif

    {{-- Attachments (Dokumen yang Diunggah) --}}
    @if($document->attachments->count() > 0 || in_array($document->documentType?->slug, ['pk-qa', 'kk-qa', 'lap-qa']))
    <div class="card border-l-4 {{ $document->isApproved() ? 'border-l-emerald-500' : 'border-l-amber-400' }}">
        <div class="card-header bg-surface-50 border-b border-surface-200 flex justify-between items-center">
            <h3 class="card-title text-charcoal flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dokumen Utama & Pengesahan
            </h3>
            <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
        </div>
        <div class="card-body p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <h4 class="font-semibold text-charcoal mb-3 text-sm">File Dokumen yang Diunggah:</h4>
                    @if($document->attachments->count() > 0)
                        <div class="space-y-3">
                            @foreach($document->attachments as $attachment)
                            @php
                                $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                                $iconBg = match($ext) {
                                    'pdf' => 'bg-red-50 text-red-600 border border-red-200',
                                    'doc', 'docx' => 'bg-blue-50 text-blue-600 border border-blue-200',
                                    'xls', 'xlsx' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                    default => 'bg-surface-100 text-charcoal-muted border border-surface-200'
                                };
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-emerald-400 rounded-xl bg-emerald-50/20 hover:bg-emerald-50/40 hover:border-emerald-500 transition-all gap-3 shadow-xs">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-lg {{ $iconBg }} flex items-center justify-center flex-shrink-0">
                                        @if($ext === 'pdf')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        @elseif(in_array($ext, ['doc', 'docx']))
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @elseif(in_array($ext, ['xls', 'xlsx']))
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="truncate">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-charcoal truncate" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</p>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Terunggah
                                            </span>
                                        </div>
                                        <p class="text-xs text-charcoal-muted mt-0.5">{{ $attachment->file_size_formatted }} &bull; Diunggah {{ $attachment->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-center">
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
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-charcoal-muted">Belum ada file yang diunggah.</p>
                    @endif
                </div>
                <div>
                    <h4 class="font-semibold text-charcoal mb-3 text-sm">Status Pengesahan:</h4>
                    @if(in_array($document->status, ['draft', 'revised', 'pending_kspi']))
                        @if(Auth::user()->hasRole(['kspi', 'admin']))
                            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                                <form action="{{ route('approvals.approve_direct', $document->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-primary text-sm w-full shadow-sm" onclick="return confirm('Sahkan dokumen ini secara langsung?')">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Sahkan Dokumen
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 flex flex-col items-center text-center">
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
                                <p class="text-xs text-emerald-700 mt-0.5">{{ $document->approved_at ? $document->approved_at->format('d/m/Y H:i') : '-' }}</p>
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
