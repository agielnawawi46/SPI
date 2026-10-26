@extends('layouts.app')
@section('title', 'Buat Dokumen Assurance')
@section('page_title', 'Buat Dokumen Assurance')

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Buat Dokumen Baru &mdash; Audit {{ ucfirst(str_replace('_', ' ', $auditType)) }}</h2></div>
    
    <form action="{{ route('assurance.store') }}" method="POST" enctype="multipart/form-data" x-data="{ 
        selectedType: '{{ old('document_type_id') ?? ($selectedType?->id ?? '') }}', 
        pkaTypeId: '{{ $documentTypes->where('slug', 'program-kerja')->first()?->id ?? '' }}',
        slideEntryTypeId: '{{ $documentTypes->where('slug', 'slide-entry')->first()?->id ?? '' }}',
        kertasKerjaAuditTypeId: '{{ $documentTypes->where('slug', 'kertas-kerja-audit')->first()?->id ?? '' }}',
        lhaTypeId: '{{ $documentTypes->where('slug', 'lha')->first()?->id ?? '' }}',
        stgTypeId: '{{ $documentTypes->where('slug', 'surat-tugas')->first()?->id ?? '' }}'
    }">
        @csrf
        <div class="card-body space-y-6">
            <input type="hidden" name="audit_type" value="{{ $auditType }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                
                <!-- Hidden input for document_type_id as it is determined by the menu -->
                <input type="hidden" name="document_type_id" value="{{ $selectedType?->id ?? '' }}" x-model="selectedType">

                <!-- Generic Title -->
                <template x-if="selectedType != pkaTypeId && selectedType != slideEntryTypeId && selectedType != kertasKerjaAuditTypeId && selectedType != lhaTypeId && selectedType != stgTypeId">
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                        <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: Surat Tugas Audit â€” PT XYZ">
                        @error('title')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </template>

                <!-- PKA Specific Fields -->
                <template x-if="selectedType == pkaTypeId">
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">No PKA <span class="text-red-500">*</span></label>
                            <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: PKA/123/2026">
                            @error('title')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tgl PKA</label>
                            <input type="date" name="tgl_pka" class="form-input" value="{{ old('tgl_pka') }}">
                            @error('tgl_pka')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </template>

                <!-- Slide Entry Specific Fields -->
                <template x-if="selectedType == slideEntryTypeId">
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 form-group">
                            <label class="form-label">Auditee <span class="text-red-500">*</span></label>
                            <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: Divisi Keuangan">
                            @error('title')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tgl Entry</label>
                            <input type="date" name="tgl_entry" class="form-input" value="{{ old('tgl_entry') }}">
                            @error('tgl_entry')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </template>



                <!-- LHA Specific Fields -->
                <template x-if="selectedType == lhaTypeId">
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 form-group">
                            <label class="form-label">No LHA <span class="text-red-500">*</span></label>
                            <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: LHA/001/2026">
                            @error('title')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </template>

                                <!-- Surat Tugas & KKA Specific Fields -->
                <template x-if="selectedType == stgTypeId || selectedType == kertasKerjaAuditTypeId">
                    <div class="md:col-span-2 space-y-6">
                        <!-- BAGIAN 1: Informasi Dokumen & Header -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Informasi Dokumen & Header
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">No. Formulir</label>
                                <input type="text" name="header_text" class="form-input w-full" value="{{ old('header_text') }}" placeholder="Contoh: FMR-003-14">
                                @error('header_text')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal_formulir" class="form-input w-full" value="{{ old('tanggal_formulir', date('Y-m-d')) }}">
                                @error('tanggal_formulir')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                                <div class="form-group">
                                    <label class="form-label">Judul Surat</label>
                                    <input type="text" name="judul_dokumen" class="form-input" value="{{ old('judul_dokumen', 'SURAT TUGAS AUDIT') }}" placeholder="SURAT TUGAS AUDIT">
                                    @error('judul_dokumen')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">No STG <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: NO. 03.STA/VII/SPI-PEB/2026">
                                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">Deskripsi Pembuka</label>
                                    <textarea name="deskripsi_pembuka" class="form-textarea w-full bg-gray-100 cursor-not-allowed" rows="4" readonly>Dalam rangka melakukan audit rutin di lingkungan Perusahaan sesuai Program Kerja Pengawasan Tahunan (PKPT) Terintegrasi {{ date('Y') }} yang dilaksanakan oleh Tim SPI PT PEB, maka dengan ini kami menugaskan kepada :</textarea>
                                    @error('deskripsi_pembuka')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 2: Tim Audit -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden" x-data="{
                            rows: {{ old('tim_audit') ? json_encode(old('tim_audit')) : "[{nama: '', nip: '', jabatan_pegawai: '', jabatan_tim: 'Ketua Tim'}]" }}
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
                                        <button type="button" @click="rows.splice(index, 1)" x-show="rows.length > 1" class="absolute top-2 right-2 text-red-500 hover:bg-red-50 p-1.5 rounded-md transition-colors" title="Hapus Anggota">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="bg-primary-100 text-primary-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" x-text="index + 1"></span>
                                            <span class="text-sm font-semibold text-charcoal">Anggota Tim</span>
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
                                
                                <button type="button" @click="rows.push({nama: '', nip: '', jabatan_pegawai: '', jabatan_tim: 'Anggota Tim'})" class="w-full py-3 bg-white border-2 border-dashed border-primary-200 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors font-medium text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Anggota Tim
                                </button>
                                @error('tim_audit')<p class="form-error mt-2">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- BAGIAN 3: Rincian Penugasan -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    Rincian Penugasan
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Audit Universe (Auditee) <span class="text-red-500">*</span></label>
                                    <input type="text" name="auditee" class="form-input w-full" value="{{ old('auditee') }}" placeholder="Contoh: Divisi Operasi...">
                                    @error('auditee')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Scope (Lingkup Audit) <span class="text-red-500">*</span></label>
                                    <textarea name="lingkup_audit" class="form-textarea w-full" rows="3" placeholder="Lingkup Audit terhadap...">{{ old('lingkup_audit') }}</textarea>
                                    @error('lingkup_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tujuan Audit <span class="text-red-500">*</span></label>
                                    <textarea name="tujuan_audit" class="form-textarea w-full" rows="3" placeholder="Menilai efektivitas...">{{ old('tujuan_audit') }}</textarea>
                                    @error('tujuan_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Periode Audit <span class="text-red-500">*</span></label>
                                    <input type="text" name="periode_audit" class="form-input w-full" value="{{ old('periode_audit') }}" placeholder="Semester I Tahun 2026">
                                    @error('periode_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Waktu Pelaksanaan Audit <span class="text-red-500">*</span></label>
                                    <input type="text" name="jadwal_audit" class="form-input w-full" value="{{ old('jadwal_audit') }}" placeholder="Contoh: 03 Agustus s.d. 11 September 2026">
                                    @error('jadwal_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 4: Penandatangan & Penutup -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Penandatangan & Penutup
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">Deskripsi Penutup</label>
                                    <textarea name="deskripsi_penutup" class="form-textarea w-full bg-gray-100 cursor-not-allowed" rows="3" readonly>Demikian surat tugas ini dibuat untuk dilaksanakan sebagaimana mestinya.</textarea>
                                    @error('deskripsi_penutup')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tgl Surat</label>
                                    <input type="date" name="tgl_stg" class="form-input" value="{{ old('tgl_stg') }}">
                                    @error('tgl_stg')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Jabatan Direktur</label>
                                    <input type="text" name="jabatan_direktur" class="form-input" value="{{ old('jabatan_direktur', 'Plt. Direktur PT PEB') }}">
                                    @error('jabatan_direktur')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nama Direktur</label>
                                    <input type="text" name="nama_direktur" class="form-input" value="{{ old('nama_direktur', 'SAMSUL BAHRI') }}">
                                    @error('nama_direktur')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="md:col-span-2 border-t border-surface-200 pt-6 mt-4">
                    <div class="p-5 bg-gradient-to-r from-primary-50/70 to-surface-50 border border-primary-200 rounded-xl shadow-sm">
                        <div class="flex items-start gap-3.5">
                            <div class="p-2.5 bg-primary-100 text-primary-700 rounded-xl flex-shrink-0 mt-0.5 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <label for="attachment" class="form-label font-bold text-charcoal text-sm mb-0">
                                        Upload File Surat Tugas / Tanda Tangan Basah
                                    </label>
                                    <span class="badge bg-amber-100 text-amber-800 text-[11px] font-medium border-amber-200">Opsional</span>
                                </div>
                                <p class="text-xs text-charcoal-muted leading-relaxed mb-3">
                                    Jika surat tugas sudah memiliki fisik bertanda tangan basah dan hanya perlu diunggah, silakan unggah scan berkas di bawah ini. Anda dapat tetap mengisi form di atas atau membiarkan opsional sesuai kebutuhan.
                                </p>
                                <input type="file" name="attachment" id="attachment" class="form-input bg-white w-full p-2 border-surface-300 rounded-lg focus:border-primary-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                <p class="text-[11px] text-charcoal-muted mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Format didukung: <strong>PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</strong>
                                </p>
                                @error('attachment')<p class="form-error mt-1.5">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="alert alert-info border-l-4">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold">Informasi</h3>
                    <p class="text-xs mt-1 text-blue-700">Setelah dokumen dibuat, Anda masih dapat mengubah data dan menambah lampiran pendukung lainnya pada halaman detail dokumen.</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('assurance.index', ['audit_type' => $auditType, 'type' => request('type')]) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat Dokumen</button>
        </div>
    </form>
</div>
@endsection




