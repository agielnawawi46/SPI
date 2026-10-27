@extends('layouts.app')
@section('title', 'Tambah Lampiran')
@section('page_title', 'Tambah Lampiran Laporan')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('performance-reports.index') }}" class="breadcrumb-link">Laporan Kinerja</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('performance-reports.show', $document) }}" class="breadcrumb-link">{{ $document->title }}</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Laporan: {{ $document->title }}</h2>
        <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
    </div>
    <div class="card-body">
        <form action="{{ route('performance-reports.update', $document) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            
            <div class="space-y-5">
                <div class="form-group">
                    <label class="form-label">Judul Laporan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" required>
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="content" class="form-textarea" rows="4">{{ old('content', $document->content) }}</textarea>
                    @error('content')<p class="form-error">{{ $message }}</p>@enderror
                </div>

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
            
            <div class="form-actions">
                <a href="{{ route('performance-reports.show', $document) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection


