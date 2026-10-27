@extends('layouts.app')
@section('title', 'Tambah Lampiran')
@section('page_title', 'Tambah Lampiran Laporan')

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
                    <textarea name="content" class="form-input" rows="4">{{ old('content', $document->content) }}</textarea>
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
            
            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('performance-reports.show', $document) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection


