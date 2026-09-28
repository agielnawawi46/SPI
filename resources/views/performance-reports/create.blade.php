@extends('layouts.app')
@section('title', 'Buat Laporan Kinerja')
@section('page_title', 'Buat Laporan Kinerja')

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Upload Laporan Kinerja</h2></div>
    
    <form action="{{ route('performance-reports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body space-y-5">
            <div class="form-group">
                <label class="form-label">Judul Laporan <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: Laporan Kinerja SPI" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            
            <div class="form-group">
                <label class="form-label">Deskripsi (Opsional)</label>
                <textarea name="content" class="form-input" rows="4" placeholder="Tambahkan deskripsi atau keterangan opsional">{{ old('content') }}</textarea>
                @error('content')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Upload File <span class="text-red-500">*</span></label>
                <input type="file" name="attachment" class="form-input" required
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                <p class="text-xs text-charcoal-muted mt-1">Format: PDF, DOC/DOCX, XLS/XLSX, JPG/PNG. Maks: 300MB.</p>
                @error('attachment')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('performance-reports.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Upload Laporan</button>
        </div>
    </form>
</div>
@endsection


