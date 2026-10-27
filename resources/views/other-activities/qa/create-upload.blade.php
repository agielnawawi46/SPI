@extends('layouts.app')
@section('title', 'Unggah Dokumen QA')
@section('page_title', 'Unggah Dokumen QA')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.qa.index', ['type' => $selectedType?->slug]) }}" class="breadcrumb-link">Quality Assurance</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Unggah {{ $selectedType?->name ?? 'Dokumen' }}</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Unggah Dokumen &mdash; {{ $selectedType?->name ?? 'Dokumen QA' }}</h2></div>
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
                hint="Pilih satu atau beberapa berkas (PDF, DOC/DOCX, XLS/XLSX, JPG/PNG - Ukuran bebas / tanpa batas)"
                :required="true"
            />
        </div>
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('other-activities.qa.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Unggah Dokumen</button>
        </div>
    </form>
</div>
@endsection
