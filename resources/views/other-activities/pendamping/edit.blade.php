@extends('layouts.app')
@section('title', 'Isi Kertas Kerja Pendamping')
@section('page_title', 'Isi Kertas Kerja Pendamping')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kertas Kerja: {{ $document->title }}</h2>
        <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
    </div>
    <div class="card-body">
        <form action="{{ route('other-activities.pendamping.update', $document) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
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
            
            <div class="form-group mt-6 border-t border-surface-200 pt-6">
                <h3 class="text-sm font-semibold text-charcoal mb-4">Unggah Lampiran (Opsional)</h3>
                <input type="file" name="attachment" class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                <p class="text-xs text-charcoal-muted mt-1">Format: PDF, Word, Excel, Gambar. Maks: 300MB.</p>
                @error('attachment')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            
            <div class="flex justify-between mt-8">
                <a href="{{ route('assurance.show', $document) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Kertas Kerja</button>
            </div>
        </form>
    </div>
</div>
@endsection


