@extends('layouts.app')
@section('title', 'Upload Dokumen QA')
@section('page_title', 'Upload Dokumen QA')

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Upload Dokumen &mdash; {{ $selectedType?->name ?? 'Dokumen QA' }}</h2></div>
    <form action="{{ route('other-activities.qa.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="document_type_id" value="{{ $selectedType?->id }}">
        <div class="card-body space-y-4">
            <div class="form-group">
                <label class="form-label">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Judul dokumen QA" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <x-file-upload 
                name="attachments"
                label="Unggah Berkas"
                badge="Wajib"
                :multiple="true"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                hint="Pilih satu atau beberapa berkas (PDF, DOC/DOCX, XLS/XLSX, JPG/PNG - Maks. 20MB per berkas)"
                :required="true"
            />
        </div>
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('other-activities.qa.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Upload Dokumen</button>
        </div>
    </form>
</div>
@endsection
