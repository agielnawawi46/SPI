@extends('layouts.app')
@section('title', 'Buat Monitoring Tindak Lanjut')
@section('page_title', 'Buat Monitoring Tindak Lanjut')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.tl.index') }}" class="breadcrumb-link">Monitoring Tindak Lanjut</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Buat Dokumen</span>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Buat Dokumen Monitoring Tindak Lanjut</h2>
        </div>
        <div class="card-body space-y-6">
            <form action="{{ route('other-activities.tl.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @php
                    $userOptionsHtml = '<option value="">-- Pilih Akun --</option>';
                    if(isset($users)) {
                        foreach($users as $u) {
                            $userOptionsHtml .= '<option value="'.htmlspecialchars($u->email).'" data-name="'.htmlspecialchars($u->name).'">'.htmlspecialchars($u->name).'</option>';
                        }
                    }
                @endphp
                <input type="hidden" name="document_type_id" value="{{ $documentType->id }}">
                <!-- Header dan Judul -->
                <div class="border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                    <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                        <h4 class="font-bold text-charcoal flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Informasi Formulir & Dokumen
                        </h4>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label" for="nomor_formulir">No. Formulir <span class="text-xs text-gray-500">(Opsional)</span></label>
                            <input type="text" name="nomor_formulir" id="nomor_formulir" class="form-input" value="{{ old('nomor_formulir', 'FMR-003-12') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="tanggal_formulir">Tanggal Formulir <span class="text-xs text-gray-500">(Opsional)</span></label>
                            <input type="date" name="tanggal_formulir" id="tanggal_formulir" class="form-input" value="{{ old('tanggal_formulir', date('Y-m-d')) }}">
                        </div>
                        
                        <div class="form-group md:col-span-2 lg:col-span-3 border-t border-surface-100 pt-4 mt-2">
                            <label class="form-label" for="header_text">Nama Instansi/PT <span class="text-red-500">*</span></label>
                            <input type="text" name="header_text" id="header_text" class="form-input" value="{{ old('header_text', 'PT PELAYANAN ENERGI BATAM') }}" required placeholder="Contoh: PT PELAYANAN ENERGI BATAM">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="judul_dokumen">Judul Dokumen <span class="text-red-500">*</span></label>
                            <input type="text" name="judul_dokumen" id="judul_dokumen" class="form-input" value="{{ old('judul_dokumen', 'MONITORING DAN EVALUASI TINDAK LANJUT') }}" required placeholder="MONITORING DAN EVALUASI TINDAK LANJUT">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="periode_audit">Berlaku Sampai Dengan</label>
                            <input type="date" name="periode_audit" id="periode_audit" class="form-input" value="{{ old('periode_audit') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="title">Nomor Dokumen <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" class="form-input" value="{{ old('title') }}" required placeholder="Nomor : XXXX">
                        </div>
                        <div class="form-group md:col-span-2 lg:col-span-3">
                            <label class="form-label" for="deskripsi_pembuka">Teks Di Atas Tabel <span class="text-xs text-gray-500">(Opsional)</span></label>
                            <input type="text" name="deskripsi_pembuka" id="deskripsi_pembuka" class="form-input" value="{{ old('deskripsi_pembuka') }}" placeholder="Contoh: Audit Kepatuhan & Operasional">
                        </div>
                    </div>
                </div>

                <!-- Tabel Dinamis Isi -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Isi Tabel Temuan</h3>
                        <button type="button" id="addRow" class="btn-secondary text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Baris
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-surface-200 shadow-2xs">
                        <table class="w-full text-sm text-left text-charcoal border-collapse" id="dynamicTable">
                            <thead class="text-xs uppercase bg-surface-100 text-charcoal border-b border-surface-200">
                                <tr>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">LHA</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">Sebab</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">Akibat</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">Rekomendasi</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">PIC TL & Email</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">Evidence TL</th>
                                    <th colspan="3" class="border-r border-surface-200 p-2 text-center font-semibold bg-surface-200/50">Status</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">Evaluasi TL</th>
                                    <th rowspan="2" class="border-r border-surface-200 p-2.5 text-center font-semibold">Jatuh Tempo</th>
                                    <th rowspan="2" class="p-2.5 text-center font-semibold">Aksi</th>
                                </tr>
                                <tr class="bg-surface-50 text-[11px] border-t border-surface-200">
                                    <th class="border-r border-surface-200 p-1.5 text-center font-semibold">Closed</th>
                                    <th class="border-r border-surface-200 p-1.5 text-center font-semibold">On Progress</th>
                                    <th class="border-r border-surface-200 p-1.5 text-center font-semibold">TDD</th>
                                </tr>
                            </thead>
                            <tbody class="group-body" data-group-index="0">
                                <tr class="bg-surface-50 border-b border-surface-200 group-header-row">
                                    <td colspan="12" class="px-4 py-2 font-bold text-primary-700">
                                        <div class="flex justify-between items-center">
                                            <span class="group-number-label flex items-center gap-2">
                                                <span class="bg-primary-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs">#</span>
                                                Nomor 1
                                            </span>
                                            <div class="space-x-3">
                                                <button type="button" class="text-primary-600 hover:text-primary-800 text-[11px] uppercase tracking-wider font-bold addSubRow transition-colors">
                                                    + Tambah Sub-Baris
                                                </button>
                                                <button type="button" class="text-red-500 hover:text-red-700 text-[11px] uppercase tracking-wider font-bold removeGroup hidden transition-colors">
                                                    - Hapus Nomor Ini
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Initial Row -->
                                <tr class="item-row hover:bg-surface-50 transition-colors">
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][lha]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly required placeholder="Klik utk edit..."></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][sebab]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][akibat]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][rekomendasi]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5">
                                        <div class="space-y-1">
                                            <textarea name="items[0][rows][0][pic_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="1" readonly placeholder="Nama PIC..."></textarea>
                                            <select name="items[0][rows][0][pic_email]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm">
                                                <option value="">-- Email PIC --</option>
                                                {!! $userOptionsHtml !!}
                                            </select>
                                        </div>
                                    </td>
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][evidence_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                                    
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][status_closed]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][status_on_progress]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][status_tdd]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                                    
                                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[0][rows][0][evaluasi_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                                    <td class="border-r border-surface-200 p-1.5"><input type="date" name="items[0][rows][0][jatuh_tempo]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm"></td>
                                    <td class="p-1.5 text-center align-middle">
                                        <button type="button" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md transition-colors removeRow" title="Hapus Baris">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pengaturan Tanda Tangan -->
                <div class="mt-8 border border-surface-200 rounded-xl bg-white shadow-sm overflow-hidden">
                    <div class="bg-surface-100 px-4 py-3 border-b border-surface-200">
                        <h4 class="font-bold text-charcoal flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Pengaturan Tanda Tangan
                        </h4>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6 bg-surface-50">
                        <!-- Auditor (Kiri) -->
                        <div class="bg-white p-4 border border-surface-200 rounded-lg shadow-sm">
                            <h4 class="font-medium text-charcoal mb-3 flex items-center gap-2">
                                <span class="bg-primary-100 text-primary-700 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                Tanda Tangan Kiri (Auditor)
                            </h4>
                            <div class="space-y-3">
                                <input type="hidden" name="bidang_tugas" id="bidang_tugas" value="AUDITOR">
                                <div>
                                    <label class="form-label" for="disusun_oleh">Nama Auditor <span class="text-red-500">*</span></label>
                                    <input type="text" name="disusun_oleh" id="disusun_oleh" class="form-input" value="{{ old('disusun_oleh', auth()->user()->name) }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- KSPI (Kanan) -->
                        <div class="bg-white p-4 border border-surface-200 rounded-lg shadow-sm">
                            <h4 class="font-medium text-charcoal mb-3 flex items-center gap-2">
                                <span class="bg-primary-100 text-primary-700 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                Tanda Tangan Kanan (KSPI)
                            </h4>
                            <div class="space-y-3">
                                <input type="hidden" name="nama_jabatan" id="nama_jabatan" value="KSPI">
                                <div>
                                    <label class="form-label" for="nama_kspi">Nama KSPI <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_kspi" id="nama_kspi" class="form-input" value="{{ old('nama_kspi', \App\Models\User::role('kspi')->first()?->name ?? 'KSPI') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 mt-6">
                    <a href="{{ route('other-activities.tl.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Simpan Draft
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<dialog id="nativeModal" class="p-0 rounded-2xl shadow-2xl border border-surface-200/90 w-full max-w-2xl bg-white backdrop:bg-charcoal/60 backdrop:backdrop-blur-sm overflow-hidden" style="position: fixed; inset: 0; margin: auto; z-index: 2147483647;">
    {{-- Modal Header --}}
    <div class="modal-header bg-surface-50/80 border-b border-surface-200/80 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary-50 border border-primary-200 text-primary-600 flex items-center justify-center flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-charcoal leading-tight" id="nativeModalTitle">Edit Kolom</h3>
                <p class="text-xs text-charcoal-muted mt-0.5">Tulis atau perbarui rincian teks untuk kolom tabel ini.</p>
            </div>
        </div>
        <button type="button" id="nativeModalXClose" class="w-8 h-8 rounded-lg text-charcoal-muted hover:text-charcoal hover:bg-surface-200/60 flex items-center justify-center transition-colors" title="Tutup (ESC)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Modal Body --}}
    <div class="modal-body space-y-3 p-6 bg-white">
        <div class="relative">
            <textarea id="nativeModalTextarea" 
                      rows="8"
                      placeholder="Tuliskan keterangan / rincian teks di sini..."
                      class="w-full p-3.5 border border-surface-300 rounded-xl form-textarea focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-sm leading-relaxed text-charcoal shadow-2xs transition-all placeholder:text-charcoal-muted/60" 
                      style="min-height: 220px; resize: vertical;"></textarea>
        </div>
        <div class="flex items-center justify-between text-xs text-charcoal-muted pt-0.5">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tip: Tekan <kbd class="px-1.5 py-0.5 bg-surface-100 border border-surface-200 rounded text-[11px] font-mono text-charcoal font-medium">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 bg-surface-100 border border-surface-200 rounded text-[11px] font-mono text-charcoal font-medium">Enter</kbd> untuk simpan cepat
            </span>
            <span id="nativeModalCharCount" class="font-mono text-[11px] font-medium text-charcoal-muted">0 karakter</span>
        </div>
    </div>

    {{-- Modal Footer --}}
    <div class="modal-footer px-6 py-4 border-t border-surface-200/80 bg-surface-50 flex items-center justify-end gap-3">
        <button type="button" id="nativeModalClose" class="btn-secondary">Batal</button>
        <button type="button" id="nativeModalSave" class="btn-primary flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Teks
        </button>
    </div>
</dialog>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let groupCount = 1;

        function updateGroupNumbers() {
            document.querySelectorAll('.group-body').forEach((group, index) => {
                group.dataset.groupIndex = index;
                group.querySelector('.group-number-label').textContent = 'Nomor ' + (index + 1);
                
                // Hide remove button if only 1 group exists
                const removeBtn = group.querySelector('.removeGroup');
                if (document.querySelectorAll('.group-body').length > 1) {
                    removeBtn.classList.remove('hidden');
                } else {
                    removeBtn.classList.add('hidden');
                }

                // Update input names for all rows in this group
                group.querySelectorAll('.item-row').forEach((row, rowIndex) => {
                    updateRowInputNames(row, index, rowIndex);
                });
            });
        }

        function updateRowInputNames(row, gIndex, rIndex) {
            row.querySelectorAll('textarea, input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/items\[\d+\]\[rows\]\[\d+\]/, `items[${gIndex}][rows][${rIndex}]`);
                    input.setAttribute('name', newName);
                }
            });
        }

        // Add new Group (Nomor)
        document.getElementById('addRow').addEventListener('click', function () {
            const table = document.getElementById('dynamicTable');
            const tbody = document.createElement('tbody');
            tbody.className = 'group-body';
            
            tbody.innerHTML = `
                <tr class="bg-surface-50 border-b border-surface-200 group-header-row">
                    <td colspan="12" class="px-4 py-2 font-bold text-primary-700">
                        <div class="flex justify-between items-center">
                            <span class="group-number-label flex items-center gap-2">
                                <span class="bg-primary-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs">#</span>
                                Nomor Baru
                            </span>
                            <div class="space-x-3">
                                <button type="button" class="text-primary-600 hover:text-primary-800 text-[11px] uppercase tracking-wider font-bold addSubRow transition-colors">
                                    + Tambah Sub-Baris
                                </button>
                                <button type="button" class="text-red-500 hover:text-red-700 text-[11px] uppercase tracking-wider font-bold removeGroup transition-colors">
                                    - Hapus Nomor Ini
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="item-row hover:bg-surface-50 transition-colors">
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][lha]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly required placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][sebab]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][akibat]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][rekomendasi]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5">
                        <div class="space-y-1">
                            <textarea name="items[999][rows][0][pic_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="1" readonly placeholder="Nama PIC..."></textarea>
                            <select name="items[999][rows][0][pic_email]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm">
                                <option value="">-- Email PIC --</option>
                                {!! $userOptionsHtml !!}
                            </select>
                        </div>
                    </td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][evidence_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][status_closed]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][status_on_progress]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][status_tdd]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][0][evaluasi_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><input type="date" name="items[999][rows][0][jatuh_tempo]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm"></td>
                    <td class="p-1.5 text-center align-middle">
                        <button type="button" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md transition-colors removeRow" title="Hapus Sub-Baris">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
            `;
            table.appendChild(tbody);
            updateGroupNumbers();
        });

        // Event Delegation for Table Actions
        document.getElementById('dynamicTable').addEventListener('click', function(e) {
            // Remove Row
            if (e.target.closest('.removeRow')) {
                const btn = e.target.closest('.removeRow');
                const tbody = btn.closest('.group-body');
                if (tbody.querySelectorAll('.item-row').length > 1) {
                    btn.closest('tr').remove();
                    updateGroupNumbers();
                } else {
                    alert('Grup temuan minimal memiliki satu baris!');
                }
            }
            
            // Remove Group
            if (e.target.closest('.removeGroup')) {
                if (document.querySelectorAll('.group-body').length > 1) {
                    const groupEl = e.target.closest('.group-body');
                    window.showConfirmDialog('Hapus temuan nomor ini?', () => {
                        groupEl.remove();
                        updateGroupNumbers();
                    });
                }
            }

            // Add Sub Row
            if (e.target.closest('.addSubRow')) {
                const tbody = e.target.closest('.group-body');
                const tr = document.createElement('tr');
                tr.className = 'border-b item-row';
                tr.innerHTML = `
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][lha]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly required placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][sebab]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][akibat]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][rekomendasi]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5">
                        <div class="space-y-1">
                            <textarea name="items[999][rows][999][pic_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="1" readonly placeholder="Nama PIC..."></textarea>
                            <select name="items[999][rows][999][pic_email]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm">
                                <option value="">-- Email PIC --</option>
                                {!! $userOptionsHtml !!}
                            </select>
                        </div>
                    </td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][evidence_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][status_closed]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][status_on_progress]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][status_tdd]" class="w-full text-xs p-2 border-surface-300 rounded text-center popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="-"></textarea></td>
                    
                    <td class="border-r border-surface-200 p-1.5"><textarea name="items[999][rows][999][evaluasi_tl]" class="w-full text-xs p-2 border-surface-300 rounded popup-textarea cursor-pointer hover:bg-surface-50 shadow-sm" rows="2" readonly placeholder="Klik utk edit..."></textarea></td>
                    <td class="border-r border-surface-200 p-1.5"><input type="date" name="items[999][rows][999][jatuh_tempo]" class="w-full text-xs p-2 border-surface-300 rounded shadow-sm"></td>
                    <td class="p-1.5 text-center align-middle">
                        <button type="button" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-md transition-colors removeRow" title="Hapus Sub-Baris">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
                updateGroupNumbers();
            }
        });

        // Initial setup
        updateGroupNumbers();

        // Auto-fill PIC TL Name when Email is selected
        document.getElementById('dynamicTable').addEventListener('change', function(e) {
            if (e.target.tagName.toLowerCase() === 'select' && e.target.name.includes('[pic_email]')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const picName = selectedOption.getAttribute('data-name') || '';
                const picTlTextarea = e.target.closest('td').querySelector('textarea[name$="[pic_tl]"]');
                if (picTlTextarea) {
                    picTlTextarea.value = picName;
                }
            }
        });

                        // -----------------------------------------
        // Native Dialog Modal Logic for Textareas
        // -----------------------------------------
        const dialog = document.getElementById('nativeModal');
        const dialogTextarea = document.getElementById('nativeModalTextarea');
        const dialogTitle = document.getElementById('nativeModalTitle');
        const dialogClose = document.getElementById('nativeModalClose');
        const dialogXClose = document.getElementById('nativeModalXClose');
        const dialogSave = document.getElementById('nativeModalSave');
        const dialogCharCount = document.getElementById('nativeModalCharCount');
        let currentTarget = null;
        
        // Always append dialog to body for absolute safety
        document.body.appendChild(dialog);

        function updateCharCount() {
            if (dialogCharCount && dialogTextarea) {
                dialogCharCount.textContent = dialogTextarea.value.length + ' karakter';
            }
        }

        if (dialogTextarea) {
            dialogTextarea.addEventListener('input', updateCharCount);

            // Shortcut Ctrl + Enter to save
            dialogTextarea.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    dialogSave.click();
                }
            });
        }

        // Close when clicking dialog backdrop
        dialog.addEventListener('click', function(e) {
            const rect = dialog.getBoundingClientRect();
            const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
                                rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
            if (!isInDialog) {
                dialog.close();
                currentTarget = null;
            }
        });

        document.getElementById('dynamicTable').addEventListener('click', function(e) {
            if (e.target.classList.contains('popup-textarea')) {
                e.preventDefault();
                currentTarget = e.target;
                
                // Find column name for title
                const tr = e.target.closest('tr');
                const tdIndex = Array.from(tr.children).indexOf(e.target.closest('td'));
                const headers = ['LHA', 'Sebab', 'Akibat', 'Rekomendasi', 'PIC TL', 'Email PIC', 'Evidence TL', 'Status Closed', 'Status On Progress', 'Status TDD', 'Evaluasi TL', 'Jatuh Tempo'];
                
                let title = 'Edit Teks';
                if(tdIndex >= 0 && tdIndex < headers.length) {
                    title = 'Edit ' + headers[tdIndex];
                }
                
                dialogTitle.textContent = title;
                dialogTextarea.value = currentTarget.value;
                updateCharCount();
                dialog.showModal();
                setTimeout(() => {
                    dialogTextarea.focus();
                    dialogTextarea.setSelectionRange(dialogTextarea.value.length, dialogTextarea.value.length);
                }, 50);
            }
        });

        if (dialogClose) {
            dialogClose.addEventListener('click', function() {
                dialog.close();
                currentTarget = null;
            });
        }

        if (dialogXClose) {
            dialogXClose.addEventListener('click', function() {
                dialog.close();
                currentTarget = null;
            });
        }

        dialogSave.addEventListener('click', function() {
            if (currentTarget) {
                currentTarget.value = dialogTextarea.value;
            }
            dialog.close();
            currentTarget = null;
        });
    });
</script>
@endpush
@endsection










