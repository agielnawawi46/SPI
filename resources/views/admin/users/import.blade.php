@extends('layouts.app')

@section('title', 'Import User CSV')
@section('page_title', 'Import User CSV')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('admin.users.index') }}" class="breadcrumb-link">Manajemen User</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Import CSV</span>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-6">
        <div class="card">
            <div class="card-header border-b border-surface-200 bg-surface-50">
                <h2 class="card-title text-base">Panduan Import</h2>
            </div>
            <div class="card-body">
                <div class="text-sm text-charcoal-muted space-y-3">
                    <p>Format file yang didukung adalah <strong>CSV</strong> (Comma Separated Values).</p>
                    <p>Baris pertama pada file CSV harus berisi header berikut persis:</p>
                    <ul class="list-disc pl-5 font-mono text-xs text-charcoal bg-surface-100 p-2 rounded">
                        <li>name</li>
                        <li>email</li>
                        <li>password</li>
                        <li>role</li>
                        <li>nip (opsional)</li>
                        <li>position (opsional)</li>
                        <li>department_code (opsional)</li>
                    </ul>
                    <p>Kolom <strong>department_code</strong> diisi dengan singkatan bidang (contoh: KEU, HC, OPS).</p>
                    <p>Kolom <strong>role</strong> diisi dengan nama role (contoh: admin, kspi, auditor, direktur, auditee_manager, auditee_staff).</p>
                </div>
                <div class="mt-4 pt-4 border-t border-surface-200">
                    <a href="{{ route('admin.users.import.template') }}" class="btn-outline w-full justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header border-b border-surface-200">
                <h2 class="card-title">Upload File CSV</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.import.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group mb-6">
                        <label class="block mb-2 text-sm font-medium text-charcoal">Pilih File CSV</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="csv_file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-surface-300 border-dashed rounded-xl cursor-pointer bg-surface-50 hover:bg-surface-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-10 h-10 mb-3 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="mb-2 text-sm text-charcoal"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                    <p class="text-xs text-charcoal-muted">CSV (Max. 2MB)</p>
                                </div>
                                <input id="csv_file" name="csv_file" type="file" class="hidden" accept=".csv" required />
                            </label>
                        </div>
                        <p id="file-name-display" class="mt-2 text-sm text-primary-600 font-medium hidden"></p>
                        @error('csv_file')
                            <p class="form-error mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-surface-200">
                        <a href="{{ route('admin.users.index') }}" class="btn-outline">Batal</a>
                        <button type="submit" class="btn-primary" onclick="this.innerHTML='<svg class=\'animate-spin w-4 h-4 mr-2\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\' fill=\'none\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\'></path></svg> Memproses...'; this.classList.add('opacity-75'); this.form.submit();">Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('csv_file').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var display = document.getElementById('file-name-display');
        display.textContent = 'File terpilih: ' + fileName;
        display.classList.remove('hidden');
    });
</script>
@endpush
