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
            <button type="submit" class="btn-primary">Upload Dokumen</button>
        </div>
    </form>
</div>
@endsection
