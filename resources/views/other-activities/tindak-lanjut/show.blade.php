@extends('layouts.app')
@section('title', 'Detail Monitoring Tindak Lanjut')
@section('page_title', 'Detail Dokumen')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.tl.index') }}" class="breadcrumb-link">Monitoring Tindak Lanjut</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $document->title }}</span>
@endsection

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    {{-- Header Info --}}
    <div class="card">
        <div class="card-header flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h2 class="card-title text-xl font-bold text-charcoal">{{ $document->title }}</h2>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-charcoal-muted mt-2">
                    <span>Dibuat oleh <strong class="text-charcoal">{{ $document->creator->name ?? '-' }}</strong> pada {{ $document->created_at->format('d F Y') }}</span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-shrink-0">
                <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
                
                <div class="flex items-center gap-2">
                    @if($document->status === 'approved')
                        <a href="{{ route('other-activities.tl.pdf', $document) }}" class="btn-primary text-sm bg-red-600 hover:bg-red-700 border-red-600 hover:border-red-700 text-white flex items-center shadow-sm" target="_blank">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export PDF
                        </a>
                    @endif
                    @if($document->canBeEditedBy(Auth::user()))
                        <a href="{{ route('other-activities.tl.edit', $document) }}" class="btn-secondary text-sm flex items-center shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    @endif
                    <a href="{{ route('other-activities.tl.index') }}" class="btn-secondary text-sm flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

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

    {{-- Approval Action Panel --}}
    @php
        $user = Auth::user();
        $pendingKspi = $document->approvals->where('approval_level', 1)->where('action', null)->first();
    @endphp

    {{-- Auditor: Submit to KSPI --}}
    @if(in_array($document->status, ['draft','revised']) && ($document->created_by === $user->id || $user->hasRole('admin')))
    <div class="card border-l-4 border-l-amber-400">
        <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-charcoal">Approve & Kirim untuk Direview</h3>
                <p class="text-sm text-charcoal-muted mt-1">Selesaikan dokumen dan setujui (generate barcode Auditor) lalu kirim ke KSPI untuk direview.</p>
            </div>
            <form action="{{ route('approvals.submit', ['type' => 'document', 'id' => $document->id]) }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="btn-primary text-sm flex items-center gap-2 shadow-sm"
                    onclick="return confirm('Apakah Anda yakin menyetujui dokumen ini dan mengirimkannya ke KSPI?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Approve & Kirim ke KSPI
                </button>
            </form>
        </div>
    </div>
    @endif



    {{-- KSPI Approval --}}
    @if($document->status === 'pending_kspi' && ($user->hasRole('kspi') || $user->hasRole('admin')) && $pendingKspi)
    <div class="card border-l-4 border-l-blue-400">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Review KSPI</h3>
            <p class="text-sm text-charcoal-muted mb-4">Dokumen ini menunggu persetujuan Anda sebagai KSPI. Jika Anda menemukan kesalahan, Anda dapat langsung mengubahnya (Edit) sebelum menyetujui.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <form action="{{ route('approvals.action', $pendingKspi) }}" method="POST" class="flex-1">
                    @csrf
                    <textarea name="comments" class="form-textarea w-full mb-2 text-sm" rows="2" placeholder="Catatan (opsional)"></textarea>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" name="action" value="approve" class="btn-primary text-sm flex-1 flex items-center justify-center gap-2"
                            onclick="return confirm('Setujui dokumen ini? Barcode Anda akan digenerate setelah disetujui.')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approve & Generate Barcode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Daftar Tindak Lanjut (Muncul Setelah Disetujui KSPI) --}}
    @if($document->status === 'approved' && $document->followUps()->count() > 0)
    <div class="card mb-6 border-l-4 border-l-primary-500">
        <div class="card-header bg-primary-50">
            <h3 class="card-title text-primary-800">Daftar Penugasan Tindak Lanjut</h3>
            <p class="text-xs text-primary-600 mt-1">Daftar tugas unggah bukti yang diteruskan ke masing-masing PIC/Departemen Auditee.</p>
        </div>
        <div class="card-body p-0">
            <div class="hidden md:block overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">No</th>
                            <th>Temuan (LHA)</th>
                            <th>PIC / Email</th>
                            <th>Jatuh Tempo</th>
                            <th>Status Persetujuan Bukti</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($document->followUps as $index => $followUp)
                            @php
                                // Restrict row visibility based on department for auditees
                                $user = Auth::user();
                                $canView = true;
                                if ($user->hasRole(['manager', 'asman', 'staff'])) {
                                    $deptId = $user->department_id ?? 1;
                                    if ($followUp->auditee_department_id !== $deptId && $followUp->assigned_to !== $user->id) {
                                        $canView = false;
                                    }
                                }
                            @endphp
                            @if($canView)
                            <tr>
                                <td class="text-center text-charcoal-muted">{{ $index + 1 }}</td>
                                <td class="max-w-xs truncate" title="{{ $followUp->finding }}">{{ Str::limit($followUp->finding, 50) }}</td>
                                <td>
                                    <div class="font-medium text-charcoal">{{ $followUp->pic_name ?? '-' }}</div>
                                    <div class="text-xs text-charcoal-muted">{{ $followUp->pic_email ?? '-' }}</div>
                                </td>
                                <td class="{{ $followUp->status === 'overdue' ? 'text-red-600 font-medium' : 'text-charcoal-muted' }}">
                                    {{ \Carbon\Carbon::parse($followUp->due_date)->format('d M Y') }}
                                </td>
                                <td>
                                    @if($followUp->approval_status === 'pending' && $followUp->evidences_count > 0)
                                        <span class="badge badge-in-progress">Draf (Belum Dikirim)</span>
                                    @elseif($followUp->status === 'closed' || $followUp->approval_status === 'approved')
                                        <span class="badge badge-approved">Closed</span>
                                    @else
                                        <span class="badge {{ $followUp->approval_status === 'rejected' ? 'badge-rejected' : 'badge-pending' }}">
                                            {{ str_replace('_', ' ', $followUp->approval_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('auditee.show', $followUp) }}" class="btn-secondary btn-sm">Detail & Unggah</a>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden space-y-4 p-4">
                @foreach($document->followUps as $index => $followUp)
                    @php
                        $user = Auth::user();
                        $canView = true;
                        if ($user->hasRole(['manager', 'asman', 'staff'])) {
                            $deptId = $user->department_id ?? 1;
                            if ($followUp->auditee_department_id !== $deptId && $followUp->assigned_to !== $user->id) {
                                $canView = false;
                            }
                        }
                    @endphp
                    @if($canView)
                    <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                        <div class="p-4">
                            <div class="flex justify-between items-start gap-4 mb-3">
                                <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                                    {{ $followUp->finding }}
                                </h3>
                                <span class="flex-shrink-0">
                                    @if($followUp->approval_status === 'pending' && $followUp->evidences_count > 0)
                                        <span class="badge badge-in-progress">Draf</span>
                                    @elseif($followUp->status === 'closed' || $followUp->approval_status === 'approved')
                                        <span class="badge badge-approved">Closed</span>
                                    @else
                                        <span class="badge {{ $followUp->approval_status === 'rejected' ? 'badge-rejected' : 'badge-pending' }}">
                                            {{ str_replace('_', ' ', $followUp->approval_status) }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                                    <span class="text-charcoal-muted">PIC</span>
                                    <span class="font-medium text-charcoal text-right">{{ $followUp->pic_name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-charcoal-muted">Jatuh Tempo</span>
                                    <span class="font-medium {{ $followUp->status === 'overdue' ? 'text-red-600 font-medium' : 'text-charcoal' }}">{{ \Carbon\Carbon::parse($followUp->due_date)->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-surface-50 px-4 py-3 flex items-center justify-end border-t border-surface-200">
                            <a href="{{ route('auditee.show', $followUp) }}" class="btn-secondary btn-sm">Detail & Unggah</a>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- A4 Paper Container Landscape -->
    <div class="bg-white shadow-xl mx-auto p-10 max-w-[297mm] min-h-[210mm] mb-8 text-black text-[10pt] flex flex-col leading-[1.15] overflow-x-auto" style="font-family: Arial, sans-serif;">
        
        <table class="w-full mb-6 border-none" style="table-layout: fixed;">
            <tr>
                <td class="w-1/2 align-top border-none">
                        <img src="{{ asset('pln.jpg') }}" alt="Logo" class="max-h-16 object-contain">
                </td>
                <td class="w-1/2 align-top text-right text-[8pt] border-none text-[#002060]">
                    <table class="w-auto float-right text-left">
                        <tr>
                            <td class="py-0.5">No. Formulir</td><td class="px-2 py-0.5">:</td><td class="py-0.5">{{ $document->nomor_formulir ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5">Tanggal</td><td class="px-2 py-0.5">:</td><td class="py-0.5">{{ $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5">Halaman</td><td class="px-2 py-0.5">:</td><td class="py-0.5">1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="text-center mb-6">
            <div class="font-bold whitespace-pre-line uppercase text-[10pt] leading-snug">{{ $document->header_text ?? "PT PELAYANAN ENERGI BATAM" }}</div>
            <div class="font-bold whitespace-pre-line uppercase text-[10pt] leading-snug">{{ $document->judul_dokumen ?? "MONITORING DAN EVALUASI TINDAK LANJUT" }}</div>
            @if($document->periode_audit)
                <div class="font-bold whitespace-pre-line uppercase text-[10pt] leading-snug">BERLAKU SAMPAI DENGAN: {{ \Carbon\Carbon::parse($document->periode_audit)->translatedFormat('d F Y') }}</div>
            @endif
            <div class="font-bold text-[10pt] mt-1">Nomor : {{ preg_replace('/^Nomor\s*:\s*/i', '', $document->title) }}</div>
        </div>

        @if($document->deskripsi_pembuka)
            <div class="font-bold text-[9pt] mb-2 text-left">{{ $document->deskripsi_pembuka }}</div>
        @endif

        @php
            $items = json_decode($document->content, true) ?? [];
        @endphp

        @if(count($items) > 0)
            <div class="w-full mb-4">
                <table class="w-full border-collapse border border-black text-[8pt]" style="table-layout: fixed; word-wrap: break-word;">
                    <thead>
                        <tr>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[3%]" style="background-color: #d9d9d9;">No</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[11%]" style="background-color: #d9d9d9;">LHA</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[11%]" style="background-color: #d9d9d9;">Sebab</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[11%]" style="background-color: #d9d9d9;">Akibat</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[11%]" style="background-color: #d9d9d9;">Rekomendasi</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[12%]" style="background-color: #d9d9d9;">PIC TL & Email</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[10%]" style="background-color: #d9d9d9;">Evidence TL</th>
                            <th colspan="3" class="border border-black p-1 text-center align-middle font-bold w-[17%]" style="background-color: #d9d9d9;">Status</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[9%]" style="background-color: #d9d9d9;">Evaluasi TL</th>
                            <th rowspan="2" class="border border-black p-1 text-center align-middle font-bold w-[9%]" style="background-color: #d9d9d9;">Tanggal Jatuh Tempo</th>
                        </tr>
                        <tr>
                            <th class="border border-black p-1 text-center align-middle font-bold w-[5%]" style="background-color: #d9d9d9;">Closed</th>
                            <th class="border border-black p-1 text-center align-middle font-bold w-[7%]" style="background-color: #d9d9d9;">On<br>Progress</th>
                            <th class="border border-black p-1 text-center align-middle font-bold w-[5%]" style="background-color: #d9d9d9;">TDD</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $countClosed = 0;
                            $countOnProgress = 0;
                            $countTdd = 0;
                        @endphp
                        @foreach($items as $groupIndex => $group)
                            @foreach($group['rows'] as $rowIndex => $item)
                                @php
                                    if(!empty(trim($item['status_closed'] ?? ''))) $countClosed++;
                                    if(!empty(trim($item['status_on_progress'] ?? ''))) $countOnProgress++;
                                    if(!empty(trim($item['status_tdd'] ?? ''))) $countTdd++;
                                @endphp
                                <tr class="align-top bg-white">
                                    <td class="border border-black p-1 text-center">{{ $rowIndex === 0 ? ($groupIndex + 1) . '.' : '' }}</td>
                                    <td class="border border-black p-1 text-center break-words whitespace-pre-wrap">{{ $item['lha'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center break-words whitespace-pre-wrap">{{ $item['sebab'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center break-words whitespace-pre-wrap">{{ $item['akibat'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center break-words whitespace-pre-wrap">{{ $item['rekomendasi'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center break-words leading-tight">
                                        <div class="font-bold">{{ $item['pic_tl'] ?? '' }}</div>
                                        <div>{{ $item['pic_email'] ?? '' }}</div>
                                    </td>
                                    <td class="border border-black p-1 text-center break-words whitespace-pre-wrap">{{ $item['evidence_tl'] ?? '' }}</td>
                                    
                                    <td class="border border-black p-1 text-center font-bold">{{ $item['status_closed'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center font-bold">{{ $item['status_on_progress'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center font-bold">{{ $item['status_tdd'] ?? '' }}</td>
                                    
                                    <td class="border border-black p-1 text-center break-words whitespace-pre-wrap">{{ $item['evaluasi_tl'] ?? '' }}</td>
                                    <td class="border border-black p-1 text-center break-words">{{ !empty($item['jatuh_tempo']) ? \Carbon\Carbon::parse($item['jatuh_tempo'])->format('d M Y') : '' }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                @for ($i = 0; $i < 12; $i++)
                                    <td class="border border-black" style="background-color: #ffc000; height: 16px;"></td>
                                @endfor
                            </tr>
                        @endforeach
                        <tr>
                            <td class="border border-black" style="background-color: #b4c6e7;"></td>
                            <td class="border border-black p-2 font-bold text-left pl-3" style="background-color: #b4c6e7;">JUMLAH</td>
                            @for ($i = 0; $i < 5; $i++)
                                <td class="border border-black" style="background-color: #b4c6e7;"></td>
                            @endfor
                            <td class="border border-black p-2 font-bold text-center" style="background-color: #b4c6e7;">{{ $countClosed ?: '' }}</td>
                            <td class="border border-black p-2 font-bold text-center" style="background-color: #b4c6e7;">{{ $countOnProgress ?: '' }}</td>
                            <td class="border border-black p-2 font-bold text-center" style="background-color: #b4c6e7;">{{ $countTdd ?: '' }}</td>
                            <td class="border border-black" style="background-color: #b4c6e7;"></td>
                            <td class="border border-black" style="background-color: #b4c6e7;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-500 flex-grow py-10">Belum ada temuan.</p>
        @endif

        {{-- Barcodes Section --}}
        <div class="mt-4 flex justify-between w-full">
            <div class="w-1/3 text-center">
                @if($document->qr_code_path_auditor)
                    <p class="font-bold mb-2 text-xs uppercase">{{ $document->bidang_tugas ?? ($document->creator->department->name ?? 'AUDITOR') }}</p>
                    <img src="{{ asset('storage/' . $document->qr_code_path_auditor) }}" alt="QR Auditor" class="w-24 h-24 mx-auto object-contain">
                    <p class="mt-2 text-xs font-bold underline uppercase">{{ $document->disusun_oleh ?? ($document->creator->name ?? '-') }}</p>
                @else
                    <p class="font-bold mb-2 text-xs uppercase">{{ $document->bidang_tugas ?? ($document->creator->department->name ?? 'AUDITOR') }}</p>
                    <div class="w-24 h-24 mx-auto"></div>
                    <p class="mt-2 text-xs font-bold underline uppercase">{{ $document->disusun_oleh ?? ($document->creator->name ?? '-') }}</p>
                @endif
            </div>
            
            <div class="w-1/3 text-center">
                @if($document->status === 'approved' && $document->qr_code_path)
                    <p class="font-bold mb-2 text-xs uppercase">{{ $document->nama_jabatan ?? 'KEPALA SPI' }}</p>
                    <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR KSPI" class="w-24 h-24 mx-auto object-contain">
                    <p class="mt-2 text-xs font-bold underline uppercase">{{ $document->nama_kspi ?? (\App\Models\User::role('kspi')->first()?->name ?? 'KSPI') }}</p>
                @else
                    <p class="font-bold mb-2 text-xs uppercase">{{ $document->nama_jabatan ?? 'KEPALA SPI' }}</p>
                    <div class="w-24 h-24 mx-auto"></div>
                    <p class="mt-2 text-xs font-bold underline uppercase">{{ $document->nama_kspi ?? (\App\Models\User::role('kspi')->first()?->name ?? 'KSPI') }}</p>
                @endif
            </div>
        </div>
        
    </div>
</div>
@endsection
