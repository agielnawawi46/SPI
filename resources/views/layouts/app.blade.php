<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIMAK (Sistem Informasi Manajemen Audit & Kepatuhan)</title>
    <meta name="description" content="@yield('meta_description', 'SIMAK - Sistem Informasi Manajemen Audit & Kepatuhan PT Pelayanan Energi Batam')">
    <link rel="icon" type="image/png" href="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}">
    <link rel="shortcut icon" href="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $currentActive = 'dashboard';
    if(request()->routeIs('pkpt*')) $currentActive = 'pkpt';
    elseif(request()->routeIs('assurance*')) $currentActive = 'assurance';
    elseif(request()->routeIs('advisory*')) $currentActive = 'advisory';
    elseif(request()->routeIs('auditee*')) $currentActive = 'auditee';
    elseif(request()->routeIs('other-activities*')) $currentActive = 'other-activities';
    elseif(request()->routeIs('performance-reports*')) $currentActive = 'performance-reports';
    elseif(request()->routeIs('admin*')) $currentActive = 'admin';

    $currentAuditType = request('audit_type');
    $currentDocType = request('type');
    
    if (isset($document) && $document instanceof \App\Models\Document) {
        $currentAuditType = $document->audit_type ?? $currentAuditType;
        $currentDocType = $document->documentType->slug ?? $currentDocType;
    } elseif (isset($selectedType) && $selectedType instanceof \App\Models\DocumentType) {
        $currentDocType = $selectedType->slug ?? $currentDocType;
    } elseif (isset($typeSlug)) {
        $currentDocType = $typeSlug;
    }

    if (isset($auditType) && is_string($auditType)) {
        $currentAuditType = $auditType;
    }

    // HITUNG NOTIFIKASI PENDING APPROVAL
    $user = auth()->user();
    $hasPendingPkpt = false;
    $hasPendingAssurance = false;
    $hasPendingAdvisory = false;
    $hasPendingOther = false;
    $hasPendingAuditee = false;

    if ($user) {
        $roleStatus = null;
        if ($user->hasRole('kspi')) $roleStatus = 'pending_kspi';
        elseif ($user->hasRole('direktur')) $roleStatus = 'pending_direktur';
        elseif ($user->hasRole('auditor')) $roleStatus = 'pending_auditor';
        elseif ($user->hasRole('manager')) $roleStatus = 'pending_manager';

        if ($roleStatus) {
            if (in_array($roleStatus, ['pending_kspi', 'pending_direktur']) && class_exists(\App\Models\Pkpt::class)) {
                $hasPendingPkpt = \App\Models\Pkpt::where('status', $roleStatus)->exists();
            }

            if (in_array($roleStatus, ['pending_kspi', 'pending_direktur']) && class_exists(\App\Models\Document::class)) {
                $hasPendingAssurance = \App\Models\Document::whereIn('audit_type', ['terjadwal', 'tidak_terjadwal'])->where('status', $roleStatus)->exists();
                $hasPendingAdvisory = \App\Models\Document::where('audit_type', 'advisory')->where('status', $roleStatus)->exists();
                $hasPendingOther = \App\Models\Document::where('audit_type', 'qa')->where('status', $roleStatus)->exists();
            }

            if (class_exists(\App\Models\FollowUp::class)) {
                $pendingFollowUp = \App\Models\FollowUp::where('approval_status', $roleStatus);
                if ($user->hasRole('manager')) {
                    $hasPendingAuditee = $pendingFollowUp->where('assigned_to', $user->id)->exists();
                } else {
                    $hasPendingOther = $hasPendingOther || (clone $pendingFollowUp)->exists();
                }
            }
        }
    }
@endphp
<body class="h-full bg-surface-50" x-data="{ sidebarOpen: false, activeMenu: '{{ $currentActive }}' }">

{{-- Mobile Sidebar Overlay --}}
<div x-show="sidebarOpen" x-transition.opacity 
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-charcoal/50 z-[90] lg:hidden" style="display: none;">
</div>

{{-- ============================================
     SIDEBAR
     ============================================ --}}
<aside class="sidebar" id="app-sidebar" :class="{'mobile-open': sidebarOpen}">
    {{-- Logo --}}
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full group overflow-hidden" title="SIMAK — Sistem Informasi Manajemen Audit & Kepatuhan">
            <img src="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}" 
                 alt="Logo SIMAK PLN" 
                 class="flex-shrink-0 w-10 h-10 object-contain drop-shadow-2xs transition-transform duration-200 group-hover:scale-105">
            <div class="min-w-0 flex-1">
                <p class="sidebar-logo-text text-base font-black tracking-tight text-charcoal leading-none">SIMAK</p>
                <p class="text-charcoal-muted text-[10px] leading-tight font-medium mt-1 line-clamp-2">
                    Sistem Informasi Manajemen Audit & Kepatuhan
                </p>
            </div>
        </a>
    </div>

    {{-- Nav Menu --}}
    <nav class="flex-1 py-4 overflow-y-auto space-y-1 px-2">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" @click="activeMenu = 'dashboard'" class="sidebar-link" :class="{ 'active': activeMenu === 'dashboard' }">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        @can('pkpt.view')
        {{-- Penyusunan PKPT --}}
        <div x-data="{ open: {{ request()->routeIs('pkpt*') ? 'true' : 'false' }} }">
            <div @click="open = !open; activeMenu = 'pkpt'" class="sidebar-link cursor-pointer" :class="{ 'active': activeMenu === 'pkpt' }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Penyusunan PKPT
                @if($hasPendingPkpt)
                <span class="w-2 h-2 bg-red-500 rounded-full ml-auto"></span>
                @endif
            </div>
            <div x-show="open" x-collapse class="mt-1.5 space-y-1.5">
                <a href="{{ route('pkpt.index') }}" class="sidebar-sublink {{ request()->routeIs('pkpt.index') ? 'active' : '' }}">PKPT</a>
                <a href="{{ route('pkpt.kertas-kerja.index') }}" class="sidebar-sublink {{ request()->routeIs('pkpt.kertas-kerja.index') || request()->routeIs('pkpt.kertas-kerja') ? 'active' : '' }}">Kertas Kerja PKPT</a>
                <a href="{{ route('pkpt.lainnya.index') }}" class="sidebar-sublink {{ request()->routeIs('pkpt.lainnya*') ? 'active' : '' }}">Lainnya</a>
            </div>
        </div>
        @endcan

        @can('assurance.view')
        {{-- Layanan Assurance --}}
        <div x-data="{ open: {{ request()->routeIs('assurance*') ? 'true' : 'false' }} }">
            <div @click="open = !open; activeMenu = 'assurance'" class="sidebar-link cursor-pointer" :class="{ 'active': activeMenu === 'assurance' }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Layanan Assurance
                @if($hasPendingAssurance)
                <span class="w-2 h-2 bg-red-500 rounded-full ml-auto"></span>
                @endif
            </div>
            <div x-show="open" x-collapse class="mt-1.5 space-y-1.5">
                {{-- Audit Terjadwal --}}
                <div x-data="{ subOpen: {{ $currentAuditType == 'terjadwal' ? 'true' : 'false' }} }">
                    <div @click="subOpen = !subOpen" class="sidebar-sublink cursor-pointer flex justify-between items-center" :class="{ 'active': '{{ $currentAuditType }}' == 'terjadwal' && !subOpen }">
                        Audit Terjadwal
                        <svg :class="{'rotate-180': subOpen}" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="subOpen" x-collapse class="pl-4 mt-1 space-y-1">
                        <a href="{{ route('assurance.index', ['audit_type' => 'terjadwal', 'type' => 'surat-tugas']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'surat-tugas' && $currentAuditType == 'terjadwal' ? 'active text-primary-300' : '' }}">Surat Tugas Audit</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'terjadwal', 'type' => 'program-kerja']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'program-kerja' && $currentAuditType == 'terjadwal' ? 'active text-primary-300' : '' }}">Program Kerja Audit</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'terjadwal', 'type' => 'slide-entry']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'slide-entry' && $currentAuditType == 'terjadwal' ? 'active text-primary-300' : '' }}">Slide Entry Meeting</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'terjadwal', 'type' => 'kertas-kerja-audit']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'kertas-kerja-audit' && $currentAuditType == 'terjadwal' ? 'active text-primary-300' : '' }}">Kertas Kerja Audit</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'terjadwal', 'type' => 'lha']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'lha' && $currentAuditType == 'terjadwal' ? 'active text-primary-300' : '' }}">Laporan Hasil Audit</a>
                    </div>
                </div>

                {{-- Audit Tidak Terjadwal --}}
                <div x-data="{ subOpen: {{ $currentAuditType == 'tidak_terjadwal' ? 'true' : 'false' }} }">
                    <div @click="subOpen = !subOpen" class="sidebar-sublink cursor-pointer flex justify-between items-center" :class="{ 'active': '{{ $currentAuditType }}' == 'tidak_terjadwal' && !subOpen }">
                        Audit Tidak Terjadwal
                        <svg :class="{'rotate-180': subOpen}" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="subOpen" x-collapse class="pl-4 mt-1 space-y-1">
                        <a href="{{ route('assurance.index', ['audit_type' => 'tidak_terjadwal', 'type' => 'surat-tugas']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'surat-tugas' && $currentAuditType == 'tidak_terjadwal' ? 'active text-primary-300' : '' }}">Surat Tugas Audit</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'tidak_terjadwal', 'type' => 'program-kerja']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'program-kerja' && $currentAuditType == 'tidak_terjadwal' ? 'active text-primary-300' : '' }}">Program Kerja Audit</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'tidak_terjadwal', 'type' => 'slide-entry']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'slide-entry' && $currentAuditType == 'tidak_terjadwal' ? 'active text-primary-300' : '' }}">Slide Entry Meeting</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'tidak_terjadwal', 'type' => 'kertas-kerja-audit']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'kertas-kerja-audit' && $currentAuditType == 'tidak_terjadwal' ? 'active text-primary-300' : '' }}">Kertas Kerja Audit</a>
                        <a href="{{ route('assurance.index', ['audit_type' => 'tidak_terjadwal', 'type' => 'lha']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'lha' && $currentAuditType == 'tidak_terjadwal' ? 'active text-primary-300' : '' }}">Laporan Hasil Audit</a>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        @can('advisory.view')
        {{-- Layanan Advisory --}}
        <div x-data="{ open: {{ request()->routeIs('advisory*') ? 'true' : 'false' }} }">
            <div @click="open = !open; activeMenu = 'advisory'" class="sidebar-link cursor-pointer" :class="{ 'active': activeMenu === 'advisory' }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Layanan Advisory
                @if($hasPendingAdvisory)
                <span class="w-2 h-2 bg-red-500 rounded-full ml-auto"></span>
                @endif
            </div>
            <div x-show="open" x-collapse class="mt-1.5 space-y-1.5">
                <a href="{{ route('advisory.index', ['type' => 'bak']) }}" class="sidebar-sublink {{ $currentDocType == 'bak' ? 'active' : '' }}">Berita Acara Konsultasi</a>
                @unlessrole('manager|asman|staff')
                <a href="{{ route('advisory.index', ['type' => 'lap-konsultasi']) }}" class="sidebar-sublink {{ $currentDocType == 'lap-konsultasi' ? 'active' : '' }}">Laporan Konsultasi</a>
                @endunless
            </div>
        </div>
        @endcan

        @can('follow_up.view')
        {{-- Auditee: Tindak Lanjut (for Auditee role) --}}
        @role('manager|asman|staff')
        <a href="{{ route('auditee.index') }}" @click="activeMenu = 'auditee'" class="sidebar-link" :class="{ 'active': activeMenu === 'auditee' }">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/></svg>
            Tindak Lanjut
            @if($hasPendingAuditee)
            <span class="w-2 h-2 bg-red-500 rounded-full ml-auto"></span>
            @endif
        </a>
        @endrole
        @endcan

        @can('other_activity.view')
        {{-- Kegiatan Lainnya (for SPI team) --}}
        <div x-data="{ open: {{ request()->routeIs('other-activities*') ? 'true' : 'false' }} }">
            <div @click="open = !open; activeMenu = 'other-activities'" class="sidebar-link cursor-pointer" :class="{ 'active': activeMenu === 'other-activities' }">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"/></svg>
                Kegiatan Lainnya
                @if($hasPendingOther)
                <span class="w-2 h-2 bg-red-500 rounded-full ml-auto"></span>
                @endif
            </div>
            <div x-show="open" x-collapse class="mt-1.5 space-y-1.5">
                @can('other_activity.view')
                <a href="{{ route('other-activities.tl.index') }}" class="sidebar-sublink {{ request()->routeIs('other-activities.tl*') ? 'active' : '' }}">Monitoring Tindak Lanjut</a>
                @endcan
                
                @can('assurance.view')
                {{-- Pendamping Eksternal --}}
                <div x-data="{ subOpen: {{ request()->routeIs('other-activities.pendamping*') ? 'true' : 'false' }} }">
                    <div @click="subOpen = !subOpen" class="sidebar-sublink cursor-pointer flex justify-between items-center" :class="{ 'active': request()->routeIs('other-activities.pendamping*') && !subOpen }">
                        Pendamping Eksternal
                        <svg :class="{'rotate-180': subOpen}" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="subOpen" x-collapse class="pl-4 mt-1 space-y-1">
                        <a href="{{ route('other-activities.pendamping.index', ['type' => 'lha-eksternal']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'lha-eksternal' ? 'active text-primary-300' : '' }}">LHA Auditor Eksternal</a>
                        <a href="{{ route('other-activities.pendamping.index', ['type' => 'lap-pendamping']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'lap-pendamping' ? 'active text-primary-300' : '' }}">Laporan Pendamping</a>
                    </div>
                </div>

                {{-- Quality Assurance (QA) --}}
                <div x-data="{ subOpen: {{ request()->routeIs('other-activities.qa*') ? 'true' : 'false' }} }">
                    <div @click="subOpen = !subOpen" class="sidebar-sublink cursor-pointer flex justify-between items-center" :class="{ 'active': request()->routeIs('other-activities.qa*') && !subOpen }">
                        Quality Assurance (QA)
                        <svg :class="{'rotate-180': subOpen}" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="subOpen" x-collapse class="pl-4 mt-1 space-y-1">
                        <a href="{{ route('other-activities.qa.index', ['type' => 'st-qa']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'st-qa' ? 'active text-primary-300' : '' }}">Surat Tugas QA</a>
                        <a href="{{ route('other-activities.qa.index', ['type' => 'pk-qa']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'pk-qa' ? 'active text-primary-300' : '' }}">Program Kerja QA</a>
                        <a href="{{ route('other-activities.qa.index', ['type' => 'kk-qa']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'kk-qa' ? 'active text-primary-300' : '' }}">Kertas Kerja QA</a>
                        <a href="{{ route('other-activities.qa.index', ['type' => 'lap-qa']) }}" class="sidebar-sublink text-[13px] {{ $currentDocType == 'lap-qa' ? 'active text-primary-300' : '' }}">Laporan Hasil QA</a>
                    </div>
                </div>
                @endcan
            </div>
        </div>
        @endcan

        @can('report.view')
        {{-- Laporan Kinerja --}}
        <a href="{{ route('performance-reports.index') }}" @click="activeMenu = 'performance-reports'" class="sidebar-link" :class="{ 'active': activeMenu === 'performance-reports' }">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Laporan Kinerja
        </a>
        @endcan

        @role('admin')
        {{-- Pengaturan Sistem --}}
        <div class="mt-4 pt-2 border-t border-surface-200">
            <p class="sidebar-section-title !pt-2">Pengaturan Sistem</p>
            <div x-data="{ open: {{ request()->routeIs('admin*') ? 'true' : 'false' }} }">
                <div @click="open = !open; activeMenu = 'admin'" class="sidebar-link cursor-pointer !mx-0 !px-5" :class="{ 'active': activeMenu === 'admin' }">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Administrasi
                </div>
                <div x-show="open" x-collapse class="mt-1.5 space-y-1.5">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.users*') ? 'active' : '' }}">Manajemen User</a>
                    <a href="{{ route('admin.audit-trail') }}" class="sidebar-sublink {{ request()->routeIs('admin.audit-trail*') ? 'active' : '' }}">Audit Trail</a>
                </div>
            </div>
        </div>
        @endrole

    </nav>

    {{-- User Info at bottom --}}
    <div class="border-t border-surface-200 p-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-50 border border-primary-100 flex items-center justify-center flex-shrink-0">
                <span class="text-primary-700 text-xs font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-charcoal text-xs font-medium truncate">{{ Auth::user()->name }}</p>
                <p class="text-charcoal-muted text-xs truncate">{{ ucfirst(str_replace('_', ' ', Auth::user()->roles->first()?->name ?? '-')) }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- ============================================
     TOP NAVBAR
     ============================================ --}}
<header class="topbar">
    {{-- Breadcrumbs / Page Title --}}
    <div class="flex items-center min-w-0 mr-4">
        {{-- Mobile Hamburger --}}
        <button @click="sidebarOpen = true" class="lg:hidden text-charcoal hover:text-primary-500 focus:outline-none mr-3" aria-label="Buka Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        
        @hasSection('breadcrumbs')
            <nav class="flex items-center flex-wrap gap-1.5 text-xs sm:text-sm text-charcoal-muted truncate [&_.breadcrumb-item]:text-charcoal-muted [&_.breadcrumb-link]:text-charcoal-muted [&_.breadcrumb-link]:hover:text-primary-600 [&_.breadcrumb-link]:font-medium [&_.breadcrumb-link]:transition-colors [&_.breadcrumb-separator]:text-charcoal-muted/40 [&_.breadcrumb-separator]:select-none [&_.breadcrumb-current]:text-charcoal [&_.breadcrumb-current]:font-bold [&_.breadcrumb-current]:text-sm sm:[&_.breadcrumb-current]:text-base" aria-label="Breadcrumb">
                @yield('breadcrumbs')
            </nav>
        @else
            <h1 class="page-title text-base sm:text-lg font-bold text-charcoal truncate">@yield('page_title', 'Dashboard')</h1>
        @endif
    </div>

    {{-- Right Side: Notifications + Logout --}}
    <div class="flex items-center justify-end gap-3 flex-shrink-0">

        {{-- Notification Bell --}}
        <div class="relative" x-data="notificationDropdown()" @click.outside="close()">
            <button @click="toggle()" id="notif-btn" class="btn-icon btn-secondary relative" aria-label="Notifikasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span id="notif-count" class="notif-badge hidden">0</span>
            </button>

            {{-- Notification Dropdown --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-card-md border border-surface-200 z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-surface-100">
                    <h3 class="text-sm font-semibold text-charcoal">Notifikasi</h3>
                    <button id="mark-all-read-btn" class="text-xs text-primary-500 hover:underline">Tandai Semua Dibaca</button>
                </div>
                <div id="notif-list" class="max-h-80 overflow-y-auto">
                    <div class="px-4 py-8 text-center text-sm text-charcoal-muted">
                        Memuat notifikasi...
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-surface-100 text-center">
                    <a href="#" class="text-xs text-primary-500 hover:underline">Lihat semua notifikasi</a>
                </div>
            </div>
        </div>

        {{-- Logout Button --}}
        <form action="{{ route('logout') }}" method="POST" class="ml-1 border-l border-surface-200 pl-3">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Keluar dari Aplikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </form>

    </div>
</header>

{{-- ============================================
     MAIN CONTENT
     ============================================ --}}
<main class="main-content">

    {{-- Flash Messages --}}
    @if (session('success'))
        <div x-data="flashMessage()" x-show="show" x-transition class="alert alert-success mb-5">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="dismiss()" class="ml-2 text-green-600/60 hover:text-green-800">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div x-data="flashMessage()" x-show="show" x-transition class="alert alert-error mb-5">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1">{{ session('error') }}</span>
            <button @click="dismiss()" class="ml-2 text-red-600/60 hover:text-red-800">&times;</button>
        </div>
    @endif
    @if (session('warning'))
        <div x-data="flashMessage()" x-show="show" x-transition class="alert alert-warning mb-5">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="flex-1">{{ session('warning') }}</span>
            <button @click="dismiss()" class="ml-2 text-amber-600/60 hover:text-amber-800">&times;</button>
        </div>
    @endif

    @yield('content')
</main>

{{-- ============================================
     FOOTER
     ============================================ --}}
<footer class="ml-0 lg:ml-64 mt-auto border-t border-surface-200 bg-white transition-all duration-300">
    <div class="px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
        
        <p class="text-xs font-medium text-charcoal-muted text-center md:text-left">
            &copy; {{ date('Y') }} PT Pelayanan Energi Batam. Seluruh hak cipta dilindungi.
        </p>

        <p class="text-xs text-charcoal-muted text-center md:text-right flex items-center justify-center gap-1.5">
            SIMAK &bull; Sistem Informasi Manajemen Audit & Kepatuhan
        </p>

    </div>
</footer>

@stack('scripts')

{{-- ============================================
     CUSTOM ALERT DIALOG (Replaces browser native alert)
     ============================================ --}}
<div id="custom-alert-overlay"
     style="display:none; position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center; padding:1rem;"
     class="bg-charcoal/60 backdrop-blur-sm">

    <div id="custom-alert-box"
         style="transform: scale(0.95); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto p-0 overflow-hidden border border-surface-200">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 pt-6 pb-4">
            <div id="alert-icon-wrap" class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-blue-100">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-charcoal">Informasi</p>
                <p class="text-xs text-charcoal-muted">SIMAK &bull; PT Pelayanan Energi Batam</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 pb-2">
            <p id="custom-alert-message" class="text-sm text-charcoal leading-relaxed"></p>
        </div>

        {{-- Divider --}}
        <div class="border-t border-surface-100 mx-6 my-4"></div>

        {{-- Button --}}
        <div class="flex items-center justify-end px-6 pb-6">
            <button id="custom-alert-ok"
                    class="px-5 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm">
                OK
            </button>
        </div>
    </div>
</div>

{{-- ============================================
     CUSTOM UPLOAD LOADING OVERLAY
     ============================================ --}}
<div id="custom-upload-overlay"
     style="display:none; position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center; padding:1rem; opacity:0; transition: opacity 0.25s ease;"
     class="bg-charcoal/60 backdrop-blur-sm">

    <div id="custom-upload-box"
         style="transform: scale(0.95); opacity: 0; transition: transform 0.25s ease, opacity 0.25s ease;"
         class="bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-auto p-6 sm:p-7 text-center border border-surface-200 flex flex-col items-center">

        {{-- Animated Icon Illustration --}}
        <div class="relative w-20 h-20 mb-4 flex items-center justify-center">
            {{-- Spinning Primary Ring --}}
            <div class="absolute inset-0 rounded-full border-4 border-primary-100 border-t-primary-500 animate-spin"></div>
            {{-- Counter-rotating Dashed Emerald Ring --}}
            <div class="absolute inset-1.5 rounded-full border-2 border-dashed border-emerald-300 animate-spin" style="animation-duration: 3s; animation-direction: reverse;"></div>
            {{-- Inner Icon with bouncing arrow --}}
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center text-primary-600 shadow-inner">
                <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
        </div>

        {{-- Heading & Subtitle --}}
        <h3 id="upload-loading-title" class="text-base sm:text-lg font-bold text-charcoal tracking-tight mb-1">
            Sedang Mengunggah Dokumen...
        </h3>
        <p id="upload-loading-subtitle" class="text-xs text-charcoal-muted leading-relaxed mb-4">
            Mohon tunggu sebentar, berkas sedang dikirim dan diproses oleh sistem.
        </p>

        {{-- Animated Indeterminate Progress Bar --}}
        <div class="w-full bg-surface-100 rounded-full h-2 overflow-hidden mb-4 relative">
            <div class="animate-upload-shimmer bg-gradient-to-r from-primary-500 via-teal-400 to-emerald-500 h-full rounded-full"></div>
        </div>

        {{-- Caution note --}}
        <div class="flex items-center gap-1.5 text-[11px] text-amber-700 bg-amber-50 border border-amber-200/80 px-3 py-1.5 rounded-xl font-medium w-full justify-center">
            <svg class="w-3.5 h-3.5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Harap jangan menutup atau memuat ulang halaman</span>
        </div>
    </div>
</div>

{{-- ============================================
     CUSTOM CONFIRM DIALOG (Replaces browser native confirm)
     ============================================ --}}
<div id="custom-confirm-overlay"
     style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:1rem;"
     class="bg-charcoal/60 backdrop-blur-sm">

    <div id="custom-confirm-box"
         style="transform: scale(0.95); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto p-0 overflow-hidden border border-surface-200">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 pt-6 pb-4">
            <div id="confirm-icon-wrap" class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-amber-100">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-charcoal">Konfirmasi Tindakan</p>
                <p class="text-xs text-charcoal-muted">SIMAK &bull; PT Pelayanan Energi Batam</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 pb-2">
            <p id="custom-confirm-message" class="text-sm text-charcoal leading-relaxed"></p>
        </div>

        {{-- Divider --}}
        <div class="border-t border-surface-100 mx-6 my-4"></div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end gap-3 px-6 pb-6">
            <button id="custom-confirm-cancel"
                    class="px-5 py-2 text-sm font-semibold text-charcoal bg-surface-100 hover:bg-surface-200 rounded-lg transition-colors border border-surface-200">
                Batal
            </button>
            <button id="custom-confirm-ok"
                    class="px-5 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const overlay  = document.getElementById('custom-confirm-overlay');
    const box      = document.getElementById('custom-confirm-box');
    const msgEl    = document.getElementById('custom-confirm-message');
    const btnOk    = document.getElementById('custom-confirm-ok');
    const btnCancel= document.getElementById('custom-confirm-cancel');

    let _resolve = null;

    function showDialog(message) {
        // Detect "destructive" keywords → switch icon to red
        const isDestructive = /hapus|delete|tutup|locked|closed/i.test(message);
        const iconWrap = document.getElementById('confirm-icon-wrap');
        if (isDestructive) {
            iconWrap.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100';
            iconWrap.querySelector('svg').setAttribute('class', 'w-5 h-5 text-red-600');
            btnOk.className = 'px-5 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm';
        } else {
            iconWrap.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-amber-100';
            iconWrap.querySelector('svg').setAttribute('class', 'w-5 h-5 text-amber-600');
            btnOk.className = 'px-5 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm';
        }

        msgEl.textContent = message;
        overlay.style.display = 'flex';
        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                box.style.transform = 'scale(1)';
                box.style.opacity   = '1';
            });
        });
    }

    function hideDialog() {
        box.style.transform = 'scale(0.95)';
        box.style.opacity   = '0';
        setTimeout(() => { overlay.style.display = 'none'; }, 200);
    }

    btnOk.addEventListener('click', () => {
        hideDialog();
        if (_resolve) { _resolve(true); _resolve = null; }
    });

    btnCancel.addEventListener('click', () => {
        hideDialog();
        if (_resolve) { _resolve(false); _resolve = null; }
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            hideDialog();
            if (_resolve) { _resolve(false); _resolve = null; }
        }
    });

    // Override native confirm()
    window.confirm = function (message) {
        return true; // Fallback — real interception done via event listeners below
    };

    // Expose showDialog globally for direct JS usage: window.showConfirmDialog('msg', callback)
    window.showConfirmDialog = function(message, onConfirm) {
        showDialog(message);
        _resolve = (confirmed) => {
            if (confirmed && typeof onConfirm === 'function') onConfirm();
        };
    };

    // Override native alert()
    (function () {
        const alertOverlay = document.getElementById('custom-alert-overlay');
        const alertBox     = document.getElementById('custom-alert-box');
        const alertMsgEl   = document.getElementById('custom-alert-message');
        const alertBtnOk   = document.getElementById('custom-alert-ok');

        function showAlert(message) {
            // Detect error-ish keywords → red icon
            const isError = /error|gagal|tidak valid|minimal|wajib/i.test(message);
            const iconWrap = document.getElementById('alert-icon-wrap');
            if (isError) {
                iconWrap.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100';
                iconWrap.querySelector('svg').setAttribute('class', 'w-5 h-5 text-red-600');
                iconWrap.querySelector('svg path').setAttribute('d', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z');
            } else {
                iconWrap.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-blue-100';
                iconWrap.querySelector('svg').setAttribute('class', 'w-5 h-5 text-blue-600');
                iconWrap.querySelector('svg path').setAttribute('d', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z');
            }
            alertMsgEl.textContent = message;
            alertOverlay.style.display = 'flex';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    alertBox.style.transform = 'scale(1)';
                    alertBox.style.opacity   = '1';
                });
            });
        }

        function hideAlert() {
            alertBox.style.transform = 'scale(0.95)';
            alertBox.style.opacity   = '0';
            setTimeout(() => { alertOverlay.style.display = 'none'; }, 200);
        }

        alertBtnOk.addEventListener('click', hideAlert);
        alertOverlay.addEventListener('click', (e) => { if (e.target === alertOverlay) hideAlert(); });

        window.alert = showAlert;
    })();

    // ============================================
    // UPLOAD LOADING OVERLAY CONTROLLER
    // ============================================
    (function () {
        const uploadOverlay = document.getElementById('custom-upload-overlay');
        const uploadBox     = document.getElementById('custom-upload-box');
        const uploadTitle   = document.getElementById('upload-loading-title');
        const uploadSub     = document.getElementById('upload-loading-subtitle');

        function showUploadLoading(title, subtitle) {
            if (!uploadOverlay || !uploadBox) return;
            if (title && uploadTitle) uploadTitle.textContent = title;
            if (subtitle && uploadSub) uploadSub.textContent = subtitle;

            uploadOverlay.style.display = 'flex';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    uploadOverlay.style.opacity = '1';
                    uploadBox.style.transform = 'scale(1)';
                    uploadBox.style.opacity   = '1';
                });
            });
        }

        function hideUploadLoading() {
            if (!uploadOverlay || !uploadBox) return;
            uploadOverlay.style.opacity = '0';
            uploadBox.style.transform = 'scale(0.95)';
            uploadBox.style.opacity   = '0';
            setTimeout(() => { 
                uploadOverlay.style.display = 'none'; 
            }, 250);
        }

        window.showUploadLoading = showUploadLoading;
        window.hideUploadLoading = hideUploadLoading;

        // Reset if restored via browser BFCache (back/forward)
        window.addEventListener('pageshow', function () {
            hideUploadLoading();
        });

        // Helper: check if form is submitting files
        function isFormUploading(form) {
            if (!form) return false;
            
            // Exclude DELETE or GET
            const methodInput = form.querySelector('input[name="_method"]');
            const method = ((methodInput ? methodInput.value : form.method) || 'POST').toUpperCase();
            if (method === 'DELETE' || method === 'GET') return false;

            const isMultipart = form.getAttribute('enctype') === 'multipart/form-data';
            const fileInputs = form.querySelectorAll('input[type="file"]');
            if (fileInputs.length === 0 && !isMultipart) return false;

            let hasFiles = false;
            fileInputs.forEach(inp => {
                if (inp.files && inp.files.length > 0) hasFiles = true;
            });

            return hasFiles || (isMultipart && fileInputs.length > 0 && typeof form.checkValidity === 'function' && form.checkValidity());
        }

        function disableSubmit(form) {
            const btn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                const svg = btn.querySelector('svg');
                if (svg) svg.classList.add('animate-spin');
            }
        }

        window._isFormUploading = isFormUploading;
        window._disableSubmit = disableSubmit;

        // Auto trigger on standard form submission
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM' || form._confirmHandled) return;

            if (isFormUploading(form)) {
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return; // HTML5 validation failed
                }
                const customTitle = form.getAttribute('data-loading-title');
                const customSub = form.getAttribute('data-loading-subtitle');
                disableSubmit(form);
                showUploadLoading(customTitle, customSub);
            }
        });
    })();

    // Intercept all form submissions that use onsubmit="return confirm(...)"
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form._confirmHandled) return;

        // Check if onsubmit attribute contains confirm(
        const onsubmitAttr = form.getAttribute('onsubmit') || '';
        const match = onsubmitAttr.match(/(?:return\s+)?confirm\(['"](.+?)['"]\)/);
        if (match) {
            e.preventDefault();
            e.stopImmediatePropagation();
            form.removeAttribute('onsubmit');
            showDialog(match[1]);
            _resolve = (confirmed) => {
                if (confirmed) {
                    form._confirmHandled = true;
                    if (window._isFormUploading && window._isFormUploading(form)) {
                        if (window._disableSubmit) window._disableSubmit(form);
                        if (window.showUploadLoading) window.showUploadLoading();
                    }
                    form.submit();
                }
            };
        }
    }, true); // capture phase

    // Intercept all button/element onclick="return confirm(...)"
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[onclick]');
        if (!el) return;
        const onclickAttr = el.getAttribute('onclick') || '';
        const match = onclickAttr.match(/(?:return\s+)?confirm\(['"](.+?)['"]\)/);
        if (match) {
            e.preventDefault();
            e.stopImmediatePropagation();
            el.removeAttribute('onclick');

            showDialog(match[1]);
            _resolve = (confirmed) => {
                if (confirmed) {
                    // Re-trigger: if inside a form, submit it
                    const form = el.closest('form') || el.form;
                    if (form) {
                        form._confirmHandled = true;
                        if (window._isFormUploading && window._isFormUploading(form)) {
                            if (window._disableSubmit) window._disableSubmit(form);
                            if (window.showUploadLoading) window.showUploadLoading();
                        }
                        form.submit();
                    } else {
                        el.click();
                    }
                }
            };
        }
    }, true); // capture phase

})();
</script>
</body>
</html>
