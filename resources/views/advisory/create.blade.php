@extends('layouts.app')
@section('title', 'Buat Dokumen Advisory')
@section('page_title', 'Buat Dokumen Advisory')

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Buat Dokumen Baru — {{ $selectedType?->name ?? 'Layanan Advisory' }}</h2></div>
    
    <form action="{{ route('advisory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body space-y-4">
            <div class="flex flex-col gap-4 mb-4">
                @if($selectedType)
                    <!-- Hidden input for document_type_id as it is determined by the menu -->
                    <input type="hidden" name="document_type_id" value="{{ $selectedType->id }}">
                @else
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">Jenis Dokumen <span class="text-red-500">*</span></label>
                        <select name="document_type_id" class="form-select">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('document_type_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                @endif

                                @if($selectedType && $selectedType->slug === 'bak')
                    <div class="md:col-span-2 space-y-6">
                        <!-- BAGIAN 1: Informasi Formulir & Dokumen -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Informasi Formulir & Dokumen
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Nomor Formulir</label>
                                    <input type="text" name="nomor_formulir" class="form-input" value="{{ old('nomor_formulir', 'FMR-003-06') }}" placeholder="Contoh: FMR-003-06">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal Formulir</label>
                                    <input type="date" name="tanggal_formulir" class="form-input" value="{{ old('tanggal_formulir', date('Y-m-d')) }}">
                                </div>
                                
                                <div class="form-group md:col-span-2 border-t border-surface-100 pt-4 mt-2">
                                    <label class="form-label">Judul Dokumen</label>
                                    <input type="text" name="judul_dokumen" class="form-input" value="{{ old('judul_dokumen', 'BERITA ACARA KONSULTANSI') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nomor Dokumen <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: XX/FM-KONS/BULAN/TAHUN">
                                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal Dokumen</label>
                                    <input type="date" name="tgl_stg" class="form-input" value="{{ old('tgl_stg', date('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 2: Pihak Terlibat -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Pihak Terlibat (Klien / Auditee)
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Manager Klien / Auditee (Untuk Approval) <span class="text-red-500">*</span></label>
                                    <select name="auditee_id" class="form-select" required>
                                        <option value="">-- Pilih Manager --</option>
                                        @foreach($auditeeManagers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('auditee_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }} ({{ $manager->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('auditee_id')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nama Bidang / Divisi Klien</label>
                                    <input type="text" name="auditee" class="form-input" value="{{ old('auditee') }}" placeholder="Contoh: DIVISI KEUANGAN">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nama & Jabatan Pimpinan Klien</label>
                                    <input type="text" name="nama_jabatan" class="form-input" value="{{ old('nama_jabatan') }}" placeholder="Contoh: Budi Santoso / Manager IT">
                                    @error('nama_jabatan')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nomor Induk Pegawai (NIP)</label>
                                    <input type="text" name="nip" class="form-input" value="{{ old('nip') }}" placeholder="NIP">
                                    @error('nip')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 3: Detail Konsultasi -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    Detail Konsultasi
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <div class="form-group md:w-1/2">
                                    <label class="form-label">Bidang Tugas (Bidang/Satuan/Tim Kerja)</label>
                                    <input type="text" name="bidang_tugas" class="form-input" value="{{ old('bidang_tugas') }}" placeholder="Contoh: Divisi IT">
                                    @error('bidang_tugas')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Deskripsi Permasalahan</label>
                                    <textarea name="deskripsi_permasalahan" class="form-textarea w-full" rows="3" placeholder="Jelaskan permasalahan yang ada...">{{ old('deskripsi_permasalahan') }}</textarea>
                                    @error('deskripsi_permasalahan')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tujuan Konsultasi</label>
                                    <textarea name="tujuan_konsultasi" class="form-textarea w-full" rows="3" placeholder="Tujuan yang ingin dicapai...">{{ old('tujuan_konsultasi') }}</textarea>
                                    @error('tujuan_konsultasi')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group md:w-1/2">
                                    <label class="form-label">Waktu Penyelesaian yang Dibutuhkan</label>
                                    <input type="text" name="waktu_penyelesaian" class="form-input" value="{{ old('waktu_penyelesaian') }}" placeholder="Contoh: 1 Minggu">
                                    @error('waktu_penyelesaian')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pengungkapan Potensi Pengurangan Independensi dan Objektifitas</label>
                                    <textarea name="pengungkapan" class="form-textarea w-full" rows="2" placeholder="...">{{ old('pengungkapan') }}</textarea>
                                    @error('pengungkapan')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 4: Penandatangan -->
                        <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                                <h4 class="font-bold text-charcoal flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Penandatangan Dokumen
                                </h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-surface-50">
                                <div class="form-group">
                                    <label class="form-label">Nama KSPI</label>
                                    <input type="text" name="nama_kspi" class="form-input" value="{{ old('nama_kspi') }}" placeholder="Contoh: M. ACHYAR">
                                    @error('nama_kspi')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nama Pimpinan Klien</label>
                                    <input type="text" name="nama_pimpinan_klien" class="form-input" value="{{ old('nama_pimpinan_klien') }}" placeholder="Contoh: Budi Santoso">
                                    @error('nama_pimpinan_klien')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">
                            @if($selectedType && $selectedType->slug === 'lap-konsultasi')
                                Nomor / Judul Laporan <span class="text-red-500">*</span>
                            @else
                                Judul Dokumen <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: Laporan Konsultasi / Nomor Dokumen">
                        @error('title')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="md:col-span-2 pt-2">
                        <x-file-upload 
                            name="attachments" 
                            label="File Lampiran Dokumen"
                            subtitle="Pilih satu atau beberapa berkas dokumen pendukung (bisa pilih lebih dari 1 file)"
                            :required="false"
                            :multiple="true"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                        />
                        @error('attachments')<p class="form-error mt-1">{{ $message }}</p>@enderror
                        @error('attachment')<p class="form-error mt-1">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>
            <div class="alert alert-info border-l-4">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold">Informasi</h3>
                    <p class="text-xs mt-1 text-blue-700">Setelah dokumen dibuat, Anda dapat mengirimkannya (submit) untuk direview oleh KSPI pada halaman detail dokumen.</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('advisory.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat Dokumen</button>
        </div>
    </form>
</div>
@endsection


