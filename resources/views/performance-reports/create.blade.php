@extends('layouts.app')
@section('title', 'Buat Laporan Kinerja')
@section('page_title', 'Buat Laporan Kinerja')

@section('content')
<div class="card">
    <div class="card-header"><h2 class="card-title">Buat Laporan Kinerja Bulanan</h2></div>
    
    <form action="{{ route('performance-reports.store') }}" method="POST">
        @csrf
        <div class="card-body space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-3 form-group">
                    <label class="form-label">Judul Laporan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Contoh: Laporan Kinerja SPI — Agustus 2026">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Bulan <span class="text-red-500">*</span></label>
                    <select name="report_month" class="form-select">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ old('report_month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                    @error('report_month')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Tahun <span class="text-red-500">*</span></label>
                    <select name="report_year" class="form-select">
                        @foreach(range(date('Y'), 2020) as $y)
                            <option value="{{ $y }}" {{ old('report_year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    @error('report_year')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Dokumen</label>
                    <select name="document_type_id" class="form-select">
                        <option value="">-- Pilih --</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="alert alert-info border-l-4">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold">Informasi</h3>
                    <p class="text-xs mt-1 text-blue-700">Setelah dokumen dibuat, Anda dapat mengisi Laporan Kinerja dan mengunggah Lampiran pada halaman detail laporan.</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-surface-50 border-t border-surface-100 flex items-center justify-end gap-3">
            <a href="{{ route('performance-reports.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat Laporan</button>
        </div>
    </form>
</div>
@endsection


