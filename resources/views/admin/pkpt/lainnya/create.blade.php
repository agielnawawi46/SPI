@extends('layouts.app')

@section('title', 'Tambah Data Lainnya')
@section('page_title', 'Tambah Data Auditee & Waktu Pelaksanaan')

@section('breadcrumbs')
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-item">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.lainnya.index') }}" class="breadcrumb-item">Lainnya</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Tambah</span>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Form Tambah Data</h2>
    </div>
    
    <form action="{{ route('pkpt.lainnya.store') }}" method="POST" enctype="multipart/form-data" x-data="{ auditee: '{{ old('auditee_department_id') }}' }">
        @csrf
        <div class="card-body space-y-6">
            
            <div class="form-group mb-0">
                <label for="pkpt_id" class="form-label">Dokumen PKPT Terkait <span class="text-red-500">*</span></label>
                <select name="pkpt_id" id="pkpt_id" class="form-select @error('pkpt_id') border-red-500 @enderror" required>
                    <option value="">-- Pilih PKPT --</option>
                    @foreach($pkpts as $pkpt)
                        <option value="{{ $pkpt->id }}" {{ old('pkpt_id') == $pkpt->id ? 'selected' : '' }}>
                            {{ $pkpt->title }} (Tahun: {{ $pkpt->period_year }})
                        </option>
                    @endforeach
                </select>
                @error('pkpt_id') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

            <div class="form-group mb-0">
                <label for="audit_object" class="form-label">Objek Audit <span class="text-red-500">*</span></label>
                <input type="text" name="audit_object" id="audit_object" class="form-input @error('audit_object') border-red-500 @enderror" value="{{ old('audit_object') }}" placeholder="Contoh: Audit Keuangan Cabang A" required>
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
                <input type="text" name="custom_auditee_name" id="custom_auditee_name" class="form-input @error('custom_auditee_name') border-red-500 @enderror" value="{{ old('custom_auditee_name') }}" placeholder="Masukkan nama bidang/unit auditee">
                @error('custom_auditee_name') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group mb-0">
                    <label for="planned_start" class="form-label">Tanggal Mulai (Rencana)</label>
                    <input type="date" name="planned_start" id="planned_start" class="form-input @error('planned_start') border-red-500 @enderror" value="{{ old('planned_start') }}">
                    @error('planned_start') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
                <div class="form-group mb-0">
                    <label for="planned_end" class="form-label">Tanggal Selesai (Rencana)</label>
                    <input type="date" name="planned_end" id="planned_end" class="form-input @error('planned_end') border-red-500 @enderror" value="{{ old('planned_end') }}">
                    @error('planned_end') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group mb-0">
                <label for="attachment" class="form-label">Unggah Dokumen (Opsional)</label>
                <input type="file" name="attachment" id="attachment" class="form-input bg-white w-full @error('attachment') border-red-500 @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx">
                <p class="text-xs text-charcoal-muted mt-2">Format: PDF, DOC, DOCX, XLS, XLSX. Maks: 300MB.</p>
                @error('attachment') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            </div>

        </div>
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('pkpt.lainnya.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                Simpan Data
            </button>
        </div>
    </form>
</div>

@endsection
