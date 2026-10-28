@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumbs')
    <span class="breadcrumb-current">Dashboard</span>
@endsection

@section('content')

{{-- ============================================
     STATS CARDS
     ============================================ --}}
@unless($isKomisaris)
<div class="grid grid-cols-1 sm:grid-cols-2 {{ $isAuditee ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }} gap-4 mb-6">

    {{-- Total TL --}}
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="pr-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted line-clamp-2">
                    {{ $isAuditee ? 'Total TL Anda' : 'Total Tindak Lanjut' }}
                </p>
                <p class="stat-card-value">{{ number_format($followUpStats['total']) }}</p>
            </div>
            <div class="stat-card-icon bg-primary-50 flex-shrink-0">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/></svg>
            </div>
        </div>
        <div class="flex gap-3 mt-3">
            <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full font-medium">{{ $followUpStats['completed'] }} selesai</span>
            <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-medium">{{ $followUpStats['inProgress'] }} proses</span>
        </div>
    </div>

    {{-- TL Open --}}
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="pr-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted line-clamp-2">Belum Ditindaklanjuti</p>
                <p class="stat-card-value text-orange-600">{{ number_format($followUpStats['open']) }}</p>
            </div>
            <div class="stat-card-icon bg-orange-50 flex-shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-xs text-charcoal-muted mt-3">
            {{ $isAuditee ? 'Menunggu tindakan Anda' : 'Menunggu tindakan Auditee' }}
        </p>
    </div>

    {{-- TL Overdue --}}
    <div class="stat-card {{ $followUpStats['overdue'] > 0 ? 'border-l-4 border-l-red-500' : '' }}">
        <div class="flex items-start justify-between">
            <div class="pr-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-muted line-clamp-2">Melewati Jatuh Tempo</p>
                <p class="stat-card-value {{ $followUpStats['overdue'] > 0 ? 'text-red-600' : '' }}">{{ number_format($followUpStats['overdue']) }}</p>
            </div>
            <div class="stat-card-icon {{ $followUpStats['overdue'] > 0 ? 'bg-red-50' : 'bg-gray-50' }} flex-shrink-0">
                <svg class="w-5 h-5 {{ $followUpStats['overdue'] > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        @if($followUpStats['overdue'] > 0)
        <p class="text-xs text-red-500 mt-3 font-medium">⚠ Perlu perhatian segera</p>
        @else
        <p class="text-xs text-charcoal-muted mt-3">Tidak ada yang melewati batas</p>
        @endif
    </div>

    {{-- Realisasi PKPT — Berbeda warna untuk membedakan dari kelompok card Tindak Lanjut --}}
    @unless($isAuditee)
    <div class="stat-card !bg-gradient-to-br !from-primary-600 !via-primary-700 !to-primary-800 !border-primary-600/50 text-white shadow-md shadow-primary-900/15 relative overflow-hidden group">
        {{-- Decorative ambient glow --}}
        <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>

        <div class="flex items-start justify-between relative z-10">
            <div class="pr-3">
                <div class="flex items-center gap-1.5 mb-1.5">
                    <span class="text-[9.5px] font-bold uppercase tracking-wider bg-white/20 text-white px-2 py-0.5 rounded-full backdrop-blur-xs">
                        Program PKPT
                    </span>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary-100 line-clamp-2">Realisasi PKPT {{ $pkptStats['year'] }}</p>
                <p class="stat-card-value !text-white !mt-1 text-3xl font-black tracking-tight">{{ $pkptStats['realization_pct'] }}%</p>
            </div>
            <div class="stat-card-icon !bg-white/20 !text-white backdrop-blur-xs flex-shrink-0 border border-white/25 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div class="mt-3.5 relative z-10">
            <div class="flex justify-between text-xs text-primary-100 font-medium mb-1.5">
                <span>{{ $pkptStats['completed'] }} / {{ $pkptStats['total_items'] }} item selesai</span>
            </div>
            <div class="w-full bg-black/25 rounded-full h-1.5 overflow-hidden">
                <div class="bg-amber-300 h-1.5 rounded-full transition-all duration-700 shadow-2xs" style="width: {{ $pkptStats['realization_pct'] }}%"></div>
            </div>
        </div>
    </div>
    @endunless

</div>
@endunless

@if($isKomisaris)
{{-- ============================================
     KOMISARIS MENUS (5 CARDS)
     ============================================ --}}
<div class="max-w-5xl mx-auto mb-8 bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-surface-200 bg-surface-50">
        <h2 class="text-lg font-bold text-charcoal">Navigasi Utama</h2>
        <p class="text-sm text-charcoal-muted">Silakan pilih menu modul di bawah ini untuk melihat detail dan daftar dokumen.</p>
    </div>
    
    <div class="p-6 bg-surface-50/30">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-5">
    
    {{-- 1. Penyusunan PKPT --}}
    <div class="md:col-span-3 xl:col-span-2 card cursor-pointer border-transparent hover:border-primary-300 transition-all shadow-sm group" x-data="{ open: false }">
        <div class="card-body p-4 flex items-center justify-between" @click="open = !open">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-100 transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-semibold text-charcoal">Penyusunan PKPT</h3>
            </div>
            <svg class="w-4 h-4 text-charcoal-muted transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-collapse class="border-t border-surface-100 bg-surface-50 rounded-b-xl">
            <div class="flex flex-col p-2 space-y-1">
                <a href="{{ route('pkpt.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    PKPT
                </a>
                <a href="{{ route('pkpt.kertas-kerja.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Kertas Kerja PKPT
                </a>
                <a href="{{ route('pkpt.lainnya.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Lainnya
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Layanan Assurance --}}
    <div class="md:col-span-3 xl:col-span-2 card cursor-pointer border-transparent hover:border-green-300 transition-all shadow-sm group" x-data="{ open: false }">
        <div class="card-body p-4 flex items-center justify-between" @click="open = !open">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600 group-hover:bg-green-100 transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="font-semibold text-charcoal">Layanan Assurance</h3>
            </div>
            <svg class="w-4 h-4 text-charcoal-muted transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-collapse class="border-t border-surface-100 bg-surface-50 rounded-b-xl">
            <div class="flex flex-col p-2 space-y-1">
                <a href="{{ route('assurance.index', ['audit_type' => 'terjadwal']) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Audit Terjadwal
                </a>
                <a href="{{ route('assurance.index', ['audit_type' => 'tidak_terjadwal']) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Audit Tidak Terjadwal
                </a>
            </div>
        </div>
    </div>

    {{-- 3. Layanan Advisory --}}
    <div class="md:col-span-3 xl:col-span-2 card cursor-pointer border-transparent hover:border-purple-300 transition-all shadow-sm group" x-data="{ open: false }">
        <div class="card-body p-4 flex items-center justify-between" @click="open = !open">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 group-hover:bg-purple-100 transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <h3 class="font-semibold text-charcoal">Layanan Advisory</h3>
            </div>
            <svg class="w-4 h-4 text-charcoal-muted transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-collapse class="border-t border-surface-100 bg-surface-50 rounded-b-xl">
            <div class="flex flex-col p-2 space-y-1">
                <a href="{{ route('advisory.index', ['type' => 'bak']) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Berita Acara Konsultasi
                </a>
                <a href="{{ route('advisory.index', ['type' => 'lap-konsultasi']) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Laporan Konsultasi
                </a>
            </div>
        </div>
    </div>

    {{-- 4. Kegiatan Lainnya --}}
    <div class="md:col-span-3 xl:col-span-3 card cursor-pointer border-transparent hover:border-orange-300 transition-all shadow-sm group" x-data="{ open: false }">
        <div class="card-body p-4 flex items-center justify-between" @click="open = !open">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600 group-hover:bg-orange-100 transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/></svg>
                </div>
                <h3 class="font-semibold text-charcoal">Kegiatan Lainnya</h3>
            </div>
            <svg class="w-4 h-4 text-charcoal-muted transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-collapse class="border-t border-surface-100 bg-surface-50 rounded-b-xl">
            <div class="flex flex-col p-2 space-y-1">
                <a href="{{ route('other-activities.tl.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Monitoring Tindak Lanjut
                </a>
                <a href="{{ route('other-activities.pendamping.index', ['type' => 'lap-pendamping']) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Pendamping Eksternal
                </a>
                <a href="{{ route('other-activities.qa.index', ['type' => 'lap-qa']) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Quality Assurance (QA)
                </a>
            </div>
        </div>
    </div>

    {{-- 5. Laporan Kinerja --}}
    <div class="md:col-span-3 xl:col-span-3 card cursor-pointer border-transparent hover:border-gray-400 transition-all shadow-sm group" x-data="{ open: false }">
        <div class="card-body p-4 flex items-center justify-between" @click="open = !open">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 group-hover:bg-gray-200 transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="font-semibold text-charcoal">Laporan Kinerja</h3>
            </div>
            <svg class="w-4 h-4 text-charcoal-muted transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-collapse class="border-t border-surface-100 bg-surface-50 rounded-b-xl">
            <div class="flex flex-col p-2 space-y-1">
                <a href="{{ route('performance-reports.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-charcoal hover:bg-white hover:text-primary-600 rounded-md transition-colors">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Lihat Laporan Kinerja
                </a>
            </div>
        </div>
    </div>

        </div>
    </div>
</div>
@endif


{{-- ============================================
     CHARTS ROW
     ============================================ --}}
<div class="grid grid-cols-1 {{ $isKomisaris ? 'max-w-5xl mx-auto' : 'lg:grid-cols-3' }} gap-4 mb-6">

    @unless($isKomisaris)
    {{-- TL Status Chart --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <h2 class="card-title">Status Tindak Lanjut</h2>
            <span class="text-xs text-charcoal-muted">
                {{ $isAuditee ? 'Tindak Lanjut Anda' : 'Keseluruhan' }}
            </span>
        </div>
        <div class="card-body">
            <canvas id="followUpStatusChart" height="120"></canvas>
        </div>
    </div>
    @endunless

    {{-- Document Search Widget --}}
    <div class="card" id="doc-search-card">
        <div class="card-header">
            <h2 class="card-title flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Cari Dokumen
            </h2>
        </div>
        <div class="card-body p-0">

            <div class="px-4 pt-3 pb-2 space-y-2">

                {{-- Search Input --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-charcoal-muted pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input
                        id="doc-search-input"
                        type="text"
                        placeholder="Cari no, nama, atau tanggal dokumen..."
                        autocomplete="off"
                        class="w-full pl-9 pr-9 py-2 text-sm rounded-lg border border-surface-200 bg-surface-50 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all"
                    />
                    <span id="doc-search-spinner" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                        <svg class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                    </span>
                    <button id="doc-search-clear" class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-charcoal-muted hover:text-charcoal transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Dropdowns Row --}}
                <div class="grid grid-cols-2 gap-2">

                    {{-- Dropdown: Modul --}}
                    <div class="relative">
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-charcoal-muted block mb-0.5">Modul</label>
                        <div class="relative">
                            <select id="doc-module-select" class="doc-filter-select" {{ $isAuditee ? 'disabled' : '' }}>
                                @if($isAuditee)
                                    {{-- Auditee: hanya bisa lihat Advisory --}}
                                    <option value="advisory" selected>Layanan Advisory</option>
                                @else
                                    <option value="all">Semua Modul</option>
                                    <option value="pkpt">Penyusunan PKPT</option>
                                    <option value="assurance">Layanan Assurance</option>
                                    <option value="advisory">Layanan Advisory</option>
                                    <option value="kegiatan-lainnya">Kegiatan Lainnya</option>
                                    <option value="report">Laporan Kinerja</option>
                                @endif
                            </select>
                            <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-charcoal-muted">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Dropdown: Cari Berdasarkan --}}
                    <div class="relative">
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-charcoal-muted block mb-0.5">Cari Berdasarkan</label>
                        <div class="relative">
                            <select id="doc-field-select" class="doc-filter-select">
                                <option value="all">Semua Field</option>
                                <option value="no">No. Dokumen</option>
                                <option value="nama">Nama Dokumen</option>
                                <option value="tanggal">Tanggal</option>
                            </select>
                            <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-charcoal-muted">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Results Area --}}
            <div id="doc-search-results" class="overflow-y-auto" style="max-height: 220px;">
                <div id="doc-search-idle" class="px-4 pb-4 pt-2 text-center">
                    <svg class="w-9 h-9 mx-auto mb-2" style="color:#d1d5db" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-xs text-charcoal-muted">Ketik minimal 2 karakter untuk mencari dokumen</p>
                </div>
            </div>

        </div>
    </div>

</div>


{{-- ============================================
     UPCOMING DEADLINES TABLE (Full)
     ============================================ --}}
@unless($isKomisaris)
<div class="card">
    <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="card-title">Tindak Lanjut Mendekati Jatuh Tempo</h2>
        </div>
        <div>
            <a href="{{ route($isAuditee ? 'auditee.index' : 'other-activities.tl.index') }}" class="btn-secondary btn-sm">Lihat Semua</a>
        </div>
    </div>

    @if($followUpStats['upcomingDeadlines']->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="empty-state-title">Semua Tindak Lanjut Aman</p>
            <p class="empty-state-description">Tidak ada TL yang mendekati jatuh tempo dalam 7 hari ke depan.</p>
        </div>
    @else
        <div class="hidden md:block table-wrapper">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Temuan</th>
                        <th>Bidang</th>
                        <th>Jatuh Tempo</th>
                        <th>Sisa Hari</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($followUpStats['upcomingDeadlines'] as $tl)
                    <tr>
                        <td class="max-w-xs">
                            <p class="font-medium text-charcoal truncate">{{ $tl->finding }}</p>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $tl->auditeeDepartment?->code ?? '-' }}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            {{ $tl->due_date->format('d M Y') }}
                        </td>
                        <td>
                            @php $days = $tl->days_remaining; @endphp
                            <span class="font-semibold {{ $days <= 3 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $days <= 0 ? 'Terlambat' : $days . ' hari' }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $tl->status_color }}">{{ $tl->status_label }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('auditee.show', $tl) }}" class="btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-4">
            @foreach($followUpStats['upcomingDeadlines'] as $tl)
            @php $days = $tl->days_remaining; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
                <div class="p-4">
                    <div class="mb-3">
                        <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">{{ $tl->finding }}</h3>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Bidang</span>
                            <span class="badge badge-primary">{{ $tl->auditeeDepartment?->code ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Jatuh Tempo</span>
                            <span class="font-medium text-charcoal">{{ $tl->due_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                            <span class="text-charcoal-muted">Sisa Hari</span>
                            <span class="font-semibold {{ $days <= 3 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $days <= 0 ? 'Terlambat' : $days . ' hari' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-charcoal-muted">Status</span>
                            <span class="flex-shrink-0 {{ $tl->status_color }}">{{ $tl->status_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-50 px-4 py-3 flex items-center justify-end border-t border-surface-200">
                    <a href="{{ route('auditee.show', $tl) }}" class="btn-secondary btn-sm">Lihat Detail</a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endunless

@endsection

@push('scripts')
<script>
window.addEventListener('load', function () {

    @unless($isKomisaris)
    // Follow-up Status Bar Chart
    new Chart(document.getElementById('followUpStatusChart'), {
        type: 'bar',
        data: {
            labels: ['Belum Ditindaklanjuti', 'Dalam Proses', 'Selesai', 'Terlambat'],
            datasets: [{
                label: 'Jumlah TL',
                data: [
                    {{ $followUpStats['open'] }},
                    {{ $followUpStats['inProgress'] }},
                    {{ $followUpStats['completed'] }},
                    {{ $followUpStats['overdue'] }},
                ],
                backgroundColor: [
                    'rgba(249, 115, 22, 0.85)',  // orange - open
                    'rgba(37, 99, 235, 0.85)',    // blue - in progress
                    'rgba(22, 163, 74, 0.85)',    // green - completed
                    'rgba(220, 38, 38, 0.85)',    // red - overdue
                ],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                },
                x: {
                    grid: { display: false },
                }
            }
        }
    });
    @endunless


});
</script><style>
/* Filter Dropdown */
.doc-filter-select {
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
    font-size: 0.75rem;
    font-weight: 500;
    color: #374151;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.3rem 1.75rem 0.3rem 0.65rem;
    cursor: pointer;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.doc-filter-select:hover {
    border-color: #a5f3fc;
    background: #fff;
}
.doc-filter-select:focus {
    border-color: #158b9f;
    box-shadow: 0 0 0 3px rgba(21,139,159,.12);
    background: #fff;
}
.doc-filter-select:disabled {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
    opacity: 1;
    border-color: #e5e7eb;
}
/* Module badge colours on result cards */
.mod-badge {
    font-size: 0.62rem; font-weight: 600;
    padding: 1px 6px; border-radius: 9999px; letter-spacing: .03em;
}
.mod-pkpt       { background:#dbeafe; color:#1d4ed8; }
.mod-assurance  { background:#dcfce7; color:#166534; }
.mod-advisory   { background:#f3e8ff; color:#7e22ce; }
.mod-other      { background:#ffedd5; color:#c2410c; }
.mod-report     { background:#f1f5f9; color:#475569; }
.mod-default    { background:#f3f4f6; color:#374151; }
/* Search Result Items */
.doc-result-item {
    display: flex; align-items: flex-start; gap: 0.65rem;
    padding: 0.6rem 1rem; border-bottom: 1px solid #f3f4f6;
    text-decoration: none; transition: background .15s ease; cursor: pointer;
}
.doc-result-item:last-child { border-bottom: none; }
.doc-result-item:hover { background: #f9fafb; }
.doc-result-icon {
    width: 32px; height: 32px; border-radius: 8px; background: #ecfeff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.doc-result-body { flex: 1; min-width: 0; }
.doc-result-no   { font-size: 0.7rem; color: #158b9f; font-weight: 600; letter-spacing: .02em; }
.doc-result-name { font-size: 0.82rem; font-weight: 500; color: #1f2937;
                   white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 1px 0; }
.doc-result-meta { font-size: 0.7rem; color: #6b7280; display: flex; gap: .4rem; flex-wrap: wrap; align-items: center; margin-top:2px; }
mark.search-hl { background: #fef08a; border-radius: 2px; padding: 0 1px; color: inherit; font-style: normal; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
.doc-result-item { animation: fadeSlideIn .18s ease both; }
</style>

<script>
(function () {
    var searchUrl = '{{ route("dashboard.search") }}';
    var input      = document.getElementById('doc-search-input');
    var spinner    = document.getElementById('doc-search-spinner');
    var clearBtn   = document.getElementById('doc-search-clear');
    var results    = document.getElementById('doc-search-results');
    var idle       = document.getElementById('doc-search-idle');

    var modSelect   = document.getElementById('doc-module-select');
    var fieldSelect = document.getElementById('doc-field-select');

    var debounceTimer = null;
    var activeModule  = 'all';
    var activeField   = 'all';
    var abortCtrl     = null;

    // ── Module dropdown ───────────────────────────
    modSelect.addEventListener('change', function() {
        activeModule = this.value;
        triggerSearch();
    });

    // ── Field dropdown ────────────────────────────
    fieldSelect.addEventListener('change', function() {
        activeField = this.value;
        triggerSearch();
    });

    // ── Input events ──────────────────────────────
    input.addEventListener('input', function() {
        var q = input.value.trim();
        clearBtn.classList.toggle('hidden', q.length === 0);
        spinner.classList.add('hidden');
        clearTimeout(debounceTimer);
        if (q.length < 2) { resetToIdle(); return; }
        debounceTimer = setTimeout(function() { doSearch(q); }, 350);
    });

    clearBtn.addEventListener('click', function() {
        input.value = '';
        clearBtn.classList.add('hidden');
        resetToIdle();
        input.focus();
    });

    function triggerSearch() {
        var q = input.value.trim();
        if (q.length >= 2) doSearch(q);
    }

    // ── Core search ───────────────────────────────
    function doSearch(q) {
        if (q.length < 2) { resetToIdle(); return; }
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        spinner.classList.remove('hidden');
        clearBtn.classList.add('hidden');
        var url = searchUrl
            + '?q='      + encodeURIComponent(q)
            + '&type='   + activeField
            + '&module=' + activeModule;
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            signal: abortCtrl.signal
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            spinner.classList.add('hidden');
            clearBtn.classList.toggle('hidden', input.value.trim().length === 0);
            renderResults(data.results, q, data.total);
        })
        .catch(function(err) {
            if (err.name === 'AbortError') return;
            spinner.classList.add('hidden');
            clearBtn.classList.remove('hidden');
            renderError();
        });
    }

    // ── Module badge helper ───────────────────────
    var modBadgeClass = {
        'pkpt':          'mod-pkpt',
        'assurance':     'mod-assurance',
        'advisory':      'mod-advisory',
        'pendamping':    'mod-other',
        'qa':            'mod-other',
        'tindak-lanjut': 'mod-other',
        'report':        'mod-report'
    };
    function moduleBadge(mod, label) {
        var cls = modBadgeClass[mod] || 'mod-default';
        return '<span class="mod-badge ' + cls + '">' + escHtml(label) + '</span>';
    }

    // ── Render results ────────────────────────────
    function renderResults(items, q, total) {
        results.querySelectorAll('.doc-result-item, .doc-search-empty, .doc-search-count').forEach(function(el) { el.remove(); });
        idle.classList.add('hidden');

        if (!items || items.length === 0) {
            var empty = document.createElement('div');
            empty.className = 'doc-search-empty';
            empty.style.cssText = 'padding:1.25rem 1rem;text-align:center;';
            empty.innerHTML =
                '<svg style="width:2rem;height:2rem;color:#d1d5db;margin:0 auto .5rem" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                '</svg>' +
                '<p style="font-size:.85rem;font-weight:500;color:#1f2937">Tidak ditemukan</p>' +
                '<p style="font-size:.75rem;color:#6b7280;margin-top:4px">Tidak ada dokumen cocok dengan &ldquo;<strong>' + escHtml(q) + '</strong>&rdquo;</p>';
            results.appendChild(empty);
            return;
        }

        // Count header
        var count = document.createElement('div');
        count.className = 'doc-search-count';
        count.style.cssText = 'padding:.35rem 1rem;font-size:.68rem;color:#6b7280;background:#f9fafb;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;';
        count.innerHTML =
            '<span>Ditemukan <strong style="color:#1f2937">' + total + '</strong> dokumen</span>' +
            '<span style="color:#158b9f;font-weight:500">&ldquo;' + escHtml(q) + '&rdquo;</span>';
        results.appendChild(count);

        items.forEach(function(doc, i) {
            var item = document.createElement('a');
            item.className = 'doc-result-item';
            item.href = doc.url || '#';
            item.style.animationDelay = (i * 30) + 'ms';
            item.innerHTML =
                '<div class="doc-result-icon">' +
                    '<svg class="w-4 h-4" style="color:#158b9f" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
                    '</svg>' +
                '</div>' +
                '<div class="doc-result-body">' +
                    '<div style="display:flex;align-items:center;gap:.4rem;margin-bottom:1px">' +
                        '<span class="doc-result-no">' + highlight(doc.no, q) + '</span>' +
                        moduleBadge(doc.module, doc.module_label) +
                    '</div>' +
                    '<div class="doc-result-name" title="' + escHtml(doc.nama) + '">' + highlight(doc.nama, q) + '</div>' +
                    '<div class="doc-result-meta">' +
                        '<span>' + escHtml(doc.type) + '</span>' +
                        '<span>&middot;</span>' +
                        '<span>' + escHtml(doc.tanggal) + '</span>' +
                        '<span>&middot;</span>' +
                        '<span class="' + escHtml(doc.status_color) + '">' + escHtml(doc.status_label) + '</span>' +
                    '</div>' +
                '</div>';
            results.appendChild(item);
        });
    }

    function renderError() {
        results.querySelectorAll('.doc-result-item, .doc-search-empty, .doc-search-count').forEach(function(el) { el.remove(); });
        idle.classList.add('hidden');
        var err = document.createElement('div');
        err.style.cssText = 'padding:1rem;text-align:center;font-size:.75rem;color:#ef4444';
        err.textContent = 'Terjadi kesalahan, silakan coba lagi.';
        results.appendChild(err);
    }

    function resetToIdle() {
        results.querySelectorAll('.doc-result-item, .doc-search-empty, .doc-search-count').forEach(function(el) { el.remove(); });
        idle.classList.remove('hidden');
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function highlight(text, q) {
        if (!text || !q) return escHtml(text);
        var safe  = escHtml(text);
        var safeQ = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return safe.replace(new RegExp(safeQ, 'gi'), function(m) { return '<mark class="search-hl">' + m + '</mark>'; });
    }
})();
</script>
@endpush
