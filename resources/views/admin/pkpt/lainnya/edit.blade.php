@extends('layouts.app')

@section('title', 'Edit Data Lainnya')
@section('page_title', 'Edit Data Auditee & Waktu Pelaksanaan')

@section('breadcrumbs')
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-item">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.lainnya.index') }}" class="breadcrumb-item">Lainnya</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit</span>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Form Edit Data</h2>
    </div>
    
    <form action="{{ route('pkpt.lainnya.update', $lainnya) }}" method="POST" enctype="multipart/form-data" x-data="{ auditee: '{{ old('auditee_department_id', $lainnya->auditee_department_id ? $lainnya->auditee_department_id : ($lainnya->custom_auditee_name ? 'other' : '')) }}' }">
        @csrf
        @method('PUT')
        <div class="card-body space-y-6">
            
            <div class="form-group mb-0">
                <label for="pkpt_id" class="form-label">Dokumen PKPT Terkait <span class="text-red-500">*</span></label>
                <select name="pkpt_id" id="pkpt_id" class="form-select @error('pkpt_id') border-red-500 @enderror" required>
                    <option value="">-- Pilih PKPT --</option>
                    @foreach($pkpts as $pkpt)
                        <option value="{{ $pkpt->id }}" {{ old('pkpt_id', $lainnya->pkpt_id) == $pkpt->id ? 'selected' : '' }}>
                            {{ $pkpt->title }} (Tahun: {{ $pkpt->period_year }})
                        </option>
                    @endforeach
                </select>
                @error('pkpt_id') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

            <div class="form-group mb-0">
                <label for="audit_object" class="form-label">Objek Audit <span class="text-red-500">*</span></label>
                <input type="text" name="audit_object" id="audit_object" class="form-input @error('audit_object') border-red-500 @enderror" value="{{ old('audit_object', $lainnya->audit_object) }}" placeholder="Contoh: Audit Keuangan Cabang A" required>
                @error('audit_object') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

            <div class="form-group mb-0">
                <label for="auditee_department_id" class="form-label">Auditee (Bidang/Unit)</label>
                <select name="auditee_department_id" id="auditee_department_id" x-model="auditee" class="form-select @error('auditee_department_id') border-red-500 @enderror">
                    <option value="">-- Pilih Auditee --</option>
                    @foreach($departments as $dept)
                        @if($dept->code !== 'LAIN')
                        <option value="{{ $dept->id }}">
                            {{ $dept->name }}
                        </option>
                        @endif
                    @endforeach
                    <option value="other">Lainnya (Isi Manual)</option>
                </select>
                @error('auditee_department_id') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

            <div class="form-group mb-0" x-show="auditee === 'other'" style="display: none;">
                <label for="custom_auditee_name" class="form-label">Nama Auditee Manual <span class="text-red-500">*</span></label>
                <input type="text" name="custom_auditee_name" id="custom_auditee_name" class="form-input @error('custom_auditee_name') border-red-500 @enderror" value="{{ old('custom_auditee_name', $lainnya->custom_auditee_name) }}" placeholder="Masukkan nama bidang/unit auditee">
                @error('custom_auditee_name') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group mb-0">
                    <label for="planned_start" class="form-label">Tanggal Mulai (Rencana)</label>
                    <input type="date" name="planned_start" id="planned_start" class="form-input @error('planned_start') border-red-500 @enderror" value="{{ old('planned_start', $lainnya->planned_start ? $lainnya->planned_start->format('Y-m-d') : '') }}">
                    @error('planned_start') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
                <div class="form-group mb-0">
                    <label for="planned_end" class="form-label">Tanggal Selesai (Rencana)</label>
                    <input type="date" name="planned_end" id="planned_end" class="form-input @error('planned_end') border-red-500 @enderror" value="{{ old('planned_end', $lainnya->planned_end ? $lainnya->planned_end->format('Y-m-d') : '') }}">
                    @error('planned_end') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group mb-0">
                <label for="attachment" class="form-label">Unggah Dokumen Baru (Opsional)</label>
                @if($lainnya->attachment_path)
                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-sm font-medium text-blue-700">Dokumen sudah terunggah</span>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remove_attachment" value="1" class="form-checkbox text-red-500 rounded border-gray-300 focus:ring-red-500">
                            <span class="text-xs text-red-600 font-medium hover:text-red-700">Hapus Dokumen</span>
                        </label>
                    </div>
                @endif
                <input type="file" name="attachment" id="attachment" class="form-input bg-white w-full @error('attachment') border-red-500 @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx">
                <p class="text-xs text-charcoal-muted mt-2">Format: PDF, DOC, DOCX, XLS, XLSX. Maks: 300MB. @if($lainnya->attachment_path) <b>Biarkan kosong jika tidak ingin mengubah dokumen.</b> @endif</p>
                @error('attachment') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

        </div>
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('pkpt.lainnya.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection
