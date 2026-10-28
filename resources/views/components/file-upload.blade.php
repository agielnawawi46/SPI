@props([
    'name' => 'attachments',
    'id' => null,
    'label' => 'Unggah Berkas / Lampiran',
    'subtitle' => 'Pilih satu atau beberapa berkas dokumen',
    'multiple' => true,
    'required' => false,
    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg',
    'hint' => 'Format didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG.',
    'existingFiles' => null,
    'badge' => null,
    'deleteRoute' => null,
    'deleteRouteParams' => [],
])

@php
    $inputName = $multiple ? ($name . (str_ends_with($name, '[]') ? '' : '[]')) : $name;
    $inputId = $id ?: 'upload_' . md5($name . microtime());
    $filesList = collect();
    if ($existingFiles instanceof \Illuminate\Support\Collection) {
        $filesList = $existingFiles;
    } elseif (is_iterable($existingFiles)) {
        $filesList = collect($existingFiles);
    } elseif ($existingFiles) {
        $filesList = collect([$existingFiles]);
    }
@endphp

<div class="border rounded-2xl bg-white shadow-sm overflow-hidden transition-all duration-200" 
     :class="(selectedFiles.length > 0 || @json($filesList->isNotEmpty())) ? 'border-emerald-500 ring-1 ring-emerald-500/20' : 'border-surface-200'" 
     x-data="{
        selectedFiles: [],
        isDragging: false,
        getDt() {
            if (!this.$el._dt) this.$el._dt = new DataTransfer();
            return this.$el._dt;
        },
        setDt(newDt) {
            this.$el._dt = newDt;
        },
        addFiles(files) {
            const fileList = Array.from(files || []);
            if (fileList.length === 0) return;

            const dt = this.getDt();
            if (@json($multiple)) {
                for (const file of fileList) {
                    const exists = Array.from(dt.files).some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
                    if (!exists) {
                        dt.items.add(file);
                    }
                }
            } else {
                const singleDt = new DataTransfer();
                singleDt.items.add(fileList[0]);
                this.setDt(singleDt);
            }

            const input = document.getElementById('{{ $inputId }}');
            if (input) {
                input.files = this.getDt().files;
            }
            this.syncSelectedFiles();
        },
        handleFileChange(event) {
            this.addFiles(event.target.files);
        },
        handleDrop(event) {
            this.isDragging = false;
            if (event.dataTransfer && event.dataTransfer.files) {
                this.addFiles(event.dataTransfer.files);
            }
        },
        removeFile(index) {
            const dt = this.getDt();
            const newDt = new DataTransfer();
            Array.from(dt.files).forEach((file, i) => {
                if (i !== index) {
                    newDt.items.add(file);
                }
            });
            this.setDt(newDt);
            const input = document.getElementById('{{ $inputId }}');
            if (input) {
                input.files = newDt.files;
                if (newDt.files.length === 0) {
                    input.value = '';
                }
            }
            this.syncSelectedFiles();
        },
        clearFiles() {
            this.setDt(new DataTransfer());
            const el = document.getElementById('{{ $inputId }}');
            if (el) el.value = '';
            this.syncSelectedFiles();
        },
        syncSelectedFiles() {
            const dt = this.getDt();
            this.selectedFiles = Array.from(dt.files).map(f => {
                let sizeStr = '';
                if (f.size > 1024 * 1024) {
                    sizeStr = (f.size / (1024 * 1024)).toFixed(2) + ' MB';
                } else if (f.size > 1024) {
                    sizeStr = (f.size / 1024).toFixed(1) + ' KB';
                } else {
                    sizeStr = f.size + ' B';
                }
                const ext = f.name.split('.').pop().toLowerCase();
                return {
                    name: f.name,
                    size: sizeStr,
                    ext: ext
                };
            });
        }
     }">
    
    {{-- Header --}}
    <div class="p-4 sm:p-5 border-b {{ $filesList->isNotEmpty() ? 'border-emerald-200 bg-emerald-50/30' : 'border-surface-100 bg-surface-50/60' }}">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl {{ $filesList->isNotEmpty() ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-primary-50 text-primary-600 border border-primary-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-charcoal text-sm flex items-center gap-2">
                        {{ $label }}
                        @if($required)
                            <span class="text-red-500">*</span>
                        @endif
                    </h4>
                    @if($subtitle)
                        <p class="text-xs text-charcoal-muted mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($filesList->isNotEmpty())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-400 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $filesList->count() }} Berkas Terlampir
                    </span>
                @endif
                @if($multiple)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                        Bisa multi-file
                    </span>
                @endif
                @if($badge)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-surface-100 text-charcoal-muted border border-surface-200">
                        {{ $badge }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- List of Existing Files (If Available) --}}
    @if($filesList->isNotEmpty())
        <div class="p-4 sm:p-5 border-b border-surface-100 bg-white">
            <div class="text-xs font-semibold text-charcoal-muted mb-2.5 flex items-center justify-between">
                <span>Berkas Saat Ini Terlampir ({{ $filesList->count() }})</span>
                <span class="text-[11px] font-normal text-charcoal-muted">Unggah berkas baru di bawah jika ingin menambah/mengganti</span>
            </div>
            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                @foreach($filesList as $item)
                    @php
                        $fileName = $item->file_name ?? (basename($item->file_path ?? $item));
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $iconStyle = match($ext) {
                            'pdf' => 'text-red-500 bg-red-50 border-red-200',
                            'doc', 'docx' => 'text-blue-500 bg-blue-50 border-blue-200',
                            'xls', 'xlsx' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                            'jpg', 'jpeg', 'png' => 'text-purple-500 bg-purple-50 border-purple-200',
                            default => 'text-charcoal bg-surface-100 border-surface-200'
                        };
                        $badgeStyle = match($ext) {
                            'pdf' => 'bg-red-50 text-red-700 border-red-200',
                            'doc', 'docx' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'xls', 'xlsx' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'jpg', 'jpeg', 'png' => 'bg-purple-50 text-purple-700 border-purple-200',
                            default => 'bg-surface-100 text-charcoal border-surface-200'
                        };
                        $fileUrl = is_object($item) ? Storage::url($item->pdf_path ?: $item->file_path) : Storage::url($item);
                        $downloadUrl = is_object($item) ? Storage::url($item->file_path) : Storage::url($item);
                    @endphp

                    <div class="p-2.5 bg-emerald-50/20 hover:bg-emerald-50/40 border border-emerald-400 rounded-xl flex items-center justify-between gap-3 transition-colors shadow-2xs">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 border {{ $iconStyle }}">
                                @if($ext === 'pdf')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @elseif(in_array($ext, ['xls', 'xlsx']))
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @elseif(in_array($ext, ['jpg', 'jpeg', 'png']))
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-charcoal truncate" title="{{ $fileName }}">
                                    {{ $fileName }}
                                </p>
                                <div class="flex items-center gap-1.5 text-[10.5px] text-charcoal-muted mt-0.5">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-400/80">
                                        <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Terunggah
                                    </span>
                                    <span class="font-medium uppercase px-1 py-0.2 rounded border {{ $badgeStyle }}">{{ $ext }}</span>
                                    @if(is_object($item) && isset($item->file_size_formatted))
                                        <span>&bull; {{ $item->file_size_formatted }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <a href="{{ $fileUrl }}" target="_blank" title="Lihat Berkas" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-90 shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ $downloadUrl }}" download target="_blank" title="Unduh Berkas" class="btn-icon bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                            @php
                                $deleteUrl = null;
                                if ($deleteRoute && is_object($item) && isset($item->id)) {
                                    $routeParams = array_merge($deleteRouteParams, ['attachment' => $item->id]);
                                    $deleteUrl = route($deleteRoute, $routeParams);
                                } elseif (is_object($item) && isset($item->id) && isset($item->document_id)) {
                                    $deleteUrl = route('documents.destroy-attachment', [$item->document_id, $item->id]);
                                }
                            @endphp
                            @if($deleteUrl)
                            <button type="button" 
                                    onclick="if(confirm('Apakah Anda yakin ingin menghapus berkas \'{{ addslashes($fileName) }}\'?')) { const f = document.createElement('form'); f.method = 'POST'; f.action = '{{ $deleteUrl }}'; const t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = '{{ csrf_token() }}'; const m = document.createElement('input'); m.type = 'hidden'; m.name = '_method'; m.value = 'DELETE'; f.appendChild(t); f.appendChild(m); document.body.appendChild(f); f.submit(); }"
                                    title="Hapus Berkas" 
                                    class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-90 shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Live Preview of Selected Files (Alpine.js) --}}
    <div x-show="selectedFiles.length > 0" x-cloak class="p-4 sm:p-5 border-b border-emerald-300 bg-emerald-50/40">
        <div class="flex items-center justify-between text-xs font-semibold text-emerald-800 mb-2">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span x-text="selectedFiles.length + ' Berkas Siap Diunggah'"></span>
            </span>
            <div class="flex items-center gap-3">
                @if($multiple)
                <button type="button" @click="document.getElementById('{{ $inputId }}').click()" class="text-xs text-primary-700 hover:text-primary-800 font-semibold hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Berkas Lain
                </button>
                <span class="text-surface-300">|</span>
                @endif
                <button type="button" @click="clearFiles()" class="text-xs text-red-600 hover:text-red-700 font-medium hover:underline">
                    Batal Semua
                </button>
            </div>
        </div>
        <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
            <template x-for="(f, i) in selectedFiles" :key="i">
                <div class="p-2 bg-white border border-emerald-400 rounded-lg flex items-center justify-between text-xs shadow-2xs gap-2">
                    <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                        <span class="font-bold uppercase text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-400" x-text="f.ext"></span>
                        <span class="font-medium text-charcoal truncate" x-text="f.name"></span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-charcoal-muted text-[11px]" x-text="f.size"></span>
                        <button type="button" 
                                @click="removeFile(i)" 
                                title="Hapus berkas ini dari daftar pilihan"
                                class="w-5 h-5 rounded-md flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 border border-transparent hover:border-red-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Upload Dropzone Area --}}
    <div class="p-4 sm:p-6 bg-surface-50/40">
        <div class="relative border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center transition-all duration-200 group flex flex-col items-center justify-center min-h-[160px] cursor-pointer"
             :class="isDragging 
                 ? 'border-primary-500 bg-primary-50/80 ring-4 ring-primary-100 shadow-sm' 
                 : 'border-surface-300 hover:border-primary-400 bg-white hover:bg-surface-50/70'">

            {{-- Transparent Native File Input Overlay --}}
            <input type="file" 
                   name="{{ $inputName }}" 
                   id="{{ $inputId }}"
                   x-ref="fileInput"
                   @if($multiple) multiple @endif
                   @if($required && $filesList->isEmpty()) required @endif
                   accept="{{ $accept }}"
                   @change="handleFileChange($event)"
                   @dragenter.prevent="isDragging = true"
                   @dragover.prevent="isDragging = true"
                   @dragleave.prevent="isDragging = false"
                   @drop.prevent="handleDrop($event)"
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                   title="Klik atau seret & lepas berkas ke sini">

            {{-- Upload Icon --}}
            <div :class="isDragging ? 'bg-primary-600 text-white scale-110 shadow-md ring-4 ring-primary-200' : 'bg-primary-50 text-primary-600 group-hover:scale-105 group-hover:bg-primary-100 group-hover:text-primary-700'"
                 class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-3 transition-all duration-200 border border-primary-200/60 shadow-2xs pointer-events-none">
                <template x-if="!isDragging">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transform transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </template>
                <template x-if="isDragging">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </template>
            </div>

            {{-- Text Instructions --}}
            <div class="space-y-1.5 max-w-md pointer-events-none">
                <template x-if="isDragging">
                    <p class="text-sm sm:text-base font-bold text-primary-700 animate-pulse">
                        Lepaskan berkas di sini untuk menambahkan berkas...
                    </p>
                </template>
                <template x-if="!isDragging">
                    <p class="text-sm sm:text-base text-charcoal font-semibold">
                        <span class="text-primary-600 group-hover:text-primary-700 underline decoration-primary-300 underline-offset-2">Klik untuk memilih berkas</span> 
                        atau seret & lepas berkas ke sini
                    </p>
                </template>
                <p class="text-xs text-charcoal-muted">
                    {{ $hint }}
                    @if($multiple && !str_contains($hint, 'sekaligus') && !str_contains($hint, 'bertahap'))
                        <span class="font-medium text-primary-700">(Bisa memilih beberapa berkas sekaligus atau bertahap).</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
