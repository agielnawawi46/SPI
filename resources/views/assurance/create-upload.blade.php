@extends('layouts.app')
@section('title', 'Upload Dokumen')
@section('page_title', 'Upload Dokumen')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Upload {{ $selectedType?->name ?? 'Dokumen' }} &mdash; {{ ucfirst(str_replace('_', ' ', $auditType)) }}</h2>
    </div>

    <form action="{{ route('assurance.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="audit_type" value="{{ $auditType }}">
        <input type="hidden" name="document_type_id" value="{{ $selectedType?->id }}">

        <div class="card-body space-y-5">
            <div class="form-group">
                <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}"
                    placeholder="Masukkan judul dokumen">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
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
            <a href="{{ route('assurance.index', ['audit_type' => $auditType, 'type' => $selectedType?->slug]) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Upload Dokumen</button>
        </div>
    </form>
</div>
@endsection
