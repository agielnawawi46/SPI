@extends('layouts.app')
@section('title', 'Edit ' . $document->title)
@section('page_title', 'Edit Dokumen')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('assurance.index', ['audit_type' => $document->audit_type, 'type' => $document->documentType?->slug]) }}" class="breadcrumb-link">Assurance ({{ ucfirst(str_replace('_', ' ', $document->audit_type)) }})</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('assurance.show', $document) }}" class="breadcrumb-link">{{ $document->title }}</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Edit: {{ $document->title }}</h2>
        <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
    </div>
    <form action="{{ route('assurance.update', $document) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="card-body space-y-6">
            @if(in_array($document->documentType?->slug, ['program-kerja', 'slide-entry', 'kertas-kerja-audit', 'lha']))
            {{-- Upload-only types: simple form --}}
            <div class="grid grid-cols-1 gap-4 mb-4">
                <div class="form-group">
                    <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Masukkan judul dokumen">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <x-file-upload 
                    name="attachments" 
                    label="Unggah Berkas Baru / Tambahan"
                    subtitle="Pilih berkas baru untuk menambah lampiran dokumen ini (bisa multi-file)"
                    :required="false"
                    :multiple="true"
                    :existingFiles="$document->attachments"
                    badge="Opsional"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                />
                @error('attachments')<p class="form-error mt-1">{{ $message }}</p>@enderror
                @error('attachment')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>
            @elseif($document->documentType?->slug === 'surat-tugas')
                <!-- HEADER -->
                <div class="space-y-6 mb-6">
                    <!-- BAGIAN 1: Informasi Dokumen & Header (Daftar & Penomoran) -->
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden"
                         x-data="{
                             headerText: @json(old('header_text', $document->header_text ?? 'FMR-003-14')),
                             tanggalFormulir: @json(old('tanggal_formulir', $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->format('Y-m-d') : date('Y-m-d'))),
                             judulDokumen: @json(old('judul_dokumen', $document->judul_dokumen ?? 'SURAT TUGAS AUDIT')),
                             titleNoStg: @json(old('title', $document->title ?? '')),
                             deskripsiPembuka: @json(old('deskripsi_pembuka', $document->deskripsi_pembuka ?: ('Dalam rangka melakukan audit rutin di lingkungan Perusahaan sesuai Program Kerja Pengawasan Tahunan (PKPT) Terintegrasi ' . date('Y') . ' yang dilaksanakan oleh Tim SPI PT PEB, maka dengan ini kami menugaskan kepada :'))),
                             isSavingTemplate: false,
                             templateSavedSuccess: false,
                             templateMessage: '',
                             
                             async saveAsDefaultTemplate() {
                                 this.isSavingTemplate = true;
                                 this.templateSavedSuccess = false;
                                 this.templateMessage = '';
                                 try {
                                     const res = await fetch('{{ route('assurance.save-header-template') }}', {
                                         method: 'POST',
                                         headers: {
                                             'Content-Type': 'application/json',
                                             'Accept': 'application/json',
                                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                         },
                                         body: JSON.stringify({
                                             audit_type: '{{ $document->audit_type }}',
                                             header_text: this.headerText,
                                             tanggal_formulir: this.tanggalFormulir,
                                             judul_dokumen: this.judulDokumen,
                                             title: this.titleNoStg,
                                             deskripsi_pembuka: this.deskripsiPembuka
                                         })
                                     });
                                     const data = await res.json();
                                     if (data.success) {
                                         this.templateSavedSuccess = true;
                                         this.templateMessage = data.message || 'Format penomoran berhasil disimpan sebagai default berikutnya.';
                                         setTimeout(() => { this.templateSavedSuccess = false; }, 6000);
                                     } else {
                                         alert(data.message || 'Gagal menyimpan format penomoran.');
                                     }
                                 } catch (err) {
                                     console.error(err);
                                     alert('Terjadi kesalahan saat menyimpan format penomoran.');
                                 } finally {
                                     this.isSavingTemplate = false;
                                 }
                             }
                         }">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Informasi Dokumen & Header
                            </h4>
                        </div>

                        {{-- Notifikasi Sukses Simpan Default --}}
                        <div x-show="templateSavedSuccess" x-collapse class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 flex items-center justify-between text-xs text-emerald-800">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="templateMessage"></span>
                            </div>
                            <button type="button" @click="templateSavedSuccess = false" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                        </div>

                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">No. Formulir</label>
                                <input type="text" name="header_text" x-model="headerText" class="form-input w-full" placeholder="Contoh: FMR-003-14">
                                <p class="text-xs text-charcoal-muted mt-1">Teks akan berada di sebelah kanan gambar formulir.</p>
                                @error('header_text')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal_formulir" x-model="tanggalFormulir" class="form-input w-full">
                                @error('tanggal_formulir')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Judul Surat</label>
                                <input type="text" name="judul_dokumen" x-model="judulDokumen" class="form-input" placeholder="SURAT TUGAS AUDIT">
                                @error('judul_dokumen')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">No STG <span class="text-red-500">*</span></label>
                                <input type="text" name="title" x-model="titleNoStg" class="form-input" placeholder="Contoh: NO. 03.STA/VII/SPI-PEB/2026">
                                @error('title')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label flex items-center justify-between">
                                    <span>Deskripsi Pembuka</span>
                                    <span class="text-[11px] text-charcoal-muted font-normal">(Dapat disesuaikan dan disimpan sebagai template)</span>
                                </label>
                                <textarea name="deskripsi_pembuka" x-model="deskripsiPembuka" class="form-textarea w-full text-charcoal border-surface-300" rows="5"></textarea>
                                @error('deskripsi_pembuka')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            {{-- Action Bar di bawah Bagian 1 --}}
                            <div class="md:col-span-2 pt-2 border-t border-surface-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <p class="text-xs text-charcoal-muted">
                                    <span class="font-semibold text-charcoal">Keterangan:</span> Klik <strong>"Simpan Format Penomoran"</strong> agar isian ini otomatis muncul saat pembuatan surat tugas berikutnya tanpa reload halaman.
                                </p>
                                <button type="button" 
                                        @click="saveAsDefaultTemplate()" 
                                        :disabled="isSavingTemplate"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer flex-shrink-0"
                                        :class="templateSavedSuccess ? 'bg-emerald-600 text-white' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300'">
                                    <svg x-show="!isSavingTemplate && !templateSavedSuccess" class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span x-text="isSavingTemplate ? 'Menyimpan format...' : (templateSavedSuccess ? 'Format Tersimpan!' : 'Simpan Format Penomoran')"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TIM AUDIT -->
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden" x-data="{
                        rows: {{ old('tim_audit') ? json_encode(old('tim_audit')) : (is_array($document->tim_audit) && count($document->tim_audit) > 0 ? json_encode($document->tim_audit) : (isset($timAudit) && count($timAudit) > 0 ? json_encode($timAudit) : "[{nama: '', nip: '', jabatan_pegawai: '', jabatan_tim: 'Ketua Tim'}]")) }}
                    }">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Susunan Tim Audit
                            </h4>
                        </div>
                        <div class="p-4 space-y-4 bg-surface-50">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="bg-white border border-surface-200 rounded-lg p-4 relative shadow-sm">
                                    <button type="button" @click="rows.splice(index, 1)" x-show="rows.length > 1" class="absolute top-2 right-2 text-red-500 hover:bg-red-50 p-1.5 rounded-md transition-colors cursor-pointer" title="Hapus Anggota">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="bg-primary-100 text-primary-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" x-text="index + 1"></span>
                                        <span class="text-sm font-semibold text-charcoal" x-text="index === 0 ? 'Ketua Tim' : 'Anggota Tim'"></span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="form-group">
                                            <label class="form-label text-xs">Nama Pegawai <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="row.nama" :name="'tim_audit['+index+'][nama]'" class="form-input w-full text-sm" placeholder="Nama Lengkap">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs">NIP <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="row.nip" :name="'tim_audit['+index+'][nip]'" class="form-input w-full text-sm" placeholder="Nomor Induk Pegawai">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs">Jabatan (Organik) <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="row.jabatan_pegawai" :name="'tim_audit['+index+'][jabatan_pegawai]'" class="form-input w-full text-sm" placeholder="Contoh: Manager IT">
                                        </div>
                                        <div class="form-group md:col-span-3">
                                            <label class="form-label text-xs">Peran / Jabatan di Tim <span class="text-red-500">*</span></label>
                                            <select x-model="row.jabatan_tim" :name="'tim_audit['+index+'][jabatan_tim]'" class="form-select w-full text-sm">
                                                <option value="Penanggung Jawab">Penanggung Jawab</option>
                                                <option value="Wakil Penanggung Jawab">Wakil Penanggung Jawab</option>
                                                <option value="Pengendali Mutu">Pengendali Mutu</option>
                                                <option value="Pengendali Teknis">Pengendali Teknis</option>
                                                <option value="Ketua Tim">Ketua Tim</option>
                                                <option value="Anggota Tim">Anggota Tim</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <button type="button" @click="rows.push({nama: '', nip: '', jabatan_pegawai: '', jabatan_tim: 'Anggota Tim'})" class="w-full py-3 border-2 border-dashed border-primary-200 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors font-medium text-sm flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Anggota Tim
                            </button>
                        </div>
                        @error('tim_audit')<p class="form-error mt-2">{{ $message }}</p>@enderror
                    </div>

                    <!-- RINCIAN PENUGASAN -->
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden mt-6"
                         x-data="{
                             jadwalMulai: {{ json_encode(old('jadwal_mulai', $document->jadwal_mulai?->format('Y-m-d') ?? ''), JSON_UNESCAPED_SLASHES) }},
                             jadwalSelesai: {{ json_encode(old('jadwal_selesai', $document->jadwal_selesai?->format('Y-m-d') ?? ''), JSON_UNESCAPED_SLASHES) }},
                             legacyJadwal: {{ json_encode(old('jadwal_audit', $document->jadwal_audit ?? ''), JSON_UNESCAPED_SLASHES) }},
                             formatDateIndo(dateStr) {
                                 if (!dateStr) return '';
                                 const parts = dateStr.split('-');
                                 if (parts.length !== 3) return dateStr;
                                 const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                 const day = parts[2];
                                 const month = months[parseInt(parts[1], 10) - 1] || '';
                                 const year = parts[0];
                                 return `${day} ${month} ${year}`.trim();
                             },
                             get formattedJadwal() {
                                 const m = this.formatDateIndo(this.jadwalMulai);
                                 const s = this.formatDateIndo(this.jadwalSelesai);
                                 if (m && s) return `${m} s.d. ${s}`;
                                 if (m) return m;
                                 return this.legacyJadwal || '';
                             }
                         }">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Rincian Penugasan
                            </h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 gap-4">
                            <!-- AUDITEE -->
                            <div class="form-group">
                                <label class="form-label">Audit Universe (Auditee) <span class="text-red-500">*</span></label>
                                <input type="text" name="auditee" class="form-input w-full" value="{{ old('auditee', $document->auditee) }}" placeholder="Contoh: Divisi Operasi...">
                                @error('auditee')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- LINGKUP AUDIT -->
                            <div class="form-group">
                                <label class="form-label">Scope (Lingkup Audit) <span class="text-red-500">*</span></label>
                                <textarea name="lingkup_audit" class="form-textarea w-full" rows="3" placeholder="Lingkup Audit terhadap...">{{ old('lingkup_audit', $document->lingkup_audit) }}</textarea>
                                @error('lingkup_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- TUJUAN AUDIT -->
                            <div class="form-group">
                                <label class="form-label">Tujuan Audit <span class="text-red-500">*</span></label>
                                <textarea name="tujuan_audit" class="form-textarea w-full" rows="3" placeholder="Menilai efektivitas...">{{ old('tujuan_audit', $document->tujuan_audit) }}</textarea>
                                @error('tujuan_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- PERIODE AUDIT -->
                            <div class="form-group">
                                <label class="form-label">Periode Audit <span class="text-red-500">*</span></label>
                                <input type="text" name="periode_audit" class="form-input w-full" value="{{ old('periode_audit', $document->periode_audit) }}" placeholder="Semester I Tahun 2026">
                                @error('periode_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- WAKTU PELAKSANAAN & SAMPAI DENGAN (2 Input Kalender) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Waktu Pelaksanaan <span class="text-red-500">*</span></label>
                                    <input type="date" 
                                           name="jadwal_mulai" 
                                           x-model="jadwalMulai" 
                                           class="form-input w-full cursor-pointer" 
                                           value="{{ old('jadwal_mulai', $document->jadwal_mulai?->format('Y-m-d')) }}">
                                    @error('jadwal_mulai')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sampai Dengan <span class="text-red-500">*</span></label>
                                    <input type="date" 
                                           name="jadwal_selesai" 
                                           x-model="jadwalSelesai" 
                                           :min="jadwalMulai" 
                                           class="form-input w-full cursor-pointer" 
                                           value="{{ old('jadwal_selesai', $document->jadwal_selesai?->format('Y-m-d')) }}">
                                    @error('jadwal_selesai')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <!-- PREVIEW FORMAT WAKTU PELAKSANAAN DI DOKUMEN -->
                            <div x-show="jadwalMulai || jadwalSelesai || legacyJadwal" x-transition class="bg-primary-50/70 border border-primary-200/80 rounded-lg p-3 text-xs text-primary-900 flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <div>
                                    <span class="text-charcoal-light">Format Waktu Pelaksanaan di Dokumen:</span>
                                    <span class="font-bold text-primary-800 ml-1" x-text="formattedJadwal || '-'"></span>
                                </div>
                            </div>

                            <input type="hidden" name="jadwal_audit" :value="formattedJadwal">
                            @error('jadwal_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- PENUTUP & TANDA TANGAN -->
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden mt-6"
                         x-data="{
                             deskripsiPenutup: {{ json_encode(old('deskripsi_penutup', $document->deskripsi_penutup ?: 'Demikian surat tugas ini dibuat untuk dilaksanakan sebagaimana mestinya.'), JSON_UNESCAPED_SLASHES) }},
                             tglStg: {{ json_encode(old('tgl_stg', $document->tgl_stg?->format('Y-m-d') ?: date('Y-m-d')), JSON_UNESCAPED_SLASHES) }},
                             jabatanDirektur: {{ json_encode(old('jabatan_direktur', $document->jabatan_direktur ?? 'Plt. Direktur PT PEB'), JSON_UNESCAPED_SLASHES) }},
                             namaDirektur: {{ json_encode(old('nama_direktur', $document->nama_direktur ?? 'SAMSUL BAHRI'), JSON_UNESCAPED_SLASHES) }},
                             isSavingClosing: false,
                             closingSavedSuccess: false,
                             closingMessage: '',
                             
                             async saveClosingDefault() {
                                 this.isSavingClosing = true;
                                 this.closingSavedSuccess = false;
                                 this.closingMessage = '';
                                 try {
                                     const res = await fetch('{{ route('assurance.save-closing-template') }}', {
                                         method: 'POST',
                                         headers: {
                                             'Content-Type': 'application/json',
                                             'Accept': 'application/json',
                                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                         },
                                         body: JSON.stringify({
                                             audit_type: '{{ $document->audit_type }}',
                                             deskripsi_penutup: this.deskripsiPenutup,
                                             tgl_stg: this.tglStg,
                                             jabatan_direktur: this.jabatanDirektur,
                                             nama_direktur: this.namaDirektur
                                         })
                                     });
                                     const data = await res.json();
                                     if (data.success) {
                                         this.closingSavedSuccess = true;
                                         this.closingMessage = data.message || 'Format penutup & penandatangan berhasil disimpan sebagai default berikutnya.';
                                         setTimeout(() => { this.closingSavedSuccess = false; }, 6000);
                                     } else {
                                         alert(data.message || 'Gagal menyimpan format penutup.');
                                     }
                                 } catch (err) {
                                     console.error(err);
                                     alert('Terjadi kesalahan saat menyimpan format penutup.');
                                 } finally {
                                     this.isSavingClosing = false;
                                 }
                             }
                         }">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                                Penutup & Tanda Tangan
                            </h4>
                        </div>

                        {{-- Notifikasi Sukses Simpan Default Penutup --}}
                        <div x-show="closingSavedSuccess" x-collapse class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 flex items-center justify-between text-xs text-emerald-800">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="closingMessage"></span>
                            </div>
                            <button type="button" @click="closingSavedSuccess = false" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                        </div>

                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- DESKRIPSI PENUTUP -->
                            <div class="form-group md:col-span-2">
                                <label class="form-label flex items-center justify-between">
                                    <span>Deskripsi Penutup</span>
                                    <span class="text-[11px] text-charcoal-muted font-normal">(Dapat disesuaikan dan disimpan sebagai template)</span>
                                </label>
                                <textarea name="deskripsi_penutup" x-model="deskripsiPenutup" class="form-textarea w-full text-charcoal border-surface-300" rows="3">{{ old('deskripsi_penutup', $document->deskripsi_penutup ?: 'Demikian surat tugas ini dibuat untuk dilaksanakan sebagaimana mestinya.') }}</textarea>
                                @error('deskripsi_penutup')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            <!-- TTD DIREKTUR -->
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Tgl Surat</label>
                                <input type="date" name="tgl_stg" x-model="tglStg" value="{{ old('tgl_stg', $document->tgl_stg?->format('Y-m-d') ?: date('Y-m-d')) }}" class="form-input w-full md:w-1/2">
                                @error('tgl_stg')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jabatan Direktur</label>
                                <input type="text" name="jabatan_direktur" x-model="jabatanDirektur" value="{{ old('jabatan_direktur', $document->jabatan_direktur ?? 'Plt. Direktur PT PEB') }}" class="form-input">
                                @error('jabatan_direktur')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Direktur</label>
                                <input type="text" name="nama_direktur" x-model="namaDirektur" value="{{ old('nama_direktur', $document->nama_direktur ?? 'SAMSUL BAHRI') }}" class="form-input">
                                @error('nama_direktur')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            {{-- Action Bar di bawah Bagian 4 (Hanya 1 tombol di bawah sesuai instruksi) --}}
                            <div class="md:col-span-2 pt-3 border-t border-surface-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <p class="text-xs text-charcoal-muted">
                                    <span class="font-semibold text-charcoal">Keterangan:</span> Klik <strong>"Simpan Format Penutup & Penandatangan"</strong> agar isian ini otomatis muncul saat pembuatan surat tugas berikutnya tanpa reload halaman.
                                </p>
                                <button type="button" 
                                        @click="saveClosingDefault()" 
                                        :disabled="isSavingClosing"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer flex-shrink-0"
                                        :class="closingSavedSuccess ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300'">
                                    <template x-if="!isSavingClosing && !closingSavedSuccess">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    </template>
                                    <template x-if="isSavingClosing">
                                        <svg class="animate-spin w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <template x-if="closingSavedSuccess">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <span x-text="isSavingClosing ? 'Menyimpan format...' : (closingSavedSuccess ? 'Format Penutup Tersimpan!' : 'Simpan Format Penutup & Penandatangan')"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
            <div class="form-group mb-4">
                <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Contoh: Surat Tugas Audit — PT XYZ">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="content" class="form-textarea w-full h-32" placeholder="Tuliskan catatan tambahan di sini...">{!! old('content', strip_tags($document->content)) !!}</textarea>
                @error('content')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="p-5 bg-gradient-to-r from-primary-50/70 to-surface-50 border border-primary-200 rounded-xl shadow-sm mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <label class="form-label font-bold text-charcoal text-sm mb-0">File Dokumen / Tanda Tangan Basah (Opsional)</label>
                    <span class="badge bg-amber-100 text-amber-800 text-[11px] font-medium border-amber-200">Opsional</span>
                </div>
                <div class="mt-4">
                    <x-file-upload 
                        name="attachments" 
                        label="Lampiran Berkas Surat Tugas"
                        subtitle="Scan fisik bertanda tangan basah atau lampiran pendukung lainnya"
                        :required="false"
                        :multiple="true"
                        :existingFiles="$document->attachments"
                        badge="Opsional"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                    />
                    @error('attachments')<p class="form-error mt-1">{{ $message }}</p>@enderror
                    @error('attachment')<p class="form-error mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('assurance.show', $document) }}" class="btn-secondary">Kembali</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
