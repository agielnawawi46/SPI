@extends('layouts.app')
@section('title', 'Buat Dokumen Pendamping')
@section('page_title', 'Buat Dokumen Pendamping Eksternal')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('other-activities.pendamping.index') }}" class="breadcrumb-link">Pendampingan Eksternal</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Buat Dokumen</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Buat Dokumen Pendamping</h2></div>
    
    <form action="{{ route('other-activities.pendamping.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        @if($selectedType)
            <input type="hidden" name="document_type_id" value="{{ $selectedType->id }}">
        @elseif(isset($documentTypes) && count($documentTypes) > 0)
            <input type="hidden" name="document_type_id" value="{{ $documentTypes->first()->id }}">
        @endif
        
        <div class="card-body space-y-6">
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Judul dokumen pendamping" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group border-t border-surface-200 pt-6">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="content" class="form-textarea w-full h-32" placeholder="Tuliskan catatan tambahan di sini...">{!! old('content') !!}</textarea>
                @error('content')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            
            <div class="border-t border-surface-200 pt-6">
                <x-file-upload 
                    name="attachments"
                    label="Unggah Berkas Lampiran"
                    badge="Wajib"
                    :multiple="true"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                    hint="Pilih satu atau beberapa berkas (PDF, Word, Excel, Gambar - Ukuran bebas / tanpa batas)"
                    :required="true"
                />
            </div>
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('other-activities.pendamping.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat Dokumen</button>
        </div>
    </form>
</div>
@endsection


