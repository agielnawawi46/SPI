@extends('layouts.app')
@section('title', 'Unggah Dokumen')
@section('page_title', 'Unggah Dokumen')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('assurance.index', ['audit_type' => $auditType, 'type' => $selectedType?->slug]) }}" class="breadcrumb-link">Assurance ({{ ucfirst(str_replace('_', ' ', $auditType)) }})</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Unggah {{ $selectedType?->name ?? 'Dokumen' }}</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Unggah {{ $selectedType?->name ?? 'Dokumen' }} &mdash; {{ ucfirst(str_replace('_', ' ', $auditType)) }}</h2>
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

            <x-file-upload 
                name="attachments" 
                label="Unggah Berkas {{ $selectedType?->name ?? 'Dokumen' }}"
                subtitle="Pilih satu atau beberapa berkas dokumen pendukung"
                :required="true"
                :multiple="true"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                hint="Format didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Bisa memilih beberapa file sekaligus."
            />
            @error('attachments')<p class="form-error">{{ $message }}</p>@enderror
            @error('attachment')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('assurance.index', ['audit_type' => $auditType, 'type' => $selectedType?->slug]) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Unggah Dokumen</button>
        </div>
    </form>
</div>
@endsection
