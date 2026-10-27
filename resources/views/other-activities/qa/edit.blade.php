@extends('layouts.app')
@section('title', 'Edit ' . $document->title)
@section('page_title', 'Edit Dokumen QA')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.qa.index', ['type' => $document->documentType?->slug]) }}" class="breadcrumb-link">Quality Assurance</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.qa.show', $document) }}" class="breadcrumb-link">{{ $document->title }}</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Edit: {{ $document->title }}</h2>
        <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
    </div>
    <form action="{{ route('other-activities.qa.update', $document) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
    <div class="card-body space-y-6">
            @if(in_array($document->documentType?->slug, ['pk-qa', 'kk-qa', 'lap-qa']))
            {{-- Upload-only types --}}
            <div class="grid grid-cols-1 gap-4 mb-4">
                <div class="form-group">
                    <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Masukkan judul dokumen">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <x-file-upload 
                    name="attachments"
                    label="Unggah Berkas Baru"
                    badge="Opsional"
                    :multiple="true"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                    hint="Pilih satu atau beberapa berkas untuk ditambahkan (PDF, Word, Excel, Gambar - Ukuran bebas / tanpa batas)"
                    :existingFiles="$document->attachments"
                    :document="$document"
                    :required="false"
                />
            </div>

            @elseif($document->documentType?->slug === 'st-qa')
            {{-- Surat Tugas QA: Full form --}}
            <div class="mb-4">
                {{-- HEADER --}}
                <div class="md:col-span-2 space-y-6">
                    {{-- BAGIAN 1: Informasi Dokumen & Header --}}
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
                                <input type="text" name="header_text" class="form-input w-full" value="{{ old('header_text', $document->header_text) }}" placeholder="Contoh: FMR-003-14">
                                @error('header_text')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Formulir</label>
                                <input type="date" name="tanggal_formulir" class="form-input w-full" value="{{ old('tanggal_formulir', $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->format('Y-m-d') : null) }}">
                                @error('tanggal_formulir')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Judul Surat</label>
                                <input type="text" name="judul_dokumen" class="form-input" value="{{ old('judul_dokumen', $document->judul_dokumen ?? 'SURAT TUGAS AUDIT QA') }}" placeholder="SURAT TUGAS AUDIT QA">
                                @error('judul_dokumen')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">No STQ <span class="text-red-500">*</span></label>
                                <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Contoh: NO. 01.STQ/VII/SPI-PEB/2026">
                                @error('title')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Deskripsi Pembuka</label>
                                <textarea name="deskripsi_pembuka" class="form-textarea w-full bg-surface-100 text-charcoal border-surface-200 cursor-not-allowed" rows="4" readonly>Dalam rangka melaksanakan quality assurance di lingkungan Perusahaan sesuai kriteria yang berlaku, maka dengan ini ditugaskan kepada :</textarea>
                                @error('deskripsi_pembuka')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN 2: Tim QA --}}
                    @php $timAudit = $document->tim_audit ?? []; @endphp
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden" x-data="{
                        rows: {{ old('tim_audit') ? json_encode(old('tim_audit')) : (count($timAudit) > 0 ? json_encode($timAudit) : '[{nama: \'\', nip: \'\', jabatan_pegawai: \'\', jabatan_tim: \'Ketua Tim\'}]') }}
                    }">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Susunan Tim QA
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
                            <button type="button" @click="rows.push({nama: '', nip: '', jabatan_pegawai: '', jabatan_tim: 'Anggota Tim'})" class="w-full py-3 border-2 border-dashed border-primary-200 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors font-medium text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Anggota Tim
                            </button>
                            @error('tim_audit')<p class="form-error mt-2">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- BAGIAN 3: Rincian Penugasan --}}
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                Rincian Penugasan QA
                            </h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 gap-4 bg-surface-50">
                            <div class="form-group">
                                <label class="form-label">Periode QA <span class="text-red-500">*</span></label>
                                <input type="text" name="periode_audit" class="form-input w-full" value="{{ old('periode_audit', $document->periode_audit) }}" placeholder="Semester I Tahun 2026">
                                @error('periode_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Lingkup QA <span class="text-red-500">*</span></label>
                                <textarea name="lingkup_audit" class="form-textarea w-full" rows="3" placeholder="Lingkup QA terhadap...">{{ old('lingkup_audit', $document->lingkup_audit) }}</textarea>
                                @error('lingkup_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tujuan QA <span class="text-red-500">*</span></label>
                                <textarea name="tujuan_audit" class="form-textarea w-full" rows="3" placeholder="Menilai kualitas pelaksanaan audit...">{{ old('tujuan_audit', $document->tujuan_audit) }}</textarea>
                                @error('tujuan_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Waktu Pelaksanaan QA <span class="text-red-500">*</span></label>
                                <input type="text" name="jadwal_audit" class="form-input w-full" value="{{ old('jadwal_audit', $document->jadwal_audit) }}" placeholder="Contoh: 03 Agustus s.d. 11 September 2026">
                                @error('jadwal_audit')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN 4: Footer Dokumen --}}
                    <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden mt-6">
                        <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                            <h4 class="font-bold text-charcoal flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                                Penutup & Tanda Tangan
                            </h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- DESKRIPSI PENUTUP --}}
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Deskripsi Penutup</label>
                                <textarea name="deskripsi_penutup" class="form-textarea w-full bg-surface-100 text-charcoal border-surface-200 cursor-not-allowed" rows="5" readonly>Demikian surat tugas ini dibuat untuk dilaksanakan sebagaimana mestinya.</textarea>
                                @error('deskripsi_penutup')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            {{-- TTD --}}
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Tgl Surat</label>
                                <input type="date" name="tgl_stg" class="form-input w-full md:w-1/2" value="{{ old('tgl_stg', $document->tgl_stg?->format('Y-m-d')) }}">
                                @error('tgl_stg')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jabatan Direktur</label>
                                <input type="text" name="jabatan_direktur" class="form-input" value="{{ old('jabatan_direktur', $document->jabatan_direktur ?? 'Plt. Direktur PT PEB') }}">
                                @error('jabatan_direktur')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Direktur</label>
                                <input type="text" name="nama_direktur" class="form-input" value="{{ old('nama_direktur', $document->nama_direktur ?? 'SAMSUL BAHRI') }}">
                                @error('nama_direktur')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @else
            <div class="form-group mb-4">
                <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Judul dokumen QA">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @endif

            @if(!in_array($document->documentType?->slug, ['pk-qa', 'kk-qa', 'lap-qa']))
            <div class="form-group">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="content" class="form-textarea w-full h-32" placeholder="Tuliskan catatan tambahan di sini...">{!! old('content', strip_tags($document->content)) !!}</textarea>
                @error('content')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="border-t border-surface-200 pt-6">
                <x-file-upload 
                    name="attachments"
                    label="Unggah Berkas Lampiran"
                    badge="Opsional"
                    :multiple="true"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                    hint="Pilih satu atau beberapa berkas untuk ditambahkan (PDF, Word, Excel, Gambar - Ukuran bebas / tanpa batas)"
                    :existingFiles="$document->attachments"
                    :document="$document"
                    :required="false"
                />
            </div>
            @endif
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('other-activities.qa.show', $document) }}" class="btn-secondary">Kembali</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
