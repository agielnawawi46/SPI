@extends('layouts.app')
@section('title', 'Edit Dokumen Advisory')
@section('page_title', 'Edit Dokumen Advisory')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Edit Dokumen: {{ $document->title }}</h2>
        <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
    </div>
    <div class="card-body">
        <form action="{{ route('advisory.update', $document) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @if($document->documentType?->slug === 'bak')
                <div class="flex flex-col gap-4 mb-4">
                    <div class="md:col-span-2 form-group border border-surface-200 rounded-lg p-4 bg-surface-50">
                        <h4 class="font-medium text-charcoal mb-3">Header dan Footer</h4>
                        <div class="flex flex-col gap-4">
                            <div class="form-group">
                                <label class="form-label">Nomor Formulir</label>
                                <input type="text" name="nomor_formulir" class="form-input" value="{{ old('nomor_formulir', $document->nomor_formulir ?? 'FMR-003-06') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Formulir</label>
                                <input type="date" name="tanggal_formulir" class="form-input" value="{{ old('tanggal_formulir', $document->tanggal_formulir ?? date('Y-m-d')) }}">
                            </div>
                            <!-- Removed header and footer image inputs -->
                        </div>
                    </div>

                    <div class="md:col-span-2 form-group border border-primary-200 rounded-lg p-4 bg-primary-50 mt-4">
                        <h4 class="font-medium text-primary-800 mb-3">Informasi Halaman 1 (Cover Dokumen)</h4>
                        <div class="flex flex-col gap-4">
                            <div class="form-group">
                                <label class="form-label">Judul Dokumen</label>
                                <input type="text" name="judul_dokumen" class="form-input" value="{{ old('judul_dokumen', $document->judul_dokumen ?? 'BERITA ACARA KONSULTANSI') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Bidang</label>
                                <input type="text" name="auditee" class="form-input" value="{{ old('auditee', $document->auditee) }}" placeholder="Contoh: DIVISI KEUANGAN">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pilih Manager Auditee (Untuk Approval) <span class="text-red-500">*</span></label>
                                <select name="auditee_id" class="form-select" required>
                                    <option value="">-- Pilih Manager Auditee --</option>
                                    @foreach($auditeeManagers as $manager)
                                        <option value="{{ $manager->id }}" {{ old('auditee_id', $document->auditee_id) == $manager->id ? 'selected' : '' }}>{{ $manager->name }} ({{ $manager->email }})</option>
                                    @endforeach
                                </select>
                                @error('auditee_id')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor Dokumen <span class="text-red-500">*</span></label>
                                <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Contoh: XX/FM-KONS/BULAN/TAHUN">
                                @error('title')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Dokumen</label>
                                <input type="date" name="tgl_stg" class="form-input" value="{{ old('tgl_stg', $document->tgl_stg ? $document->tgl_stg->format('Y-m-d') : date('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <label class="form-label">Nama / Jabatan Pimpinan Klien</label>
                        <input type="text" name="nama_jabatan" class="form-input" value="{{ old('nama_jabatan', $document->nama_jabatan) }}" placeholder="Contoh: Budi Santoso / Manager IT">
                        @error('nama_jabatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nomor Induk Pegawai (NIP)</label>
                        <input type="text" name="nip" class="form-input" value="{{ old('nip', $document->nip) }}" placeholder="NIP">
                        @error('nip')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">Bidang Tugas (Bidang/Satuan/Tim Kerja)</label>
                        <input type="text" name="bidang_tugas" class="form-input" value="{{ old('bidang_tugas', $document->bidang_tugas) }}" placeholder="Contoh: Divisi IT">
                        @error('bidang_tugas')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="md:col-span-2 form-group border-t border-surface-200 pt-4 mt-2">
                        <label class="form-label">Deskripsi Permasalahan</label>
                        <textarea name="deskripsi_permasalahan" class="form-textarea w-full" rows="4" placeholder="Jelaskan permasalahan yang ada...">{{ old('deskripsi_permasalahan', $document->deskripsi_permasalahan) }}</textarea>
                        @error('deskripsi_permasalahan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">Tujuan Konsultasi</label>
                        <textarea name="tujuan_konsultasi" class="form-textarea w-full" rows="3" placeholder="Tujuan yang ingin dicapai...">{{ old('tujuan_konsultasi', $document->tujuan_konsultasi) }}</textarea>
                        @error('tujuan_konsultasi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Waktu penyelesaian yang dibutuhkan</label>
                        <input type="text" name="waktu_penyelesaian" class="form-input" value="{{ old('waktu_penyelesaian', $document->waktu_penyelesaian) }}" placeholder="Contoh: 1 Minggu">
                        @error('waktu_penyelesaian')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">Pengungkapan Potensi Pengurangan Independensi dan Objektifitas Auditor</label>
                        <textarea name="pengungkapan" class="form-textarea w-full" rows="3" placeholder="...">{{ old('pengungkapan', $document->pengungkapan) }}</textarea>
                        @error('pengungkapan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    </div>
                    
                    <div class="md:col-span-2 form-group border-t border-surface-200 pt-4 mt-2">
                        <h4 class="text-md font-bold mb-3 text-[#0a2342]">Penandatangan Dokumen</h4>
                        <div class="flex flex-col gap-4">
                            <div class="form-group">
                                <label class="form-label">Nama KSPI</label>
                                <input type="text" name="nama_kspi" class="form-input" value="{{ old('nama_kspi', $document->nama_kspi ?? '') }}" placeholder="Contoh: M. ACHYAR">
                                @error('nama_kspi')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Pimpinan Klien</label>
                                <input type="text" name="nama_pimpinan_klien" class="form-input" value="{{ old('nama_pimpinan_klien', $document->nama_pimpinan_klien ?? (explode('/', $document->nama_jabatan)[0] ?? '')) }}" placeholder="Contoh: Budi Santoso">
                                @error('nama_pimpinan_klien')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="form-group mb-4">
                    <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Contoh: Laporan Konsultasi Divisi IT">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            @endif
            
            @if($document->documentType?->slug !== 'bak')
            <div class="form-group border-t border-surface-200 pt-6">
                <h3 class="text-sm font-semibold text-charcoal mb-4">Timpa Lampiran (Opsional)</h3>
                <div class="alert alert-warning text-xs mb-3">
                    Upload file baru hanya jika Anda ingin mengganti file lampiran yang sebelumnya telah diunggah.
                </div>
                <input type="file" name="attachment" class="form-input p-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                <p class="text-xs text-charcoal-muted mt-1">Format: PDF, Word, Excel, Gambar. Maks: 300MB.</p>
                @error('attachment')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @endif
            
            <div class="flex justify-between mt-8">
                <a href="{{ route('advisory.show', $document) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection


