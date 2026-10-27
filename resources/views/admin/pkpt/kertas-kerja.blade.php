@extends('layouts.app')

@section('title', 'Kertas Kerja PKPT')
@section('page_title', 'Kertas Kerja PKPT')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.kertas-kerja.index') }}" class="breadcrumb-link">Kertas Kerja PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">{{ $pkpt->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    <div class="card">
        <div class="card-header bg-primary-50 border-b border-primary-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="card-title text-primary-800">Kertas Kerja &bull; {{ $pkpt->title }}</h2>
                <div class="flex flex-wrap items-center gap-3 text-xs text-primary-700 mt-1.5">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Tahun: <strong>{{ $pkpt->period_year }}</strong>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        Status: 
                        <span class="badge {{ $document && $document->status === 'draft' ? 'badge-draft' : 'badge-approved' }}">
                            {{ $document && $document->status === 'draft' ? 'Draft' : 'Closed' }}
                        </span>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1">
                        Total Terunggah: <strong>{{ $document && $document->attachments ? $document->attachments->count() : 0 }} Berkas</strong>
                    </span>
                </div>
            </div>
            <a href="{{ route('pkpt.kertas-kerja.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>

        <div class="card-body">
            {{-- Instruction Banner --}}
            <div class="p-4 rounded-xl bg-surface-50 border border-surface-200 mb-6 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-primary-100 text-primary-700 flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-xs sm:text-sm text-charcoal">
                    <p class="font-semibold text-charcoal mb-0.5">Kelola Dokumen Kertas Kerja PKPT</p>
                    <p class="text-charcoal-muted leading-relaxed">
                        Anda dapat mengunggah <strong>satu atau lebih berkas</strong> pada setiap kategori dokumen di bawah ini. File baru akan ditambahkan tanpa menghapus berkas yang telah ada. Format didukung: PDF, Word (DOC/DOCX), dan Excel (XLS/XLSX).
                    </p>
                </div>
            </div>

            @php
                $categories = [
                    'RJP' => 'Rencana Jangka Panjang',
                    'RKAP' => 'Rencana Kerja & Anggaran Perusahaan',
                    'Profil Risiko' => 'Dokumen Profil Risiko',
                    'Arahan PS' => 'Arahan Pemegang Saham',
                    'Arahan Dekom' => 'Arahan Dewan Komisaris',
                    'Arahan Direktur' => 'Arahan Direktur'
                ];
                
                // Group uploaded files by category
                $uploadedFiles = [];
                foreach ($categories as $catKey => $label) {
                    $uploadedFiles[$catKey] = [];
                }
                if ($document && $document->attachments) {
                    foreach ($document->attachments as $att) {
                        $uploadedFiles[$att->description][] = $att;
                    }
                }
            @endphp

            {{-- 6 Category Cards Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($categories as $key => $label)
                    @php
                        $files = $uploadedFiles[$key] ?? [];
                        $hasFiles = count($files) > 0;
                    @endphp

                    <div class="border rounded-2xl transition-all duration-200 overflow-hidden flex flex-col justify-between {{ $hasFiles ? 'border-emerald-200 bg-white shadow-sm hover:shadow-md' : 'border-surface-200 bg-white shadow-sm' }}">
                        
                        {{-- Card Header --}}
                        <div class="p-4 sm:p-5 border-b {{ $hasFiles ? 'border-emerald-100 bg-emerald-50/40' : 'border-surface-100 bg-surface-50/50' }}">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold flex-shrink-0 {{ $hasFiles ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-200 text-charcoal' }}">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-charcoal text-base tracking-tight">{{ $key }}</h4>
                                        <p class="text-xs text-charcoal-muted mt-0.5">{{ $label }}</p>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    @if($hasFiles)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            {{ count($files) }} Berkas Terunggah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-surface-100 text-charcoal-muted border border-surface-200">
                                            Belum Ada Berkas
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Card Body: List of Uploaded Files --}}
                        @if($hasFiles)
                        <div class="p-4 sm:p-5 flex-1 flex flex-col border-b border-surface-100">
                            <div class="space-y-2.5 flex-1">
                                    <div class="flex items-center justify-between text-xs text-charcoal-muted font-medium pb-1 border-b border-surface-100">
                                        <span>Berkas Terlampir ({{ count($files) }})</span>
                                        <span>Aksi</span>
                                    </div>
                                    
                                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                        @foreach($files as $att)
                                            @php
                                                $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION));
                                                $iconStyle = match($ext) {
                                                    'pdf' => 'text-red-500 bg-red-50 border-red-200',
                                                    'doc', 'docx' => 'text-blue-500 bg-blue-50 border-blue-200',
                                                    'xls', 'xlsx' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                                                    default => 'text-charcoal bg-surface-100 border-surface-200'
                                                };
                                                $badgeStyle = match($ext) {
                                                    'pdf' => 'bg-red-50 text-red-700 border-red-200',
                                                    'doc', 'docx' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'xls', 'xlsx' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    default => 'bg-surface-100 text-charcoal border-surface-200'
                                                };
                                            @endphp

                                            <div class="p-2.5 bg-surface-50/70 hover:bg-surface-100/80 border border-surface-200 rounded-xl flex items-center justify-between gap-3 transition-colors">
                                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                    {{-- File Type Icon --}}
                                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 border {{ $iconStyle }}">
                                                        @if($ext === 'pdf')
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                        @elseif(in_array($ext, ['xls', 'xlsx']))
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        @else
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        @endif
                                                    </div>

                                                    {{-- File Info --}}
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs font-semibold text-charcoal truncate" title="{{ $att->file_name }}">
                                                            {{ $att->file_name }}
                                                        </p>
                                                        <div class="flex items-center gap-1.5 text-[10.5px] text-charcoal-muted mt-0.5">
                                                            <span class="font-medium uppercase px-1 py-0.2 rounded border {{ $badgeStyle }}">{{ $ext }}</span>
                                                            @if($att->file_size)
                                                                <span>&bull; {{ $att->file_size_formatted }}</span>
                                                            @endif
                                                            <span>&bull; {{ $att->created_at->format('d/m/Y H:i') }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Action Buttons --}}
                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    {{-- View / Preview --}}
                                                    <a href="{{ Storage::url($att->pdf_path ?: $att->file_path) }}" 
                                                       target="_blank" 
                                                       title="Lihat Berkas" 
                                                       class="text-primary-600 hover:text-primary-800 bg-white hover:bg-primary-50 border border-surface-200 hover:border-primary-200 rounded-lg p-1.5 transition-colors shadow-2xs">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </a>
                                                    
                                                    {{-- Download --}}
                                                    <a href="{{ Storage::url($att->file_path) }}" 
                                                       download 
                                                       target="_blank" 
                                                       title="Unduh Berkas" 
                                                       class="text-charcoal hover:text-charcoal-dark bg-white hover:bg-surface-100 border border-surface-200 rounded-lg p-1.5 transition-colors shadow-2xs">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                    </a>
                                                    
                                                    {{-- Delete (Draft Only) --}}
                                                    @if($document && $document->status === 'draft')
                                                    <form action="{{ route('pkpt.destroy-kertas-kerja-attachment', [$pkpt, $att->id]) }}" 
                                                          method="POST" 
                                                          class="inline-block m-0 p-0 leading-none" 
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus file \'{{ addslashes($att->file_name) }}\'?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                title="Hapus Berkas" 
                                                                class="text-red-600 hover:text-red-800 bg-white hover:bg-red-50 border border-surface-200 hover:border-red-200 rounded-lg p-1.5 transition-colors shadow-2xs">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Card Footer: Upload Form (Only in Draft Status) --}}
                        @if($document && $document->status === 'draft')
                            <div class="p-4 sm:p-5 border-t border-surface-100 bg-surface-50/60">
                                <form action="{{ route('pkpt.store-kertas-kerja', $pkpt) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-semibold text-charcoal flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            Tambah Berkas {{ $key }}
                                        </label>
                                        <span class="text-[10.5px] text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md font-medium border border-primary-100">
                                            Bisa multi-file
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <input type="file" 
                                               name="attachments[{{ $key }}][]" 
                                               multiple
                                               class="form-input flex-1 text-xs bg-white h-[38px] cursor-pointer file:mr-2.5 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx" 
                                               required>
                                        <button type="submit" class="btn-primary text-xs py-2 px-3.5 h-[38px] flex-shrink-0 flex items-center gap-1.5 shadow-sm font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            Unggah
                                        </button>
                                    </div>
                                    <p class="text-[10.5px] text-charcoal-muted mt-1.5">Format: PDF, DOC, DOCX, XLS, XLSX.</p>
                                </form>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            {{-- Finish / Submit Section --}}
            @if($document && $document->status === 'draft')
                <div class="mt-8 flex justify-end pt-6 border-t border-surface-200">
                    <form action="{{ route('approvals.submit', ['type' => 'document', 'id' => $document->id]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin sudah selesai mengunggah semua file Kertas Kerja? Setelah diklik selesai, dokumen akan dikunci (Closed).')">
                        @csrf
                        <button type="submit" class="btn-primary flex items-center gap-2 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Selesai & Kunci Kertas Kerja (Closed)
                        </button>
                    </form>
                </div>
            @elseif($document && $document->status !== 'draft')
                <div class="mt-8 pt-6 border-t border-surface-200">
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm">Kertas Kerja PKPT Telah Selesai (Closed)</p>
                            <p class="text-xs text-emerald-700 mt-0.5">Seluruh berkas dokumen Kertas Kerja pada PKPT ini telah dikunci dan disimpan secara permanen.</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
