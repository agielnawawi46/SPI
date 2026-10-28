@extends('layouts.app')

@section('title', 'Edit PKPT')
@section('page_title', 'Edit PKPT')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-link">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Edit PKPT</h2>
    </div>

    <form action="{{ route('pkpt.update', $pkpt) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group mb-0">
                    <label for="title" class="form-label">Judul PKPT <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $pkpt->title) }}" class="form-input" required>
                    @error('title') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
                
                <div class="form-group mb-0">
                    <label for="period_year" class="form-label">Tahun Periode <span class="text-red-500">*</span></label>
                    <select id="period_year" name="period_year" class="form-select" required>
                        @php
                            $currentYear = (int) date('Y');
                        @endphp
                        @foreach(range($currentYear - 2, $currentYear + 2) as $year)
                            <option value="{{ $year }}" {{ old('period_year', $pkpt->period_year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    @error('period_year') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
            </div>

            <x-file-upload 
                name="attachments"
                label="Dokumen PKPT Utama"
                subtitle="Kelola dan tambah berkas dokumen PKPT (berkas baru akan ditambahkan tanpa menghapus berkas sebelumnya)"
                badge="Multi-File"
                :multiple="true"
                accept=".pdf,.doc,.docx,.xls,.xlsx"
                hint="Format: PDF, Word (DOC/DOCX), Excel (XLS/XLSX) (Ukuran bebas / tanpa batas). Anda dapat memilih beberapa berkas sekaligus atau bertahap."
                :existingFiles="$attachments"
                :document="$document"
                deleteRoute="pkpt.destroy-attachment"
                :deleteRouteParams="['pkpt' => $pkpt->id]"
                :required="false"
            />
            @error('attachments') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
            
        </div>
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('pkpt.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
