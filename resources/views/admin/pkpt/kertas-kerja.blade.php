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
        <div class="card-header bg-primary-50/70 border-b border-primary-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="card-title text-primary-800">Kertas Kerja &bull; {{ $pkpt->title }}</h2>
                <div class="flex flex-wrap items-center gap-3 text-xs text-primary-700 mt-1.5">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Tahun: <strong>{{ $pkpt->period_year }}</strong>
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1.5">
                        Status: 
                        <span class="badge {{ $document && $document->status === 'draft' ? 'badge-draft' : 'badge-completed' }}">
                            {{ $document && $document->status === 'draft' ? 'Draft' : 'Closed' }}
                        </span>
                        @if($document && $document->status !== 'draft' && auth()->user()->hasRole('admin'))
                            <form action="{{ route('pkpt.reopen-kertas-kerja', $pkpt) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci (reopen) Kertas Kerja PKPT ini kembali ke status Draft?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 transition-all shadow-2xs cursor-pointer ml-1" title="Buka Kunci Closed">
                                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                    <span>Buka Kunci</span>
                                </button>
                            </form>
                        @endif
                    </span>
                    <span>&bull;</span>
                    @php
                        $totalAttachmentsCount = $document && $document->attachments ? $document->attachments->count() : 0;
                    @endphp
                    <span class="flex items-center gap-1" x-data="{ total: {{ $totalAttachmentsCount }} }" @pkpt-attachments-updated.window="total = $event.detail.total">
                        Total Terunggah: <strong><span x-text="total">{{ $totalAttachmentsCount }}</span> Berkas</strong>
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div x-data="{ total: {{ $totalAttachmentsCount }} }" @pkpt-attachments-updated.window="total = $event.detail.total">
                    <template x-if="total > 0">
                        <a href="{{ route('pkpt.kertas-kerja.download-zip', $pkpt) }}" 
                           class="btn-secondary text-sm py-2 px-3.5 flex items-center gap-2 text-emerald-700 hover:text-emerald-800 border-emerald-300 hover:bg-emerald-50 rounded-xl transition-all shadow-2xs font-semibold" 
                           title="Unduh seluruh berkas Kertas Kerja dalam format ZIP terstruktur per folder">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh Semua (.ZIP)</span>
                        </a>
                    </template>
                </div>
                <a href="{{ route('pkpt.kertas-kerja.index') }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>
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
                        $filesData = array_values(array_map(function($att) use ($pkpt) {
                            $ext = strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION));
                            return [
                                'id' => $att->id,
                                'file_name' => $att->file_name,
                                'ext' => $ext,
                                'file_size_formatted' => $att->file_size_formatted,
                                'created_at_formatted' => $att->created_at->format('d/m/Y H:i'),
                                'view_url' => route('pkpt.view-file', [$pkpt, $att->id]),
                                'download_url' => route('pkpt.download', [$pkpt, $att->id]),
                                'delete_url' => route('pkpt.destroy-kertas-kerja-attachment', [$pkpt, $att->id]),
                            ];
                        }, $files));
                        $slugKey = \Illuminate\Support\Str::slug($key);
                    @endphp

                    <div id="category-{{ $slugKey }}" 
                         x-data="kertasKerjaCard({
                             key: '{{ $key }}',
                             label: '{{ $label }}',
                             files: {{ Js::from($filesData) }},
                             csrfToken: '{{ csrf_token() }}',
                             uploadUrl: '{{ route('pkpt.store-kertas-kerja', $pkpt) }}'
                         })"
                         class="border rounded-2xl transition-all duration-200 overflow-hidden flex flex-col justify-between"
                         :class="hasFiles ? 'border-emerald-500 bg-white shadow-sm hover:shadow-md ring-1 ring-emerald-500/20' : 'border-surface-200 bg-white shadow-sm'">
                        
                        {{-- Card Header --}}
                        <div class="p-4 sm:p-5 border-b transition-colors duration-200"
                             :class="hasFiles ? 'border-emerald-200 bg-emerald-50/50' : 'border-surface-100 bg-surface-50/50'">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold flex-shrink-0 transition-colors"
                                          :class="hasFiles ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-200 text-charcoal'">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-charcoal text-base tracking-tight">{{ $key }}</h4>
                                        <p class="text-xs text-charcoal-muted mt-0.5">{{ $label }}</p>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <template x-if="hasFiles">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-400 shadow-2xs">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span x-text="files.length + ' Berkas Terunggah'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!hasFiles">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-surface-100 text-charcoal-muted border border-surface-200">
                                            Belum Ada Berkas
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body: List of Uploaded Files --}}
                        <div x-show="hasFiles" class="p-4 sm:p-5 flex-1 flex flex-col border-b border-surface-100">
                            <div class="space-y-2.5 flex-1">
                                <div class="flex items-center justify-between text-xs text-charcoal-muted font-medium pb-1 border-b border-surface-100">
                                    <span>Berkas Terlampir (<span x-text="files.length"></span>)</span>
                                    <span>Aksi</span>
                                </div>
                                
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                    <template x-for="att in files" :key="att.id">
                                        <div class="p-2.5 bg-emerald-50/20 hover:bg-emerald-50/40 border border-emerald-400 rounded-xl flex items-center justify-between gap-3 transition-colors shadow-2xs">
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                {{-- File Type Icon --}}
                                                <x-file-type-icon alpine-ext="att.ext" size="md" />

                                                {{-- File Info --}}
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-semibold text-charcoal truncate" :title="att.file_name" x-text="att.file_name"></p>
                                                    <div class="flex items-center gap-1.5 text-[10.5px] text-charcoal-muted mt-0.5">
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                                            <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                            Terunggah
                                                        </span>
                                                        <template x-if="att.file_size_formatted">
                                                            <span>&bull; <span x-text="att.file_size_formatted"></span></span>
                                                        </template>
                                                        <span>&bull; <span x-text="att.created_at_formatted"></span></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Action Buttons --}}
                                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                                {{-- View / Preview --}}
                                                <a :href="att.view_url" 
                                                   target="_blank" 
                                                   title="Lihat Berkas" 
                                                   class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-90 shadow-sm transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                                
                                                {{-- Download --}}
                                                <a :href="att.download_url" 
                                                   title="Unduh Berkas" 
                                                   class="btn-icon bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>
                                                
                                                {{-- Delete (Draft Only) --}}
                                                @if($document && $document->status === 'draft')
                                                <button type="button" 
                                                        @click="deleteFile(att)"
                                                        title="Hapus Berkas" 
                                                        class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-90 shadow-sm transition-all cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer: Upload Form (Only in Draft Status) --}}
                        @if($document && $document->status === 'draft')
                            <div class="p-4 sm:p-5 border-t border-surface-100 bg-surface-50/60">
                                <form x-ref="uploadForm" 
                                      action="{{ route('pkpt.store-kertas-kerja', $pkpt) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data"
                                      onsubmit="return false;"
                                      @submit.prevent="submitUpload()">
                                    @csrf
                                    <input type="hidden" name="category" value="{{ $key }}">
                                    
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-xs font-semibold text-charcoal flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            Tambah Berkas {{ $key }}
                                        </label>
                                        <span class="text-[10.5px] text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md font-medium border border-primary-100">
                                            Bisa multi-file
                                        </span>
                                    </div>
                                    
                                    {{-- Selected files list preview --}}
                                    <div x-show="selectedFiles.length > 0" class="mb-3 space-y-1.5 bg-emerald-50/60 p-3 rounded-xl border border-emerald-400">
                                        <div class="flex items-center justify-between text-xs font-semibold text-emerald-800 pb-1 border-b border-emerald-300">
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span x-text="selectedFiles.length + ' Berkas Siap Diunggah:'"></span>
                                            </span>
                                            <div class="flex items-center gap-2">
                                                <button type="button" :disabled="isUploading" @click="$refs.fileInput.click()" class="text-primary-600 hover:text-primary-800 font-medium hover:underline flex items-center gap-1 text-[11px] disabled:opacity-50">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                    Tambah lagi
                                                </button>
                                                <span class="text-emerald-300">|</span>
                                                <button type="button" :disabled="isUploading" @click="clearFiles()" class="text-red-600 hover:text-red-700 font-medium hover:underline text-[11px] disabled:opacity-50">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                        <div class="space-y-1 max-h-36 overflow-y-auto pr-1">
                                            <template x-for="(f, i) in selectedFiles" :key="i">
                                                <div class="flex items-center justify-between p-1.5 bg-white border border-emerald-400 rounded-lg text-xs shadow-2xs gap-2">
                                                    <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                                        <x-file-type-icon alpine-ext="f.ext" size="xs" />
                                                        <span class="truncate font-medium text-emerald-900" x-text="f.name + ' (' + f.size + ')'"></span>
                                                    </div>
                                                    <button type="button" :disabled="isUploading" @click="removeSelectedFile(i)" class="text-red-500 hover:text-red-700 ml-2 p-0.5 rounded hover:bg-red-50 disabled:opacity-50" title="Hapus">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Modern Taller Dropzone --}}
                                    <div class="relative border-2 border-dashed rounded-xl p-5 text-center transition-all duration-200 group flex flex-col items-center justify-center min-h-[130px] cursor-pointer mb-3"
                                         :class="[
                                             isDragging 
                                                 ? 'border-primary-500 bg-primary-50/80 ring-4 ring-primary-100 shadow-sm' 
                                                 : 'border-surface-300 hover:border-primary-400 bg-white hover:bg-surface-50/60',
                                             isUploading ? 'opacity-70 pointer-events-none' : ''
                                         ]">
                                        
                                        <input type="file" 
                                               x-ref="fileInput"
                                               name="attachments[]" 
                                               multiple
                                               @change="handleFiles($event)"
                                               @dragenter.prevent="isDragging = true"
                                               @dragover.prevent="isDragging = true"
                                               @dragleave.prevent="isDragging = false"
                                               @drop.prevent="handleDrop($event)"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx" 
                                               title="Klik atau seret & lepas berkas ke sini">

                                        <div :class="isDragging ? 'bg-primary-600 text-white scale-110 shadow-sm' : 'bg-primary-50 text-primary-600 group-hover:scale-105 group-hover:bg-primary-100'"
                                             class="w-10 h-10 rounded-xl flex items-center justify-center mb-2 transition-all duration-200 border border-primary-200/60 pointer-events-none">
                                            <template x-if="!isDragging">
                                                <svg class="w-5 h-5 transform transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            </template>
                                            <template x-if="isDragging">
                                                <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            </template>
                                        </div>

                                        <div class="pointer-events-none space-y-0.5">
                                            <template x-if="isDragging">
                                                <p class="text-xs font-bold text-primary-700 animate-pulse">Lepaskan berkas di sini untuk menambahkan...</p>
                                            </template>
                                            <template x-if="!isDragging">
                                                <p class="text-xs font-medium text-charcoal">
                                                    <span class="text-primary-600 font-semibold underline decoration-primary-300">Pilih berkas</span> atau seret & lepas berkas ke sini
                                                </p>
                                            </template>
                                            <p class="text-[11px] text-charcoal-muted">PDF, DOC, DOCX, XLS, XLSX (bisa beberapa berkas sekaligus)</p>
                                        </div>
                                    </div>

                                    {{-- Upload Progress Indicator --}}
                                    <div x-show="isUploading" x-cloak data-inline-upload-progress class="mb-3 p-3 bg-primary-50/80 rounded-xl border border-primary-200">
                                        <div class="flex items-center justify-between text-xs font-semibold text-primary-800 mb-1.5">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span>Sedang mengunggah berkas...</span>
                                            </span>
                                            <span x-text="uploadProgress + '%'"></span>
                                        </div>
                                        <div class="w-full bg-primary-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-150" :style="'width: ' + uploadProgress + '%'"></div>
                                        </div>
                                    </div>

                                    {{-- Error Message Alert --}}
                                    <div x-show="errorMessage" class="mb-3 p-2.5 bg-red-50 border border-red-200 rounded-xl flex items-center justify-between gap-2 text-xs text-red-700">
                                        <span x-text="errorMessage"></span>
                                        <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                    </div>

                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[10.5px] text-charcoal-muted">Pastikan format & nama dokumen sesuai.</p>
                                        <button type="button" 
                                                @click="submitUpload()"
                                                :disabled="selectedFiles.length === 0 || isUploading"
                                                :class="(selectedFiles.length === 0 || isUploading) ? 'opacity-60 cursor-not-allowed bg-surface-300 text-charcoal-muted' : 'btn-primary'"
                                                class="text-xs py-2 px-4 h-[36px] flex-shrink-0 flex items-center gap-1.5 shadow-sm font-semibold rounded-lg transition-all cursor-pointer">
                                            <template x-if="!isUploading">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            </template>
                                            <template x-if="isUploading">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </template>
                                            <span x-text="isUploading ? 'Mengunggah...' : (selectedFiles.length > 0 ? 'Unggah ' + selectedFiles.length + ' Dokumen' : 'Unggah Dokumen')"></span>
                                        </button>
                                    </div>
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
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 sm:p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600 border border-emerald-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-emerald-900">Kertas Kerja PKPT Telah Selesai (Closed)</p>
                                <p class="text-xs text-emerald-700 mt-0.5">Seluruh berkas dokumen Kertas Kerja pada PKPT ini telah dikunci. Berkas tidak dapat diubah atau dihapus.</p>
                            </div>
                        </div>
                        @if(auth()->user()->hasRole('admin'))
                        <div class="flex-shrink-0">
                            <form action="{{ route('pkpt.reopen-kertas-kerja', $pkpt) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci (reopen) Kertas Kerja PKPT ini? Dokumen akan kembali ke status Draft sehingga berkas dapat diubah, ditambah, atau dihapus kembali.')">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs bg-amber-500 hover:bg-amber-600 text-white shadow-md shadow-amber-500/20 hover:shadow-lg transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                    <span>Buka Kunci Closed (Reopen ke Draft)</span>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Floating Notification Toast for In-Place Actions --}}
<div x-data="{ show: false, message: '' }" 
     @pkpt-attachments-updated.window="message = $event.detail.message || 'Perubahan berkas berhasil disimpan.'; show = true; setTimeout(() => show = false, 3500)"
     class="fixed bottom-6 right-6 z-50 transition-all duration-300"
     style="display: none;"
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-4 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center gap-2.5 px-4 py-3 bg-charcoal text-white rounded-xl shadow-xl border border-charcoal-light/50 text-xs sm:text-sm font-medium">
        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        <span x-text="message"></span>
    </div>
</div>

<script>
if (typeof window.kertasKerjaCard !== 'function') {
    window.kertasKerjaCard = function(config) {
        return {
            key: config.key,
            label: config.label,
            files: config.files || [],
            csrfToken: config.csrfToken,
            selectedFiles: [],
            isDragging: false,
            isUploading: false,
            uploadProgress: 0,
            errorMessage: '',

            get hasFiles() {
                return this.files.length > 0;
            },
            getDt() {
                if (!this.$el._dt) this.$el._dt = new DataTransfer();
                return this.$el._dt;
            },
            setDt(newDt) {
                this.$el._dt = newDt;
            },
            addFiles(newFiles) {
                const dt = this.getDt();
                const files = Array.from(newFiles || []);
                for (let f of files) {
                    const exists = Array.from(dt.files).some(x => x.name === f.name && x.size === f.size && x.lastModified === f.lastModified);
                    if (!exists) dt.items.add(f);
                }
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.files = dt.files;
                }
                this.syncList();
            },
            handleFiles(e) {
                this.addFiles(e.target.files);
            },
            handleDrop(e) {
                this.isDragging = false;
                if (e.dataTransfer && e.dataTransfer.files) {
                    this.addFiles(e.dataTransfer.files);
                }
            },
            removeSelectedFile(idx) {
                const dt = this.getDt();
                const newDt = new DataTransfer();
                Array.from(dt.files).forEach((f, i) => {
                    if (i !== idx) newDt.items.add(f);
                });
                this.setDt(newDt);
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.files = newDt.files;
                    if (newDt.files.length === 0) {
                        this.$refs.fileInput.value = '';
                    }
                }
                this.syncList();
            },
            clearFiles() {
                this.setDt(new DataTransfer());
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
                this.selectedFiles = [];
                this.errorMessage = '';
            },
            syncList() {
                const dt = this.getDt();
                this.selectedFiles = Array.from(dt.files).map(f => ({
                    name: f.name,
                    size: f.size > 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : Math.round(f.size / 1024) + ' KB'
                }));
            },
            submitUpload() {
                const dt = this.getDt();
                const filesToUpload = dt.files.length > 0 ? dt.files : (this.$refs.fileInput ? this.$refs.fileInput.files : []);
                if (filesToUpload.length === 0 || this.isUploading) return;

                this.isUploading = true;
                this.uploadProgress = 0;
                this.errorMessage = '';

                const formData = new FormData();
                formData.append('_token', this.csrfToken);
                formData.append('category', this.key);
                for (let i = 0; i < filesToUpload.length; i++) {
                    formData.append('attachments[]', filesToUpload[i]);
                }

                const targetUrl = config.uploadUrl || (this.$refs.uploadForm ? this.$refs.uploadForm.action : window.location.href);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', targetUrl, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    this.isUploading = false;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                this.files = res.attachments || [];
                                this.clearFiles();
                                window.dispatchEvent(new CustomEvent('pkpt-attachments-updated', {
                                    detail: { total: res.total_count, category: this.key, message: res.message }
                                }));
                            } else {
                                this.errorMessage = res.message || 'Gagal mengunggah berkas.';
                            }
                        } catch (err) {
                            this.errorMessage = 'Terjadi kesalahan respons server.';
                        }
                    } else {
                        try {
                            const errRes = JSON.parse(xhr.responseText);
                            this.errorMessage = errRes.message || 'Gagal mengunggah berkas. Pastikan format file PDF, Word, atau Excel.';
                        } catch(e) {
                            this.errorMessage = 'Gagal mengunggah berkas (Error ' + xhr.status + ').';
                        }
                    }
                };

                xhr.onerror = () => {
                    this.isUploading = false;
                    this.errorMessage = 'Koneksi jaringan terputus saat mengunggah.';
                };

                xhr.send(formData);
            },
            deleteFile(att) {
                if (!att || !att.delete_url) return;
                const msg = 'Apakah Anda yakin ingin menghapus file "' + att.file_name + '"?';

                const proceed = async () => {
                    try {
                        const res = await fetch(att.delete_url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data.success) {
                                this.files = this.files.filter(f => f.id !== att.id);
                                window.dispatchEvent(new CustomEvent('pkpt-attachments-updated', {
                                    detail: { 
                                        total: data.total_count, 
                                        category: this.key, 
                                        message: 'File ' + att.file_name + ' berhasil dihapus.' 
                                    }
                                }));
                            } else {
                                this.errorMessage = data.message || 'Gagal menghapus file.';
                            }
                        } else {
                            const errData = await res.json().catch(() => ({}));
                            this.errorMessage = errData.message || ('Gagal menghapus file (Error ' + res.status + ').');
                        }
                    } catch (e) {
                        this.errorMessage = 'Terjadi kesalahan jaringan saat menghapus file.';
                    }
                };

                if (typeof window.showConfirmDialog === 'function') {
                    window.showConfirmDialog(msg, proceed);
                } else if (confirm(msg)) {
                    proceed();
                }
            }
        };
    };
}

// Preserve scroll position if ever a page refresh occurs
window.addEventListener('beforeunload', () => {
    sessionStorage.setItem('pkpt_scroll_pos', window.scrollY);
});

document.addEventListener('DOMContentLoaded', () => {
    const savedPos = sessionStorage.getItem('pkpt_scroll_pos');
    if (savedPos !== null) {
        window.scrollTo({ top: parseInt(savedPos, 10), behavior: 'instant' });
        sessionStorage.removeItem('pkpt_scroll_pos');
    }
});
</script>
@endsection
