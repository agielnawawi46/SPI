@extends('layouts.app')
@section('title', 'Detail Tindak Lanjut')
@section('page_title', 'Detail Tindak Lanjut')

@section('content')
<div class="space-y-6">

    {{-- TL Info Card --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Detail Tindak Lanjut</h2>
            <span class="badge badge-{{ $followUp->status === 'open' ? 'open' : ($followUp->status === 'in_progress' ? 'in-progress' : ($followUp->status === 'closed' ? 'completed' : 'overdue')) }}">
                {{ ucfirst(str_replace('_', ' ', $followUp->status)) }}
            </span>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-charcoal-muted uppercase tracking-wide font-semibold mb-1">Temuan</p>
                <p class="text-sm">{{ $followUp->finding }}</p>
            </div>
            <div>
                <p class="text-xs text-charcoal-muted uppercase tracking-wide font-semibold mb-1">Rekomendasi</p>
                <p class="text-sm">{{ $followUp->recommendation }}</p>
            </div>
            <div>
                <p class="text-xs text-charcoal-muted uppercase tracking-wide font-semibold mb-1">Jatuh Tempo</p>
                <p class="text-sm {{ $followUp->status === 'overdue' ? 'text-red-600 font-semibold' : '' }}">
                    {{ \Carbon\Carbon::parse($followUp->due_date)->format('d MMMM Y') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-charcoal-muted uppercase tracking-wide font-semibold mb-1">Ditugaskan Kepada</p>
                <p class="text-sm">{{ $followUp->assignee?->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Riwayat Persetujuan Bukti (Progress) --}}
    @if($followUp->evidences->count() > 0 && $followUp->approval_status !== 'pending')
    <div class="card mb-4">
        <div class="card-body py-4 px-4 overflow-x-auto">
            <h3 class="text-sm font-semibold text-charcoal mb-4">Riwayat Persetujuan Bukti</h3>
            @php
                $levels = [
                    1 => ['role' => 'Asman Auditee', 'status' => 'waiting'],
                    2 => ['role' => 'Manajer Auditee', 'status' => 'waiting'],
                    3 => ['role' => 'Auditor', 'status' => 'waiting'],
                    4 => ['role' => 'KSPI', 'status' => 'waiting'],
                ];
                
                if ($followUp->approval_status === 'pending_asman') {
                    $levels[1]['status'] = 'pending';
                } elseif ($followUp->approval_status === 'pending_manager') {
                    $levels[1]['status'] = 'approved';
                    $levels[2]['status'] = 'pending';
                } elseif ($followUp->approval_status === 'pending_auditor') {
                    $levels[1]['status'] = 'approved';
                    $levels[2]['status'] = 'approved';
                    $levels[3]['status'] = 'pending';
                } elseif ($followUp->approval_status === 'pending_kspi') {
                    $levels[1]['status'] = 'approved';
                    $levels[2]['status'] = 'approved';
                    $levels[3]['status'] = 'approved';
                    $levels[4]['status'] = 'pending';
                } elseif (in_array($followUp->approval_status, ['approved', 'closed']) || $followUp->status === 'closed') {
                    $levels[1]['status'] = 'approved';
                    $levels[2]['status'] = 'approved';
                    $levels[3]['status'] = 'approved';
                    $levels[4]['status'] = 'approved';
                } elseif ($followUp->approval_status === 'rejected') {
                    // Jika ditolak, set semua previous approved ke approved, dan level berjalan ke rejected
                    // Agak kompleks, tapi kita tandai saja yang aktif
                    $lastApproval = $followUp->approvals->last();
                    if ($lastApproval) {
                        $levels[$lastApproval->approval_level]['status'] = 'rejected';
                    } else {
                        $levels[1]['status'] = 'rejected';
                    }
                }
            @endphp
            <div class="flex items-start w-full min-w-[400px]">
                @foreach($levels as $levelNum => $levelData)
                <div class="relative flex-1 text-center group px-1">
                    {{-- Connector Line --}}
                    @if(!$loop->last)
                    <div class="absolute top-3.5 left-[50%] w-full h-0.5 {{ $levelData['status'] === 'approved' ? 'bg-green-400' : 'bg-surface-200' }} z-0 mt-[1px]"></div>
                    @endif

                    {{-- Step Icon --}}
                    <div class="relative z-10 mx-auto w-7 h-7 flex items-center justify-center rounded-full border-2 border-white shadow-sm {{ 
                        $levelData['status'] === 'approved' ? 'bg-green-100 text-green-600' : 
                        ($levelData['status'] === 'rejected' ? 'bg-red-100 text-red-600' : 
                        ($levelData['status'] === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-400')) 
                    }}">
                        @if($levelData['status'] === 'approved')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @elseif($levelData['status'] === 'rejected')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>

                    {{-- Step Info --}}
                    <div class="mt-2">
                        <h4 class="text-[11px] font-semibold text-charcoal leading-tight">
                            Level {{ $levelNum }} - <span class="capitalize">{{ $levelData['role'] }}</span>
                        </h4>
                        <p class="text-[10px] font-bold mt-1 leading-none {{ 
                            $levelData['status'] === 'approved' ? 'text-green-600' : 
                            ($levelData['status'] === 'rejected' ? 'text-red-600' : 
                            ($levelData['status'] === 'pending' ? 'text-amber-600' : 'text-gray-400')) 
                        }}">
                            {{ $levelData['status'] === 'approved' ? 'Disetujui' : ($levelData['status'] === 'rejected' ? 'Revisi' : ($levelData['status'] === 'pending' ? 'Menunggu' : '-')) }}
                        </p>
                        @php
                            $approvalRecord = $followUp->approvals->where('approval_level', $levelNum)->last();
                        @endphp
                        @if($approvalRecord && in_array($levelData['status'], ['approved', 'rejected']))
                            <p class="text-[9px] text-gray-500 mt-1">{{ \Carbon\Carbon::parse($approvalRecord->acted_at)->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Approval Panel --}}
    @php
        $user = Auth::user();
        $canApprove = false;
        $approvalRoleText = '';
        $canUploadEvidence = false;

        if ($user->hasRole(['staff', 'admin']) && in_array($followUp->approval_status, ['pending', 'rejected'])) {
            $canUploadEvidence = true;
        }

        if ($followUp->approval_status === 'pending_asman' && $user->hasRole(['asman', 'manager'])) {
            $canApprove = true;
            $canUploadEvidence = true;
            $approvalRoleText = $user->hasRole('manager') ? 'Manajer Auditee (Bypass)' : 'Assistant Manager';
        } elseif ($followUp->approval_status === 'pending_manager' && $user->hasRole('manager')) {
            $canApprove = true;
            $canUploadEvidence = true;
            $approvalRoleText = 'Manajer Auditee';
        } elseif ($followUp->approval_status === 'pending_auditor' && $user->hasRole('auditor')) {
            $canApprove = true;
            $canUploadEvidence = true;
            $approvalRoleText = 'Auditor';
        } elseif ($followUp->approval_status === 'pending_kspi' && $user->hasRole('kspi')) {
            $canApprove = true;
            $canUploadEvidence = true;
            $approvalRoleText = 'KSPI';
        }
    @endphp

    @if($canApprove)
    <div class="card border-l-4 border-l-blue-400">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Persetujuan ({{ $approvalRoleText }})</h3>
            <p class="text-sm text-charcoal-muted mb-4">Silakan review bukti yang telah diunggah. Apakah bukti tindak lanjut ini sudah memadai?</p>
            
            <div class="flex gap-4 items-start">
                <form action="{{ route('auditee.approve-evidence', $followUp) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="btn-primary text-sm flex items-center justify-center gap-2" onclick="return confirm('Apakah Anda yakin menyetujui bukti ini?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui Bukti
                    </button>
                </form>

            </div>
        </div>
    </div>
    @endif

    {{-- Status Alert for Auditee --}}
    @if(in_array($followUp->approval_status, ['pending_manager', 'pending_auditor', 'pending_kspi']))
        <div class="alert alert-info">
            Bukti tindak lanjut telah diunggah dan sedang dalam tahap review ({{ str_replace('_', ' ', $followUp->approval_status) }}).
        </div>
    @endif
    @if($followUp->approval_status === 'rejected')
        <div class="alert alert-error">
            <p class="font-bold">Bukti ditolak!</p>
            <p class="text-sm mt-1">Catatan: {{ $followUp->notes }}</p>
        </div>
    @endif

    {{-- Upload Bukti --}}
    @if($canUploadEvidence)
    <div class="card mb-8">
        <div class="card-header"><h2 class="card-title">{{ $followUp->evidences->count() > 0 ? 'Perbarui Bukti Tindak Lanjut' : 'Upload Bukti Tindak Lanjut' }}</h2></div>
        <div class="card-body">
            @if($followUp->evidences->count() > 0)
                <div class="alert alert-warning mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <p class="text-sm text-yellow-700"><strong>Catatan:</strong> Mengunggah file baru di sini akan <strong>menimpa</strong> bukti yang sudah Anda unggah sebelumnya.</p>
                </div>
            @endif
            <form action="{{ route('auditee.upload-evidence', $followUp) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">File Bukti <span class="text-red-500">*</span></label>
                    <input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                    <p class="text-xs text-charcoal-muted mt-1">Format: PDF, Word, Excel, Gambar.</p>
                    @error('file')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Bukti</label>
                    <textarea name="description" class="form-textarea" rows="2" placeholder="Jelaskan bukti yang diunggah..."></textarea>
                </div>
                <button type="submit" class="btn-primary">Unggah Bukti</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Riwayat Bukti --}}
    @if($followUp->evidences->count() > 0)
    <div class="card border-l-4 {{ $followUp->status === 'completed' || $followUp->status === 'closed' ? 'border-l-green-500' : 'border-l-amber-400' }} mt-6">
        <div class="card-header bg-surface-50 border-b border-surface-200 flex justify-between items-center">
            <h3 class="card-title text-charcoal flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Dokumen Utama & Pengesahan
            </h3>
            <span class="badge {{ $followUp->status_color }}">{{ $followUp->status_label }}</span>
        </div>
        <div class="card-body p-6">
            <h4 class="font-semibold text-charcoal mb-3 text-sm">File Dokumen yang Diunggah:</h4>
            @foreach($followUp->evidences as $evidence)
            <div class="p-4 border border-surface-200 rounded-lg bg-white shadow-sm mb-4 last:mb-0">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="p-2 bg-primary-50 rounded-lg text-primary-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-charcoal truncate" title="{{ basename($evidence->file_path) }}">{{ basename($evidence->file_path) }}</p>
                            <p class="text-xs text-charcoal-muted mt-0.5">{{ $evidence->file_size_formatted }} • Diunggah oleh {{ $evidence->uploader?->name ?? '-' }} pada {{ $evidence->created_at->format('d/m/Y H:i') }}</p>
                            @if($evidence->description)
                                <p class="text-xs text-charcoal-muted mt-0.5">Catatan: {{ $evidence->description }}</p>
                            @endif
                            @if($evidence->updated_by)
                                <p class="text-xs text-charcoal-muted mt-0.5">Diubah oleh {{ $evidence->updater?->name }} pada {{ $evidence->updated_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0 items-center">
                        @php
                            $ext = strtolower(pathinfo($evidence->file_name ?? basename($evidence->file_path), PATHINFO_EXTENSION));
                        @endphp
                        @if($evidence->pdf_path)
                            <a href="{{ asset('storage/' . $evidence->pdf_path) }}" target="_blank" class="btn-primary btn-sm flex items-center justify-center gap-1.5 flex-shrink-0 shadow-sm py-1.5 px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Preview
                            </a>
                        @elseif(in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']))
                            <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn-primary btn-sm flex items-center justify-center gap-1.5 flex-shrink-0 shadow-sm py-1.5 px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Preview
                            </a>
                        @endif
                        <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn-secondary text-sm py-1.5 px-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh
                        </a>
                        @if(Auth::user()->hasRole(['staff', 'admin']) && in_array($followUp->approval_status, ['pending', 'rejected']))
                        <form action="{{ route('auditee.delete-evidence', $evidence) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file draft ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700 btn-sm flex items-center justify-center gap-1.5 py-1.5 px-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Submit Evidence Button --}}
            @if(Auth::user()->hasRole(['staff', 'admin']) && in_array($followUp->approval_status, ['pending', 'rejected']) && $followUp->evidences->count() > 0)
                <div class="mt-6 pt-4 border-t border-surface-200">
                    <form action="{{ route('auditee.submit-evidence', $followUp) }}" method="POST">
                        @csrf
                        <div class="alert alert-warning mb-4">
                            <strong>Perhatian:</strong> Pastikan semua file bukti (draft) sudah Anda unggah. Setelah Anda klik "Kirim Tindak Lanjut" di bawah ini, bukti akan diteruskan ke Asman/Manajer untuk disetujui dan Anda tidak dapat mengubah file lagi.
                        </div>
                        <button type="submit" class="btn-primary w-full md:w-auto bg-blue-600 hover:bg-blue-700 border-none" onclick="return confirm('Kirim tindak lanjut ini untuk disetujui atasan Anda?')">
                            <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Kirim Tindak Lanjut
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
    @endif

    <div class="flex justify-start">
        <a href="{{ Auth::user()->hasRole(['manager', 'asman', 'staff']) ? route('auditee.index') : route('other-activities.tl.index') }}" class="btn-secondary">← Kembali</a>
    </div>

</div>
@endsection
