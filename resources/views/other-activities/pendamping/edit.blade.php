@extends('layouts.app')
@section('title', 'Isi Kertas Kerja Pendamping')
@section('page_title', 'Isi Kertas Kerja Pendamping')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kertas Kerja: {{ $document->title }}</h2>
        <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
    </div>
    <form action="{{ route('other-activities.pendamping.update', $document) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
    <div class="card-body">
            <div class="form-group mb-4">
                <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $document->title) }}" placeholder="Judul dokumen pendamping" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group border-t border-surface-200 pt-6">
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
                    hint="Pilih satu atau beberapa berkas untuk ditambahkan (PDF, Word, Excel, Gambar - Maks. 20MB per berkas)"
                    :existingFiles="$document->attachments"
                    :document="$document"
                    :required="false"
                />
            </div>
        </div>
        
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3 mt-8">
            <a href="{{ route('other-activities.pendamping.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Kertas Kerja</button>
        </div>
    </form>
</div>
@endsection


