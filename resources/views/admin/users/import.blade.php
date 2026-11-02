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
<div class="space-y-6">

    {{-- Top Grid: 2 Balanced Cards (Langkah 1 & Langkah 2) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        
        {{-- Card 1: Langkah 1 - Panduan & Unduh Template CSV --}}
        <div class="card flex flex-col justify-between">
            <div>
                <div class="card-header border-b border-surface-200 bg-surface-50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 font-bold text-xs flex items-center justify-center">1</span>
                        <h2 class="card-title text-sm font-bold text-charcoal">Panduan & Template CSV</h2>
                    </div>
                    <span class="text-[11px] font-semibold text-charcoal-muted">Langkah 1</span>
                </div>

                <div class="card-body space-y-4">
                    <p class="text-xs text-charcoal-muted leading-relaxed">
                        Unduh template CSV resmi untuk memastikan struktur kolom dan format data sesuai dengan sistem SIMAK.
                    </p>

                    {{-- Format Header Columns --}}
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal mb-2">Struktur Header CSV (6 Kolom):</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 font-mono text-[11px]">
                            <div class="bg-surface-100 p-2 rounded-lg border border-surface-200">
                                <span class="font-bold text-charcoal">name</span>
                                <span class="text-red-500 font-sans text-[10px] block font-normal mt-0.5">Wajib</span>
                            </div>
                            <div class="bg-surface-100 p-2 rounded-lg border border-surface-200">
                                <span class="font-bold text-charcoal">email</span>
                                <span class="text-red-500 font-sans text-[10px] block font-normal mt-0.5">Wajib (Unik)</span>
                            </div>
                            <div class="bg-surface-100 p-2 rounded-lg border border-surface-200">
                                <span class="font-bold text-charcoal">password</span>
                                <span class="text-red-500 font-sans text-[10px] block font-normal mt-0.5">Wajib</span>
                            </div>
                            <div class="bg-surface-100 p-2 rounded-lg border border-surface-200">
                                <span class="font-bold text-charcoal">role</span>
                                <span class="text-red-500 font-sans text-[10px] block font-normal mt-0.5">Wajib</span>
                            </div>
                            <div class="bg-surface-100 p-2 rounded-lg border border-surface-200">
                                <span class="font-bold text-charcoal">nip</span>
                                <span class="text-charcoal-muted font-sans text-[10px] block mt-0.5">Opsional</span>
                            </div>
                            <div class="bg-surface-100 p-2 rounded-lg border border-surface-200">
                                <span class="font-bold text-charcoal">department_code</span>
                                <span class="text-amber-700 font-sans text-[10px] block font-semibold mt-0.5">Wajib Auditee</span>
                            </div>
                        </div>
                    </div>

                    {{-- Rules Box --}}
                    <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1.5">
                        <div class="flex items-center gap-1.5 font-bold text-amber-950">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Ketentuan Pengisian:</span>
                        </div>
                        <ul class="list-disc pl-5 space-y-1 text-[11px] leading-relaxed">
                            <li><strong>Auditee (Manager, Asman, Staff):</strong> Kolom <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200 text-amber-900">department_code</code> wajib diisi sesuai kode bidang resmi pada tabel kamus di bawah.</li>
                            <li><strong>Direktur & Komisaris:</strong> Berdiri sendiri menaungi seluruh perusahaan, sehingga kolom <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200 text-amber-900">department_code</code> dapat dikosongkan.</li>
                            <li>Kolom <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200 text-amber-900">position</code> (jabatan) sudah ditiadakan di template baru, namun sistem tetap mendukung jika diimpor dari file CSV lama.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-footer p-4 border-t border-surface-200 bg-surface-50/50 mt-auto">
                <a href="{{ route('admin.users.import.template') }}" class="btn-primary w-full justify-center text-xs py-2.5 shadow-2xs">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh Template CSV Resmi (.csv)
                </a>
            </div>
        </div>

        {{-- Card 2: Langkah 2 - Unggah File CSV --}}
        <div class="card flex flex-col justify-between">
            <form action="{{ route('admin.users.import.post') }}" method="POST" enctype="multipart/form-data" class="flex flex-col h-full">
                @csrf
                <div>
                    <div class="card-header border-b border-surface-200 bg-surface-50 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">2</span>
                            <h2 class="card-title text-sm font-bold text-charcoal">Unggah Berkas CSV</h2>
                        </div>
                        <span class="text-[11px] font-semibold text-charcoal-muted">Langkah 2</span>
                    </div>

                    <div class="card-body space-y-4">
                        <p class="text-xs text-charcoal-muted leading-relaxed">
                            Pilih berkas CSV yang telah diisi sesuai format untuk langsung diimpor ke dalam database pengguna.
                        </p>

                        {{-- Drag & Drop Area --}}
                        <div class="form-group mb-0">
                            <label for="csv_file" class="flex flex-col items-center justify-center w-full p-8 border-2 border-surface-300 border-dashed rounded-2xl cursor-pointer bg-surface-50/60 hover:bg-surface-100 hover:border-primary-400 transition-all duration-200 group">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center mb-2.5 shadow-2xs group-hover:scale-105 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-charcoal mb-0.5"><span class="text-primary-600 hover:underline">Klik untuk pilih berkas</span> atau seret file ke sini</p>
                                    <p class="text-[11px] text-charcoal-muted">Mendukung ekstensi .csv (Maksimal 2MB)</p>
                                </div>
                                <input id="csv_file" name="csv_file" type="file" class="hidden" accept=".csv" required />
                            </label>

                            <div id="file-info-container" class="mt-3 p-3 bg-primary-50/80 border border-primary-200 rounded-xl hidden items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span id="file-name-display" class="text-xs font-bold text-primary-900 truncate"></span>
                                </div>
                                <span class="text-[10px] font-bold uppercase bg-primary-100 text-primary-700 px-2 py-0.5 rounded border border-primary-200 flex-shrink-0">Siap Import</span>
                            </div>

                            @error('csv_file')
                                <p class="form-error mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer p-4 border-t border-surface-200 bg-surface-50/50 mt-auto flex items-center justify-end gap-2.5">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary text-xs px-4 py-2">Batal</a>
                    <button type="submit" class="btn-primary text-xs px-5 py-2 cursor-pointer shadow-2xs" onclick="this.innerHTML='<svg class=\'animate-spin w-3.5 h-3.5 mr-1.5 inline\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\' fill=\'none\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\'></path></svg> Mengimpor...'; this.classList.add('opacity-75'); this.form.submit();">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Mulai Import User
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Bottom Section (Full Width): Kamus Kode Bidang Resmi --}}
    <div class="card" x-data="{ searchDept: '', filterLevel: 'all' }">
        <div class="card-header border-b border-surface-200 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-surface-50">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="card-title text-base font-bold text-charcoal">Daftar Kode Bidang Resmi PT Pelayanan Energi Batam</h3>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 border border-primary-200">{{ count($departments) }} Bidang</span>
                </div>
                <p class="text-xs text-charcoal-muted mt-0.5">Gunakan nilai pada kolom <strong>Kode CSV</strong> di bawah ini saat mengisi file CSV.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <svg class="w-4 h-4 text-charcoal-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchDept" placeholder="Cari nama atau kode bidang..." class="form-input text-xs py-1.5 pl-9 pr-3 w-64 bg-white">
                </div>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="px-5 py-2.5 bg-white border-b border-surface-200 flex items-center gap-2 overflow-x-auto text-xs no-scrollbar">
            <span class="text-charcoal-muted font-semibold text-[11px] uppercase tracking-wider mr-1">Tingkatan:</span>
            <button type="button" @click="filterLevel = 'all'" :class="filterLevel === 'all' ? 'bg-primary-600 text-white font-semibold shadow-2xs' : 'bg-surface-50 text-charcoal hover:bg-surface-100 border border-surface-200'" class="px-3 py-1 rounded-full transition-all cursor-pointer">Semua ({{ count($departments) }})</button>
            <button type="button" @click="filterLevel = 'manager'" :class="filterLevel === 'manager' ? 'bg-purple-600 text-white font-semibold shadow-2xs' : 'bg-surface-50 text-charcoal hover:bg-surface-100 border border-surface-200'" class="px-3 py-1 rounded-full transition-all cursor-pointer">Manager (4)</button>
            <button type="button" @click="filterLevel = 'asman'" :class="filterLevel === 'asman' ? 'bg-teal-600 text-white font-semibold shadow-2xs' : 'bg-surface-50 text-charcoal hover:bg-surface-100 border border-surface-200'" class="px-3 py-1 rounded-full transition-all cursor-pointer">Assistant Manager (11)</button>
            <button type="button" @click="filterLevel = 'staff'" :class="filterLevel === 'staff' ? 'bg-sky-600 text-white font-semibold shadow-2xs' : 'bg-surface-50 text-charcoal hover:bg-surface-100 border border-surface-200'" class="px-3 py-1 rounded-full transition-all cursor-pointer">Staff Unit (18)</button>
            <button type="button" @click="filterLevel = 'spi'" :class="filterLevel === 'spi' ? 'bg-emerald-600 text-white font-semibold shadow-2xs' : 'bg-surface-50 text-charcoal hover:bg-surface-100 border border-surface-200'" class="px-3 py-1 rounded-full transition-all cursor-pointer">Satuan Pengawasan Intern (2)</button>
        </div>

        {{-- Table --}}
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-surface-100 text-charcoal sticky top-0 border-b border-surface-200 z-10">
                    <tr>
                        <th class="py-2.5 px-4 font-bold w-40">Kode CSV</th>
                        <th class="py-2.5 px-4 font-bold">Nama Bidang / Unit Kerja</th>
                        <th class="py-2.5 px-4 font-bold w-36">Tingkatan</th>
                        <th class="py-2.5 px-4 font-bold">Hierarki Induk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 bg-white">
                    @foreach($departments as $dept)
                        <tr x-show="(filterLevel === 'all' || 
                                     (filterLevel === 'spi' && ('{{ $dept->level }}' === 'kspi' || '{{ $dept->level }}' === 'auditor')) || 
                                     filterLevel === '{{ $dept->level }}') &&
                                    ('{{ strtolower($dept->name) }}'.includes(searchDept.toLowerCase()) || '{{ strtolower($dept->code) }}'.includes(searchDept.toLowerCase()))"
                            class="hover:bg-primary-50/30 transition-colors">
                            <td class="py-2.5 px-4 font-mono font-bold text-primary-700 bg-primary-50/20">
                                <span class="bg-white px-2 py-0.5 rounded border border-primary-200 shadow-2xs select-all">{{ $dept->code }}</span>
                            </td>
                            <td class="py-2.5 px-4 font-semibold text-charcoal">
                                {{ $dept->name }}
                            </td>
                            <td class="py-2.5 px-4">
                                @php
                                    $badgeClass = match($dept->level) {
                                        'manager' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'asman'   => 'bg-teal-50 text-teal-800 border-teal-200',
                                        'staff'   => 'bg-sky-50 text-sky-800 border-sky-200',
                                        'kspi', 'auditor' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        default   => 'bg-gray-50 text-gray-700 border-gray-200'
                                    };
                                    $levelLabel = match($dept->level) {
                                        'manager' => 'Manager',
                                        'asman'   => 'Asman',
                                        'staff'   => 'Staff',
                                        'kspi'    => 'KSPI',
                                        'auditor' => 'Auditor',
                                        default   => ucfirst($dept->level)
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $badgeClass }}">
                                    {{ $levelLabel }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-charcoal-muted">
                                @if($dept->parent)
                                    <span class="inline-flex items-center gap-1 text-[11px]">
                                        <svg class="w-3 h-3 text-charcoal-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        {{ $dept->parent->name }}
                                    </span>
                                @else
                                    <span class="text-charcoal-muted/60 italic text-[11px]">&mdash; Langsung Direktur</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.getElementById('csv_file').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var container = document.getElementById('file-info-container');
            var display = document.getElementById('file-name-display');
            display.textContent = file.name;
            container.classList.remove('hidden');
            container.classList.add('flex');
        }
    });
</script>
@endpush
