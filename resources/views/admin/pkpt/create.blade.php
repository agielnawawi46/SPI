@extends('layouts.app')

@section('title', 'Tambah PKPT Baru')
@section('page_title', 'Tambah PKPT Baru')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-link">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Tambah</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Form Pembuatan PKPT</h2>
    </div>

    <form action="{{ route('pkpt.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group mb-0">
                    <label for="title" class="form-label">Judul PKPT <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-input" required placeholder="Contoh: PKPT Tahun 2026">
                    @error('title') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
                
                <div class="form-group mb-0">
                    <label for="period_year" class="form-label">Tahun Periode <span class="text-red-500">*</span></label>
                    <select id="period_year" name="period_year" class="form-select" required>
                        @php
                            $currentYear = (int) date('Y');
                        @endphp
                        @foreach(range($currentYear - 1, $currentYear + 2) as $year)
                            <option value="{{ $year }}" {{ old('period_year', $currentYear) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    @error('period_year') <p class="form-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $message }}</p> @enderror
                </div>
            </div>

            <x-file-upload 
                name="pkpt_file"
                label="Unggah Dokumen PKPT Utama"
                badge="Wajib PDF"
                :multiple="false"
                accept=".pdf"
                hint="Format: PDF (Ukuran bebas / tanpa batas). File ini akan melalui proses review sebelum disetujui."
                :required="true"
            />
            
            <div class="alert alert-info border-l-4">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold">Informasi</h3>
                    <p class="text-xs mt-1 text-blue-700">Pastikan file PKPT Utama yang diunggah adalah versi final. File ini akan melalui proses review sebelum disetujui.</p>
                </div>
            </div>

        </div>
        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('pkpt.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat PKPT</button>
        </div>
    </form>
</div>
@endsection
