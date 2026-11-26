@extends('layouts.app')

@section('title', 'Backup Berkas')
@section('page_title', 'Backup & Unduh Berkas')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Backup Berkas</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header & Action Card --}}
    <div class="card">
        <div class="card-header flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="card-title flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Pusat Backup Berkas SIMAK (.ZIP)</span>
                </h2>
                <p class="text-xs sm:text-sm text-charcoal-muted mt-1">
                    Unduh seluruh dokumen yang diunggah beserta dokumen PDF formulir (Surat Tugas, BAK, Monitoring TL) yang tersusun otomatis ke dalam folder sesuai menu. Dokumen yang telah dihapus tidak ikut dicadangkan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                {{-- Filter Tahun --}}
                <form action="{{ route('admin.backup.index') }}" method="GET" class="flex items-center gap-2 m-0">
                    <label for="backup-year-filter" class="text-xs font-semibold text-charcoal-muted whitespace-nowrap">Periode:</label>
                    <select id="backup-year-filter" name="year" onchange="this.form.submit()" class="form-input text-sm py-1.5 h-9 w-36">
                        <option value="all" {{ !$yearFilter ? 'selected' : '' }}>Semua Tahun</option>
                        @foreach($inventory['available_years'] as $year)
                            <option value="{{ $year }}" {{ (int)$yearFilter === (int)$year ? 'selected' : '' }}>Tahun {{ $year }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Download All Button --}}
                <form action="{{ route('admin.backup.download') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="module" value="all">
                    <input type="hidden" name="year" value="{{ $yearFilter ?? 'all' }}">
                    <button type="submit"
                            @if($inventory['total_items'] === 0) disabled @endif
                            class="btn-primary h-9 px-4 text-sm inline-flex items-center gap-2 {{ $inventory['total_items'] === 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Download Semua (.ZIP)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div class="pr-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Berkas Lampiran Aktif</p>
                    <p class="stat-card-value">{{ number_format($inventory['total_files']) }}</p>
                </div>
                <div class="stat-card-icon bg-primary-50 flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            @if($inventory['total_missing'] > 0)
                <p class="text-xs text-amber-600 mt-3 font-medium">
                    {{ $inventory['total_missing'] }} file tercatat di DB tidak ditemukan di disk
                </p>
            @else
                <p class="text-xs text-charcoal-muted mt-3">Seluruh lampiran &amp; bukti auditee</p>
            @endif
        </div>

        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div class="pr-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Dokumen PDF Otomatis</p>
                    <p class="stat-card-value text-emerald-600">{{ number_format($inventory['total_pdfs']) }}</p>
                </div>
                <div class="stat-card-icon bg-emerald-50 flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-charcoal-muted mt-3">Surat Tugas, BAK, &amp; Monitoring TL</p>
        </div>

        <div class="stat-card">
            <div class="flex items-start justify-between">
                <div class="pr-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted">Total Ukuran Lampiran</p>
                    <p class="stat-card-value">{{ \App\Services\BackupArchiveService::formatBytes($inventory['total_size']) }}</p>
                </div>
                <div class="stat-card-icon bg-blue-50 flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-charcoal-muted mt-3">Belum termasuk ukuran PDF yang di-generate</p>
        </div>
    </div>

    {{-- Module Breakdown Table --}}
    <div class="card">
        <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="card-title">Rincian Berkas per Menu</h3>
                <p class="text-xs text-charcoal-muted mt-0.5">
                    Anda dapat mengunduh semua menu sekaligus di atas, atau mengunduh per menu secara terpisah.
                </p>
            </div>
            @if($yearFilter)
                <span class="badge badge-primary">Filter Aktif: Tahun {{ $yearFilter }}</span>
            @endif
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block table-wrapper">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Menu / Modul</th>
                        <th>Folder di Dalam ZIP</th>
                        <th class="text-center">File Upload</th>
                        <th class="text-center">Generate PDF</th>
                        <th class="text-right">Ukuran Upload</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventory['modules'] as $mod)
                    <tr>
                        <td>
                            <p class="text-sm font-semibold text-charcoal">{{ $mod['label'] }}</p>
                            <p class="text-xs text-charcoal-muted mt-0.5">{{ $mod['desc'] }}</p>
                        </td>
                        <td>
                            <code class="text-xs bg-surface-100 text-primary-700 px-2 py-1 rounded border border-surface-200 font-mono">
                                {{ $mod['folder'] }}/
                            </code>
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-charcoal">{{ number_format($mod['file_count']) }}</span>
                            @if($mod['missing_count'] > 0)
                                <span class="block text-[11px] text-amber-600 mt-0.5">({{ $mod['missing_count'] }} hilang di disk)</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-emerald-700">{{ number_format($mod['pdf_count']) }}</span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <span class="text-sm text-charcoal">{{ \App\Services\BackupArchiveService::formatBytes($mod['total_size']) }}</span>
                        </td>
                        <td class="text-right">
                            <form action="{{ route('admin.backup.download') }}" method="POST" class="inline m-0">
                                @csrf
                                <input type="hidden" name="module" value="{{ $mod['key'] }}">
                                <input type="hidden" name="year" value="{{ $yearFilter ?? 'all' }}">
                                <button type="submit"
                                        @if($mod['total_items'] === 0) disabled @endif
                                        class="btn-secondary btn-sm inline-flex items-center gap-1.5 {{ $mod['total_items'] === 0 ? 'opacity-45 cursor-not-allowed' : 'hover:border-primary-400 hover:text-primary-700' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Unduh ZIP</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-surface-200">
            @foreach($inventory['modules'] as $mod)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-charcoal">{{ $mod['label'] }}</h4>
                        <p class="text-xs text-charcoal-muted mt-0.5">{{ $mod['desc'] }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <code class="bg-surface-100 text-primary-700 px-2 py-0.5 rounded border border-surface-200 font-mono">{{ $mod['folder'] }}/</code>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs bg-surface-50 p-2.5 rounded-lg border border-surface-200">
                    <div>
                        <span class="text-charcoal-muted block">File Upload</span>
                        <strong class="text-charcoal text-sm">{{ number_format($mod['file_count']) }}</strong>
                    </div>
                    <div>
                        <span class="text-charcoal-muted block">PDF Form</span>
                        <strong class="text-emerald-700 text-sm">{{ number_format($mod['pdf_count']) }}</strong>
                    </div>
                    <div>
                        <span class="text-charcoal-muted block">Ukuran</span>
                        <strong class="text-charcoal text-sm">{{ \App\Services\BackupArchiveService::formatBytes($mod['total_size']) }}</strong>
                    </div>
                </div>
                <form action="{{ route('admin.backup.download') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="module" value="{{ $mod['key'] }}">
                    <input type="hidden" name="year" value="{{ $yearFilter ?? 'all' }}">
                    <button type="submit"
                            @if($mod['total_items'] === 0) disabled @endif
                            class="btn-secondary btn-sm w-full justify-center inline-flex items-center gap-1.5 {{ $mod['total_items'] === 0 ? 'opacity-45 cursor-not-allowed' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Unduh ZIP Modul Ini</span>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Struktur Folder Info --}}
    <div class="card" x-data="{ showTree: false }">
        <div class="card-header flex items-center justify-between cursor-pointer" @click="showTree = !showTree">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <h3 class="card-title text-sm">Struktur Folder di Dalam Arsip ZIP</h3>
            </div>
            <button type="button" class="text-xs font-semibold text-primary-600 hover:underline">
                <span x-text="showTree ? 'Sembunyikan' : 'Lihat Struktur'"></span>
            </button>
        </div>
        <div x-show="showTree" x-collapse class="card-body bg-surface-50 border-t border-surface-200">
<pre class="text-xs font-mono text-charcoal overflow-x-auto leading-relaxed">SIMAK_Backup_....zip
├── 01_Penyusunan_PKPT/
│   ├── PKPT_Utama/&lt;Tahun - Judul PKPT&gt;/
│   └── Kertas_Kerja_PKPT/&lt;Tahun - Judul PKPT&gt;/
│       ├── 01_RJP/ ... 06_Arahan_Direktur/
├── 02_Layanan_Assurance/
│   ├── Audit_Terjadwal/
│   │   ├── 01_Surat_Tugas_Audit/&lt;Judul&gt;/ (STG-*.pdf + lampiran)
│   │   ├── 02_Program_Kerja_Audit/
│   │   ├── 03_Slide_Entry_Meeting/
│   │   ├── 04_Kertas_Kerja_Audit/
│   │   └── 05_Laporan_Hasil_Audit/
│   └── Audit_Tidak_Terjadwal/ ...
├── 03_Layanan_Advisory/
│   ├── 01_Berita_Acara_Konsultasi/&lt;Judul&gt;/ (BAK-*.pdf + lampiran)
│   └── 02_Laporan_Konsultasi/
├── 04_Kegiatan_Lainnya/
│   ├── Monitoring_Tindak_Lanjut/&lt;Judul&gt;/
│   │   ├── Monitoring_Tindak_Lanjut_*.pdf
│   │   ├── Lampiran_Dokumen/
│   │   └── Bukti_Auditee/&lt;Bidang - PIC&gt;/
│   ├── Pendamping_Eksternal/ (LHA_Auditor_Eksternal, Laporan_Pendamping)
│   └── Quality_Assurance/ (01_Surat_Tugas_QA [STQ-*.pdf] ... 04_Laporan_QA)
└── 05_Laporan_Kinerja/
    └── &lt;Tahun&gt;/&lt;Bulan_Judul&gt;/</pre>
        </div>
    </div>

</div>
@endsection
