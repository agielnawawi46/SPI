<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Profil Pegawai — SIMAK</title>
    <link rel="icon" type="image/png" href="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}">
    <link rel="shortcut icon" href="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-surface-50 text-charcoal antialiased font-sans flex flex-col justify-between selection:bg-primary-500 selection:text-white">

    {{-- Top Navbar --}}
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-surface-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center gap-4">
            {{-- Brand Logos --}}
            <div class="flex items-center gap-2.5 sm:gap-4">
                <img src="{{ asset('pln.jpg') }}?v={{ filemtime(public_path('pln.jpg')) }}" alt="PLN Batam" class="h-8 sm:h-10 w-auto object-contain mix-blend-multiply">
                <img src="{{ asset('DI.png') }}" alt="Danantara Indonesia" class="h-5 sm:h-6 w-auto object-contain border-l border-surface-200 pl-2.5 sm:pl-3.5">
                <img src="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}" alt="Logo SIMAK" class="h-7 sm:h-9 w-auto object-contain border-l border-surface-200 pl-2.5 sm:pl-3.5">
            </div>

            {{-- Logout / Switch Account --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-charcoal-medium hover:text-red-600 bg-surface-100 hover:bg-red-50 border border-surface-200 hover:border-red-200 rounded-xl transition-all cursor-pointer"
                        title="Keluar dari akun Google ini">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Ganti Akun</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- Main Onboarding Form --}}
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12"
          x-data="profileRoleDepartmentForm('{{ old('role', $initialRole) }}', '{{ old('department_id', $user->department_id ?? '') }}')">
        <div class="w-full max-w-xl">

            <div class="bg-white rounded-3xl border border-surface-200 shadow-xl shadow-primary-950/5 p-6 sm:p-9">

                {{-- Header Badge & Title --}}
                <div class="text-center mb-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700 border border-primary-200 mb-3">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                        Langkah Terakhir &bull; Lengkapi Profil
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-charcoal tracking-tight">Selamat Datang di SIMAK</h1>
                    <p class="text-xs sm:text-sm text-charcoal-medium mt-1.5 max-w-md mx-auto">
                        Akun Google Workspace Anda telah terverifikasi. Mohon lengkapi NIP, Role, dan Bidang kerja Anda untuk mengaktifkan akses sistem.
                    </p>
                </div>

                {{-- Verified User Card --}}
                <div class="mb-6 p-4 bg-surface-50 border border-surface-200 rounded-2xl flex items-center gap-3.5">
                    @if (!empty($user->avatar))
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-xl object-cover border border-surface-200 flex-shrink-0" referrerpolicy="no-referrer">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg flex-shrink-0">
                            {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-charcoal truncate">{{ $user->name }}</h2>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full flex-shrink-0">
                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Terverifikasi
                            </span>
                        </div>
                        <p class="text-xs text-charcoal-muted truncate mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>

                {{-- Session / Error Alerts --}}
                @if (session('info'))
                    <div class="mb-5 p-3.5 bg-sky-50 border border-sky-200 rounded-xl text-xs sm:text-sm text-sky-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 flex-shrink-0 text-sky-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs sm:text-sm text-red-700">
                        <div class="font-bold mb-1">Terdapat kesalahan pengisian data:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('profile.complete.store') }}" method="POST" @submit="handleSubmit($event)" class="space-y-4">
                    @csrf

                    {{-- 1. NIP --}}
                    <div>
                        <label for="nip" class="block text-xs font-bold text-charcoal mb-1.5">
                            Nomor Induk Pegawai (NIP) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            </div>
                            <input type="text"
                                   name="nip"
                                   id="nip"
                                   required
                                   value="{{ old('nip', $user->nip) }}"
                                   placeholder="Contoh: 198901012015031001 atau PEB-0123"
                                   class="form-input !pl-10 @error('nip') !border-red-500 @enderror">
                        </div>
                        @error('nip')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-charcoal-muted mt-1">Masukkan NIP resmi Anda yang terdaftar di PT Pelayanan Energi Batam.</p>
                    </div>

                    {{-- 2. Role (Pilih terlebih dahulu sebelum Bidang) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="role" class="block text-xs font-bold text-charcoal">
                                Role / Peran Pengguna <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] font-semibold text-primary-700 bg-primary-50 border border-primary-200 px-2 py-0.5 rounded">
                                Langkah 1
                            </span>
                        </div>
                        @php
                            $roleLabels = [
                                'manager'   => 'Manager (Kepala Bidang / Divisi)',
                                'asman'     => 'Assistant Manager (Kepala Sub-Bidang)',
                                'staff'     => 'Staff (Auditee Unit Kerja)',
                                'kspi'      => 'KSPI (Kepala Satuan Pengawasan Intern)',
                                'auditor'   => 'Auditor (Staf Auditor SPI)',
                                'direktur'  => 'Direktur (Direksi)',
                                'komisaris' => 'Komisaris (Dewan Komisaris)',
                                'admin'     => 'Admin (Administrator Sistem)',
                            ];
                        @endphp
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <select name="role"
                                    id="role"
                                    x-model="selectedRole"
                                    required
                                    class="form-select !pl-10 @error('role') !border-red-500 @enderror"
                                    :class="{ '!border-amber-500 ring-2 ring-amber-500/25': roleHighlight }">
                                <option value="">-- Pilih Role Pengguna --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">
                                        {{ $roleLabels[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('role')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p x-show="roleWarning" x-cloak class="mt-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Silakan pilih Role terlebih dahulu agar pilihan Bidang menyesuaikan posisi Anda.</span>
                        </p>
                        <p x-show="!roleWarning" class="text-[11px] text-charcoal-muted mt-1">
                            Pilih peran Anda terlebih dahulu agar daftar Bidang di bawah otomatis menyesuaikan tingkatan Role.
                        </p>
                    </div>

                    {{-- 3. Bidang / Departemen Pop-Up Selector (Sama seperti Manajemen User) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-charcoal">
                                Bidang (Department) <span class="text-red-500" x-show="!isStandaloneRole">*</span>
                            </label>
                            <span class="text-[11px] text-primary-700 font-semibold" x-show="selectedRole" x-cloak x-text="activeRoleLevelLabel"></span>
                            <span class="text-[11px] font-semibold text-charcoal-muted bg-surface-100 border border-surface-200 px-2 py-0.5 rounded" x-show="!selectedRole">
                                Langkah 2
                            </span>
                        </div>

                        {{-- Hidden input for standard form submission --}}
                        <input type="hidden" name="department_id" :value="isStandaloneRole ? '' : selectedDept">

                        {{-- State 0: Standalone Role (Direktur / Komisaris) --}}
                        <template x-if="isStandaloneRole">
                            <div class="form-input flex items-center justify-between min-h-[46px] bg-surface-100/70 border-surface-200 cursor-not-allowed select-none text-charcoal-muted">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-surface-200 flex items-center justify-center text-charcoal-muted flex-shrink-0">
                                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-charcoal">Berdiri Sendiri (Tanpa Bidang)</p>
                                        <p class="text-[11px] text-charcoal-muted">Role ini menaungi seluruh organisasi dan tidak terikat bidang.</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold uppercase bg-surface-200 text-charcoal-muted px-2 py-0.5 rounded border border-surface-300 flex-shrink-0">
                                    Berdiri Sendiri
                                </span>
                            </div>
                        </template>

                        {{-- State 1: No Role Selected Yet --}}
                        <template x-if="!selectedRole && !isStandaloneRole">
                            <div @click="openModal()"
                                 class="form-input flex items-center justify-between min-h-[46px] cursor-pointer hover:border-amber-400 bg-surface-50/70 transition-all select-none border-dashed"
                                 :class="{ '!border-red-400 bg-red-50/20': hasDeptError }">
                                <div class="flex items-center gap-2.5 text-charcoal-muted">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="text-sm font-medium text-charcoal-muted">Pilih Role terlebih dahulu</span>
                                </div>
                                <span class="text-[11px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200 flex-shrink-0">
                                    Wajib Role
                                </span>
                            </div>
                        </template>

                        {{-- State 2: Role is Selected, But No Department Chosen Yet --}}
                        <template x-if="selectedRole && !isStandaloneRole && !selectedDepartmentObj">
                            <div @click="openModal()"
                                 class="form-input flex items-center justify-between min-h-[46px] cursor-pointer hover:border-primary-400 hover:bg-primary-50/20 transition-all select-none"
                                 :class="{ '!border-red-400 bg-red-50/20': hasDeptError }">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="text-sm font-medium text-charcoal truncate" x-text="'Klik untuk memilih ' + modalRoleTargetText"></span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-[11px] font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-200" x-text="availableCountText"></span>
                                    <svg class="w-4 h-4 text-charcoal-muted/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </template>

                        {{-- State 3: Department is Selected --}}
                        <template x-if="selectedRole && !isStandaloneRole && selectedDepartmentObj">
                            <div @click="openModal()"
                                 class="form-input flex items-center justify-between min-h-[46px] cursor-pointer hover:border-primary-400 hover:bg-primary-50/20 transition-all select-none bg-primary-50/30 border-primary-200">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <span class="text-[10px] font-mono font-bold bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded border border-primary-200 flex-shrink-0" x-text="selectedDepartmentObj.code"></span>
                                    <div class="min-w-0">
                                        <span class="text-sm font-semibold text-charcoal truncate block" x-text="selectedDepartmentObj.name"></span>
                                        <template x-if="selectedDepartmentObj.hierarchy_path && selectedDepartmentObj.level !== 'manager'">
                                            <p class="text-[11px] text-charcoal-muted truncate mt-0.5" x-text="selectedDepartmentObj.hierarchy_path"></p>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0 text-charcoal-muted">
                                    <span @click.stop="clearSelection()" class="p-1 text-charcoal-muted hover:text-red-600 hover:bg-red-50 rounded-full transition-colors cursor-pointer" title="Kosongkan Pilihan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </span>
                                    <svg class="w-4 h-4 text-charcoal-muted/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </template>

                        {{-- Dynamic Hint --}}
                        <div class="mt-1.5 flex items-center gap-1.5 text-xs">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-primary-50 text-primary-700 border border-primary-200" x-show="selectedRole" x-cloak>
                                <svg class="w-3 h-3 mr-1 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="filterHint"></span>
                            </span>
                            <span class="text-[11px] text-charcoal-muted" x-show="!selectedRole">Wajib diisi untuk user dengan role Auditee (Manager, Asman, Staff) maupun tim SPI.</span>
                        </div>

                        {{-- Custom department field when LAIN is selected --}}
                        <template x-if="!isStandaloneRole && String(selectedDept) === String(lainDeptId)">
                            <div class="mt-3 p-3 bg-surface-50 rounded-xl border border-surface-200">
                                <label class="block text-xs font-bold text-charcoal mb-1.5">Nama Bidang Baru / Kustom <span class="text-red-500">*</span></label>
                                <input type="text"
                                       name="custom_department"
                                       placeholder="Masukkan nama bidang atau unit kerja baru..."
                                       class="form-input @error('custom_department') !border-red-500 @enderror"
                                       value="{{ old('custom_department') }}"
                                       required>
                            </div>
                        </template>

                        <p x-show="deptClientError" x-cloak class="form-error" x-text="deptClientError"></p>
                        @error('department_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        @error('custom_department')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Jabatan (Posisi) --}}
                    <div>
                        <label for="position" class="block text-xs font-bold text-charcoal mb-1.5">
                            Jabatan <span class="text-charcoal-muted font-normal">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="text"
                                   name="position"
                                   id="position"
                                   value="{{ old('position', $user->position) }}"
                                   placeholder="Contoh: Officer Akuntansi, Staf Operasi, Supervisor K3L"
                                   class="form-input !pl-10 @error('position') !border-red-500 @enderror">
                        </div>
                        @error('position')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role & Department Guide Banner --}}
                    <div class="p-4 bg-primary-50/60 border border-primary-200/80 rounded-2xl flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="text-xs text-charcoal leading-relaxed">
                            <strong class="font-bold text-primary-800">Panduan Pemilihan Role &amp; Bidang</strong>
                            <p class="text-charcoal-medium mt-0.5">
                                Pilih <strong>Role</strong> sesuai jabatan Anda terlebih dahulu, kemudian klik kolom <strong>Bidang</strong> untuk memilih unit kerja yang sesuai. Jika terjadi kesalahan atau perubahan mutasi jabatan di kemudian hari, Administrator SPI dapat memperbaruinya melalui Manajemen User.
                            </p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm hover:shadow hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer">
                            <span>Simpan Profil &amp; Masuk ke Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>

                </form>

            </div>

        </div>

        {{-- ========================================================
             MODAL POP-UP: STRUKTUR ORGANISASI SESUAI ROLE TERPILIH
             ======================================================== --}}
        <div x-show="modalOpen"
             x-cloak
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-[9999] overflow-y-auto bg-charcoal/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.outside="closeModal()"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl border border-surface-200 overflow-hidden flex flex-col max-h-[90vh] my-auto"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-white border-b border-surface-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-charcoal" x-text="modalTitle"></h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-primary-50 text-primary-700 border border-primary-200" x-text="activeRoleLevelLabel"></span>
                            </div>
                            <p class="text-xs text-charcoal-muted mt-0.5" x-text="modalSubtitle"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()" class="p-2 text-charcoal-muted hover:text-charcoal hover:bg-surface-100 rounded-lg transition-colors cursor-pointer" title="Tutup">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Filter & Search Header --}}
                <div class="p-4 bg-surface-50 border-b border-surface-200 space-y-3 flex-shrink-0">
                    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
                        {{-- Search Input --}}
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text"
                                   x-ref="modalSearchInput"
                                   x-model="searchQuery"
                                   :placeholder="'Cari bidang ' + activeRoleLevelLabel + ' (contoh: billing, risiko, OPS, KEU)...'"
                                   class="w-full pl-10 pr-9 py-2 text-xs text-charcoal bg-white border border-surface-300 rounded-lg focus:outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20">
                            <button type="button" x-show="searchQuery" @click="searchQuery = ''; $refs.modalSearchInput.focus()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-charcoal-muted hover:text-charcoal cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Mode Toggle (Sesuaikan dengan role) --}}
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-charcoal select-none bg-white px-3 py-2 rounded-lg border border-surface-200 shadow-sm hover:bg-surface-50">
                            <input type="checkbox" x-model="showAllDepartments" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                            <span class="font-medium">Tampilkan seluruh bidang struktur organisasi</span>
                        </label>
                    </div>

                    {{-- Tabs (Hanya muncul jika mode Tampilkan Seluruh Bidang aktif atau role admin) --}}
                    <div x-show="showAllDepartments || ['admin', 'direktur', 'komisaris'].includes(selectedRole?.toLowerCase())" class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                        <span class="text-charcoal-muted text-[11px] font-semibold uppercase tracking-wider mr-1">Tingkat:</span>
                        <button type="button" @click="setLevelTab('all')"
                                :class="activeLevelTab === 'all' ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-3 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Semua (<span x-text="departments.length"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('manager')"
                                :class="activeLevelTab === 'manager' ? 'bg-purple-600 text-white font-semibold shadow-sm' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-3 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Manager (<span x-text="countByLevel('manager')"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('asman')"
                                :class="activeLevelTab === 'asman' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-3 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Assistant Manager (<span x-text="countByLevel('asman')"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('staff')"
                                :class="activeLevelTab === 'staff' ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-3 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Staff Unit (<span x-text="countByLevel('staff')"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('spi')"
                                :class="activeLevelTab === 'spi' ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-3 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            SPI (<span x-text="countByLevel('spi')"></span>)
                        </button>
                    </div>
                </div>

                {{-- Modal Body: Spacious Tree & Cards --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-surface-50/50">
                    {{-- Empty Search State --}}
                    <div x-show="visibleDepartmentList.length === 0" class="py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-surface-100 flex items-center justify-center text-charcoal-muted mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="font-bold text-charcoal text-sm">Tidak ada bidang yang sesuai</p>
                        <p class="text-xs text-charcoal-muted mt-1 max-w-sm mx-auto">Coba gunakan kata kunci pencarian lain atau aktifkan centang "Tampilkan seluruh bidang" di atas.</p>
                    </div>

                    {{-- Grouped Departments --}}
                    <template x-for="group in groupedVisibleDepartments" :key="group.name">
                        <div class="bg-white rounded-xl border border-surface-200 p-4 shadow-sm">
                            {{-- Group Header --}}
                            <div class="flex items-center gap-2 pb-2.5 mb-3 border-b border-surface-100">
                                <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-charcoal" x-text="group.name"></h4>
                                <span class="text-[11px] text-charcoal-muted font-normal ml-auto" x-text="group.items.length + ' unit'"></span>
                            </div>

                            {{-- Department Tiles Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                <template x-for="dept in group.items" :key="dept.id">
                                    <div @click="selectAndApply(dept)"
                                         class="p-3 rounded-xl border cursor-pointer transition-all flex items-center justify-between gap-3 select-none"
                                         :class="String(selectedDept) === String(dept.id)
                                            ? 'bg-primary-50/80 border-primary-500 ring-2 ring-primary-500/20 shadow-sm'
                                            : 'bg-white border-surface-200 hover:border-primary-300 hover:bg-surface-50'">

                                        <div class="flex items-center gap-3 min-w-0">
                                            {{-- Radio selection indicator --}}
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                                                 :class="String(selectedDept) === String(dept.id) ? 'border-primary-600 bg-primary-600 text-white' : 'border-surface-300 bg-white'">
                                                <svg x-show="String(selectedDept) === String(dept.id)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>

                                            {{-- Nama Bidang / Unit --}}
                                            <span class="text-xs font-semibold text-charcoal leading-snug truncate" x-text="dept.name" :title="dept.name"></span>
                                        </div>

                                        {{-- Kode Bidang --}}
                                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 bg-surface-100 border border-surface-200 rounded text-charcoal-muted flex-shrink-0" x-text="dept.code"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3.5 bg-white border-t border-surface-200 flex items-center justify-between gap-3 flex-shrink-0">
                    <div class="text-xs text-charcoal-muted flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                        <span>Klik bidang untuk langsung memilih</span>
                    </div>

                    <button type="button" @click="closeModal()" class="btn-secondary text-xs px-4 py-1.5 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="w-full bg-white border-t border-surface-200 py-3.5 px-4 text-center text-xs text-charcoal-muted">
        &copy; {{ date('Y') }} PT Pelayanan Energi Batam &bull; SIMAK (Sistem Informasi Manajemen Audit &amp; Kepatuhan)
    </footer>

    <script>
    window.profileRoleDepartmentForm = function(initialRole = '', initialDept = '') {
        return {
            selectedRole: initialRole,
            selectedDept: initialDept,
            showAllDepartments: false,
            modalOpen: false,
            searchQuery: '',
            activeLevelTab: 'all',
            roleWarning: false,
            roleHighlight: false,
            deptClientError: '',
            hasServerDeptError: {{ $errors->has('department_id') ? 'true' : 'false' }},
            departments: @json($departments),
            lainDeptId: '{{ $lainDeptId }}',

            get hasDeptError() {
                return this.hasServerDeptError || !!this.deptClientError;
            },

            get isStandaloneRole() {
                return ['direktur', 'komisaris'].includes(this.selectedRole?.toLowerCase());
            },

            get activeRoleLevelLabel() {
                if (this.isStandaloneRole) {
                    return this.selectedRole?.toLowerCase() === 'direktur' ? 'Direksi (Berdiri Sendiri)' : 'Dewan Komisaris (Berdiri Sendiri)';
                }
                const map = {
                    'manager': 'Tingkat Manager',
                    'asman': 'Tingkat Assistant Manager',
                    'staff': 'Tingkat Staff Unit',
                    'kspi': 'Tingkat KSPI',
                    'auditor': 'Tingkat Auditor SPI',
                    'direktur': 'Direksi',
                    'komisaris': 'Komisaris',
                    'admin': 'Administrator',
                };
                return map[this.selectedRole?.toLowerCase()] || 'Pilih Role';
            },

            get modalRoleTargetText() {
                const map = {
                    'manager': 'Bidang (Tingkat Manager)',
                    'asman': 'Sub-Bidang Assistant Manager',
                    'staff': 'Unit Kerja Staff',
                    'kspi': 'Unit Satuan Pengawasan Intern',
                    'auditor': 'Unit Auditor SPI',
                };
                return map[this.selectedRole?.toLowerCase()] || 'Bidang (Department)';
            },

            get availableCountText() {
                const count = this.filteredDepartments.filter(d => String(d.id) !== String(this.lainDeptId)).length;
                if (this.selectedRole === 'asman') return `${count} Sub-bidang`;
                if (this.selectedRole === 'staff') return `${count} Unit Kerja`;
                if (this.selectedRole === 'manager') return `${count} Bidang`;
                return `${count} Tersedia`;
            },

            get modalTitle() {
                if (this.showAllDepartments) return 'Semua Bidang / Struktur Organisasi';
                const map = {
                    'manager': 'Pilih Bidang (Tingkat Manager)',
                    'asman': 'Pilih Sub-Bidang (Tingkat Assistant Manager)',
                    'staff': 'Pilih Unit Kerja (Tingkat Staff)',
                    'kspi': 'Pilih Unit Satuan Pengawasan Intern',
                    'auditor': 'Pilih Unit Auditor SPI',
                };
                return map[this.selectedRole?.toLowerCase()] || 'Pilih Bidang / Unit Organisasi';
            },

            get modalSubtitle() {
                if (this.showAllDepartments) {
                    return `Menampilkan seluruh ${this.departments.length} bidang struktur organisasi PT Pelayanan Energi Batam`;
                }
                const counts = this.visibleDepartmentList.filter(d => String(d.id) !== String(this.lainDeptId)).length;
                return `Menampilkan ${counts} bidang yang sesuai untuk ${this.activeRoleLevelLabel}`;
            },

            get filterHint() {
                if (this.isStandaloneRole) {
                    return 'Role ini berdiri sendiri dan menaungi seluruh perusahaan (tanpa bidang).';
                }
                if (!this.selectedRole) {
                    return 'Pilih role terlebih dahulu untuk menyesuaikan pilihan bidang.';
                }
                const counts = this.filteredDepartments.filter(d => String(d.id) !== String(this.lainDeptId)).length;
                if (this.selectedRole === 'manager') return `Menampilkan ${counts} bidang tingkat Manager`;
                if (this.selectedRole === 'asman') return `Menampilkan ${counts} sub-bidang tingkat Assistant Manager`;
                if (this.selectedRole === 'staff') return `Menampilkan ${counts} unit kerja tingkat Staff`;
                if (this.selectedRole === 'kspi') return 'Menampilkan unit Kepala Satuan Pengawasan Intern';
                if (this.selectedRole === 'auditor') return 'Menampilkan unit Auditor Satuan Pengawasan Intern';
                return `Menampilkan bidang sesuai role ${this.selectedRole} (${counts} bidang)`;
            },

            get filteredDepartments() {
                if (!this.selectedRole) {
                    return [];
                }

                const role = this.selectedRole.toLowerCase();

                if (role === 'manager') {
                    return this.departments.filter(d => d.level === 'manager' || String(d.id) === String(this.lainDeptId));
                }
                if (role === 'asman') {
                    return this.departments.filter(d => d.level === 'asman' || String(d.id) === String(this.lainDeptId));
                }
                if (role === 'staff') {
                    return this.departments.filter(d => d.level === 'staff' || String(d.id) === String(this.lainDeptId));
                }
                if (role === 'kspi') {
                    return this.departments.filter(d => d.level === 'kspi' || String(d.id) === String(this.lainDeptId));
                }
                if (role === 'auditor') {
                    return this.departments.filter(d => d.level === 'auditor' || d.level === 'kspi' || String(d.id) === String(this.lainDeptId));
                }

                return this.departments;
            },

            get visibleDepartmentList() {
                let list = this.departments;

                if (!this.showAllDepartments) {
                    list = this.filteredDepartments;
                } else if (this.activeLevelTab !== 'all') {
                    if (this.activeLevelTab === 'spi') {
                        list = list.filter(d => d.level === 'kspi' || d.level === 'auditor' || String(d.id) === String(this.lainDeptId));
                    } else {
                        list = list.filter(d => d.level === this.activeLevelTab || String(d.id) === String(this.lainDeptId));
                    }
                }

                if (this.searchQuery && this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase().trim();
                    list = list.filter(d =>
                        (d.name && d.name.toLowerCase().includes(q)) ||
                        (d.code && d.code.toLowerCase().includes(q)) ||
                        (d.hierarchy_path && d.hierarchy_path.toLowerCase().includes(q)) ||
                        (d.group_label && d.group_label.toLowerCase().includes(q))
                    );
                }

                return list;
            },

            get groupedVisibleDepartments() {
                const groups = {};
                this.visibleDepartmentList.forEach(dept => {
                    const groupName = dept.group_label || 'Lainnya';
                    if (!groups[groupName]) {
                        groups[groupName] = [];
                    }
                    groups[groupName].push(dept);
                });

                return Object.keys(groups).map(name => ({
                    name: name,
                    items: groups[name]
                }));
            },

            get selectedDepartmentObj() {
                if (!this.selectedDept) return null;
                return this.departments.find(d => String(d.id) === String(this.selectedDept)) || null;
            },

            countByLevel(level) {
                if (level === 'spi') {
                    return this.departments.filter(d => d.level === 'kspi' || d.level === 'auditor').length;
                }
                return this.departments.filter(d => d.level === level).length;
            },

            init() {
                this.$watch('selectedRole', () => {
                    this.onRoleChange();
                });
            },

            onRoleChange() {
                this.roleWarning = false;
                this.roleHighlight = false;
                this.deptClientError = '';

                if (this.isStandaloneRole) {
                    this.selectedDept = '';
                    return;
                }

                const isValid = this.filteredDepartments.some(d => String(d.id) === String(this.selectedDept));
                if (!isValid && this.selectedDept && String(this.selectedDept) !== String(this.lainDeptId)) {
                    this.selectedDept = '';
                }
            },

            promptRoleFirst() {
                this.roleWarning = true;
                this.roleHighlight = true;
                const roleEl = document.getElementById('role');
                if (roleEl) {
                    roleEl.focus();
                    roleEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                setTimeout(() => {
                    this.roleHighlight = false;
                }, 2500);
            },

            openModal() {
                if (this.isStandaloneRole) {
                    return;
                }
                if (!this.selectedRole) {
                    this.promptRoleFirst();
                    return;
                }

                this.deptClientError = '';
                this.searchQuery = '';
                this.showAllDepartments = false;
                this.activeLevelTab = 'all';
                this.modalOpen = true;
                this.$nextTick(() => {
                    if (this.$refs.modalSearchInput) {
                        this.$refs.modalSearchInput.focus();
                    }
                });
            },

            closeModal() {
                this.modalOpen = false;
            },

            selectAndApply(dept) {
                this.selectedDept = String(dept.id);
                this.deptClientError = '';
                this.hasServerDeptError = false;
                this.modalOpen = false;
                if (String(dept.id) === String(this.lainDeptId)) {
                    this.$nextTick(() => {
                        const el = document.querySelector('input[name="custom_department"]');
                        if (el) {
                            el.focus();
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            },

            clearSelection() {
                this.selectedDept = '';
            },

            setLevelTab(tab) {
                this.activeLevelTab = tab;
            },

            handleSubmit(event) {
                if (!this.selectedRole) {
                    event.preventDefault();
                    this.promptRoleFirst();
                    return;
                }

                if (!this.isStandaloneRole && !this.selectedDept) {
                    event.preventDefault();
                    this.deptClientError = 'Silakan pilih Bidang / Departemen sesuai Role Anda.';
                    this.openModal();
                    return;
                }
            }
        };
    };
    </script>

</body>
</html>
