<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Profil — SIMAK</title>
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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex justify-between items-center gap-4">
            <div class="flex items-center gap-2.5 sm:gap-3.5">
                <img src="{{ asset('pln.jpg') }}?v={{ filemtime(public_path('pln.jpg')) }}" alt="PLN Batam" class="h-7 sm:h-9 w-auto object-contain mix-blend-multiply">
                <img src="{{ asset('DI.png') }}" alt="Danantara Indonesia" class="h-4 sm:h-5 w-auto object-contain border-l border-surface-200 pl-2.5 sm:pl-3.5">
                <img src="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}" alt="Logo SIMAK" class="h-6 sm:h-8 w-auto object-contain border-l border-surface-200 pl-2.5 sm:pl-3.5">
            </div>

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
    <main class="flex-1 flex items-center justify-center px-4 py-6 sm:py-10"
          x-data="profileRoleDepartmentForm('{{ old('role', $initialRole) }}', '{{ old('department_id', $user->department_id ?? '') }}')">
        <div class="w-full max-w-lg">

            <div class="bg-white rounded-2xl border border-surface-200 shadow-lg shadow-primary-950/5 p-6 sm:p-8">

                {{-- Simple Header --}}
                <div class="mb-5">
                    <h1 class="text-xl font-bold text-charcoal tracking-tight">Lengkapi Profil Pegawai</h1>
                    <p class="text-xs sm:text-sm text-charcoal-muted mt-1">
                        Isi data di bawah ini untuk mengaktifkan akses SIMAK Anda.
                    </p>
                </div>

                {{-- Compact Verified User Strip --}}
                <div class="mb-5 px-3.5 py-2.5 bg-surface-50 border border-surface-200 rounded-xl flex items-center gap-3">
                    @if (!empty($user->avatar))
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-lg object-cover border border-surface-200 flex-shrink-0" referrerpolicy="no-referrer">
                    @else
                        <div class="w-9 h-9 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-semibold text-charcoal truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-charcoal-muted truncate">{{ $user->email }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full flex-shrink-0">
                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Terverifikasi
                    </span>
                </div>

                {{-- Error Alert (Only when validation fails) --}}
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
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

                    {{-- Row 1: NIP & Jabatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nip" class="block text-xs font-semibold text-charcoal mb-1.5">
                                NIP <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="nip"
                                   id="nip"
                                   required
                                   value="{{ old('nip', $user->nip) }}"
                                   placeholder="Contoh: PEB-0123"
                                   class="form-input @error('nip') !border-red-500 @enderror">
                            @error('nip')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="position" class="block text-xs font-semibold text-charcoal mb-1.5">
                                Jabatan <span class="text-charcoal-muted font-normal">(Opsional)</span>
                            </label>
                            <input type="text"
                                   name="position"
                                   id="position"
                                   value="{{ old('position', $user->position) }}"
                                   placeholder="Contoh: Officer Akuntansi"
                                   class="form-input @error('position') !border-red-500 @enderror">
                            @error('position')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 2: Role --}}
                    <div>
                        <label for="role" class="block text-xs font-semibold text-charcoal mb-1.5">
                            Role <span class="text-red-500">*</span>
                        </label>
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
                        <select name="role"
                                id="role"
                                x-model="selectedRole"
                                required
                                class="form-select @error('role') !border-red-500 @enderror"
                                :class="{ '!border-amber-500 ring-2 ring-amber-500/20': roleHighlight }">
                            <option value="">-- Pilih Role Pengguna --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">
                                    {{ $roleLabels[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p x-show="roleWarning" x-cloak class="mt-1.5 text-xs text-amber-700">
                            Pilih Role terlebih dahulu agar daftar Bidang menyesuaikan posisi Anda.
                        </p>
                    </div>

                    {{-- Row 3: Bidang / Department --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-charcoal">
                                Bidang <span class="text-red-500" x-show="!isStandaloneRole">*</span>
                            </label>
                            <span class="text-[11px] text-primary-700 font-medium" x-show="selectedRole" x-cloak x-text="activeRoleLevelLabel"></span>
                        </div>

                        <input type="hidden" name="department_id" :value="isStandaloneRole ? '' : selectedDept">

                        {{-- State 0: Standalone Role (Direktur / Komisaris) --}}
                        <template x-if="isStandaloneRole">
                            <div class="form-input flex items-center justify-between min-h-[44px] bg-surface-50 border-surface-200 cursor-not-allowed select-none">
                                <span class="text-xs font-medium text-charcoal-muted">Berdiri Sendiri (Tanpa Bidang)</span>
                                <span class="text-[10px] font-semibold uppercase bg-surface-200/80 text-charcoal-muted px-2 py-0.5 rounded">
                                    Berdiri Sendiri
                                </span>
                            </div>
                        </template>

                        {{-- State 1: No Role Selected Yet --}}
                        <template x-if="!selectedRole && !isStandaloneRole">
                            <div @click="openModal()"
                                 class="form-input flex items-center justify-between min-h-[44px] cursor-pointer hover:border-amber-400 bg-surface-50/70 transition-all select-none border-dashed"
                                 :class="{ '!border-red-400 bg-red-50/20': hasDeptError }">
                                <div class="flex items-center gap-2 text-charcoal-muted">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="text-sm text-charcoal-muted">Pilih Role terlebih dahulu</span>
                                </div>
                                <span class="text-[11px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200 flex-shrink-0">
                                    Wajib Role
                                </span>
                            </div>
                        </template>

                        {{-- State 2: Role Selected, Department Not Chosen --}}
                        <template x-if="selectedRole && !isStandaloneRole && !selectedDepartmentObj">
                            <div @click="openModal()"
                                 class="form-input flex items-center justify-between min-h-[44px] cursor-pointer hover:border-primary-400 hover:bg-primary-50/20 transition-all select-none"
                                 :class="{ '!border-red-400 bg-red-50/20': hasDeptError }">
                                <span class="text-sm text-charcoal truncate pr-2" x-text="'Klik untuk memilih ' + modalRoleTargetText"></span>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-[11px] font-semibold text-primary-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-200" x-text="availableCountText"></span>
                                    <svg class="w-4 h-4 text-charcoal-muted/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </template>

                        {{-- State 3: Department Selected --}}
                        <template x-if="selectedRole && !isStandaloneRole && selectedDepartmentObj">
                            <div @click="openModal()"
                                 class="form-input flex items-center justify-between min-h-[44px] cursor-pointer hover:border-primary-400 transition-all select-none bg-primary-50/30 border-primary-200">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <span class="text-[10px] font-mono font-bold bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded border border-primary-200 flex-shrink-0" x-text="selectedDepartmentObj.code"></span>
                                    <div class="min-w-0">
                                        <span class="text-sm font-semibold text-charcoal truncate block" x-text="selectedDepartmentObj.name"></span>
                                        <template x-if="selectedDepartmentObj.hierarchy_path && selectedDepartmentObj.level !== 'manager'">
                                            <p class="text-[11px] text-charcoal-muted truncate" x-text="selectedDepartmentObj.hierarchy_path"></p>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0 text-charcoal-muted">
                                    <span @click.stop="clearSelection()" class="p-1 text-charcoal-muted hover:text-red-600 hover:bg-red-50 rounded-full transition-colors cursor-pointer" title="Kosongkan Pilihan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </span>
                                    <svg class="w-4 h-4 text-charcoal-muted/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </template>

                        {{-- Custom department field when LAIN is selected --}}
                        <template x-if="!isStandaloneRole && String(selectedDept) === String(lainDeptId)">
                            <div class="mt-2.5">
                                <input type="text"
                                       name="custom_department"
                                       placeholder="Masukkan nama bidang / unit kerja baru..."
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

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all cursor-pointer">
                            <span>Simpan &amp; Masuk Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                        <p class="text-[11px] text-charcoal-muted text-center mt-2.5">
                            Perubahan role atau bidang di kemudian hari dapat diperbarui oleh Administrator SPI.
                        </p>
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
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl border border-surface-200 overflow-hidden flex flex-col max-h-[85vh] my-auto"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                {{-- Modal Header --}}
                <div class="px-5 py-3.5 bg-white border-b border-surface-200 flex items-center justify-between flex-shrink-0">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-charcoal" x-text="modalTitle"></h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-primary-50 text-primary-700 border border-primary-200" x-text="activeRoleLevelLabel"></span>
                        </div>
                        <p class="text-xs text-charcoal-muted mt-0.5" x-text="modalSubtitle"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="p-1.5 text-charcoal-muted hover:text-charcoal hover:bg-surface-100 rounded-lg transition-colors cursor-pointer" title="Tutup">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Filter & Search Header --}}
                <div class="p-3.5 bg-surface-50 border-b border-surface-200 space-y-2.5 flex-shrink-0">
                    <div class="flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center justify-between">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-charcoal-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text"
                                   x-ref="modalSearchInput"
                                   x-model="searchQuery"
                                   placeholder="Cari nama atau kode bidang..."
                                   class="w-full pl-9 pr-8 py-2 text-xs text-charcoal bg-white border border-surface-300 rounded-lg focus:outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-500/20">
                            <button type="button" x-show="searchQuery" @click="searchQuery = ''; $refs.modalSearchInput.focus()" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-charcoal-muted hover:text-charcoal cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-charcoal select-none bg-white px-3 py-2 rounded-lg border border-surface-200 hover:bg-surface-50">
                            <input type="checkbox" x-model="showAllDepartments" class="w-3.5 h-3.5 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                            <span>Tampilkan semua bidang</span>
                        </label>
                    </div>

                    <div x-show="showAllDepartments || ['admin', 'direktur', 'komisaris'].includes(selectedRole?.toLowerCase())" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs">
                        <button type="button" @click="setLevelTab('all')"
                                :class="activeLevelTab === 'all' ? 'bg-primary-600 text-white font-semibold' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-2.5 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Semua (<span x-text="departments.length"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('manager')"
                                :class="activeLevelTab === 'manager' ? 'bg-purple-600 text-white font-semibold' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-2.5 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Manager (<span x-text="countByLevel('manager')"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('asman')"
                                :class="activeLevelTab === 'asman' ? 'bg-teal-600 text-white font-semibold' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-2.5 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Asman (<span x-text="countByLevel('asman')"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('staff')"
                                :class="activeLevelTab === 'staff' ? 'bg-sky-600 text-white font-semibold' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-2.5 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            Staff (<span x-text="countByLevel('staff')"></span>)
                        </button>
                        <button type="button" @click="setLevelTab('spi')"
                                :class="activeLevelTab === 'spi' ? 'bg-emerald-600 text-white font-semibold' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                                class="px-2.5 py-1 rounded-full transition-all cursor-pointer whitespace-nowrap">
                            SPI (<span x-text="countByLevel('spi')"></span>)
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-surface-50/50">
                    <div x-show="visibleDepartmentList.length === 0" class="py-10 text-center">
                        <p class="font-semibold text-charcoal text-sm">Tidak ada bidang yang sesuai</p>
                        <p class="text-xs text-charcoal-muted mt-1">Coba kata kunci lain atau aktifkan "Tampilkan semua bidang".</p>
                    </div>

                    <template x-for="group in groupedVisibleDepartments" :key="group.name">
                        <div class="bg-white rounded-xl border border-surface-200 p-3.5">
                            <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-surface-100">
                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-charcoal-medium" x-text="group.name"></h4>
                                <span class="text-[11px] text-charcoal-muted" x-text="group.items.length + ' unit'"></span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <template x-for="dept in group.items" :key="dept.id">
                                    <div @click="selectAndApply(dept)"
                                         class="p-2.5 rounded-lg border cursor-pointer transition-all flex items-center justify-between gap-2.5 select-none"
                                         :class="String(selectedDept) === String(dept.id)
                                            ? 'bg-primary-50/80 border-primary-500 ring-1 ring-primary-500/20'
                                            : 'bg-white border-surface-200 hover:border-primary-300 hover:bg-surface-50'">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                                 :class="String(selectedDept) === String(dept.id) ? 'border-primary-600 bg-primary-600' : 'border-surface-300 bg-white'">
                                                <svg x-show="String(selectedDept) === String(dept.id)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="text-xs font-medium text-charcoal truncate" x-text="dept.name" :title="dept.name"></span>
                                        </div>
                                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 bg-surface-100 border border-surface-200 rounded text-charcoal-muted flex-shrink-0" x-text="dept.code"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3 bg-white border-t border-surface-200 flex items-center justify-between gap-3 flex-shrink-0">
                    <span class="text-xs text-charcoal-muted">Klik bidang untuk langsung memilih</span>
                    <button type="button" @click="closeModal()" class="btn-secondary text-xs px-3.5 py-1.5 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="w-full bg-white border-t border-surface-200 py-3 px-4 text-center text-xs text-charcoal-muted">
        &copy; {{ date('Y') }} PT Pelayanan Energi Batam &bull; SIMAK
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
                    return this.selectedRole?.toLowerCase() === 'direktur' ? 'Direksi' : 'Dewan Komisaris';
                }
                const map = {
                    'manager': 'Tingkat Manager',
                    'asman': 'Tingkat Assistant Manager',
                    'staff': 'Tingkat Staff Unit',
                    'kspi': 'Tingkat KSPI',
                    'auditor': 'Tingkat Auditor SPI',
                    'admin': 'Administrator',
                };
                return map[this.selectedRole?.toLowerCase()] || '';
            },

            get modalRoleTargetText() {
                const map = {
                    'manager': 'Bidang (Manager)',
                    'asman': 'Sub-Bidang (Asman)',
                    'staff': 'Unit Kerja (Staff)',
                    'kspi': 'Unit SPI',
                    'auditor': 'Unit Auditor SPI',
                };
                return map[this.selectedRole?.toLowerCase()] || 'Bidang';
            },

            get availableCountText() {
                const count = this.filteredDepartments.filter(d => String(d.id) !== String(this.lainDeptId)).length;
                if (this.selectedRole === 'asman') return `${count} Sub-bidang`;
                if (this.selectedRole === 'staff') return `${count} Unit Kerja`;
                if (this.selectedRole === 'manager') return `${count} Bidang`;
                return `${count} Tersedia`;
            },

            get modalTitle() {
                if (this.showAllDepartments) return 'Semua Bidang Organisasi';
                const map = {
                    'manager': 'Pilih Bidang (Manager)',
                    'asman': 'Pilih Sub-Bidang (Asman)',
                    'staff': 'Pilih Unit Kerja (Staff)',
                    'kspi': 'Pilih Unit SPI',
                    'auditor': 'Pilih Unit Auditor SPI',
                };
                return map[this.selectedRole?.toLowerCase()] || 'Pilih Bidang';
            },

            get modalSubtitle() {
                if (this.showAllDepartments) {
                    return `Menampilkan seluruh ${this.departments.length} bidang struktur organisasi`;
                }
                const counts = this.visibleDepartmentList.filter(d => String(d.id) !== String(this.lainDeptId)).length;
                return `${counts} bidang sesuai ${this.activeRoleLevelLabel}`;
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
                    this.deptClientError = 'Silakan pilih Bidang sesuai Role Anda.';
                    this.openModal();
                    return;
                }
            }
        };
    };
    </script>

</body>
</html>
