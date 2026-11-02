@extends('layouts.app')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('admin.users.index') }}" class="breadcrumb-link">Manajemen User</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Edit User</span>
@endsection

@section('content')
<div class="card" x-data="userRoleDepartmentForm('{{ old('role', $user->roles->first()?->name) }}', '{{ old('department_id', $user->department_id) }}')">
    <div class="card-header border-b border-surface-200">
        <h2 class="card-title">Form Edit User: {{ $user->name }}</h2>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama --}}
                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-input @error('email') border-red-400 @enderror" required>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                
                {{-- NIP --}}
                <div class="form-group md:col-span-2">
                    <label for="nip" class="form-label">NIP</label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip', $user->nip) }}" placeholder="Nomor Induk Pegawai (Opsional)" class="form-input @error('nip') border-red-400 @enderror">
                    @error('nip') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Role --}}
                <div class="form-group">
                    <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
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
                    <select name="role" id="role" x-model="selectedRole" class="form-input @error('role') border-red-400 @enderror" required>
                        <option value="">-- Pilih Role Pengguna --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                {{ $roleLabels[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Bidang / Department Pop-Up Selector --}}
                <div class="form-group">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="form-label mb-0">Bidang (Department)</label>
                        <span class="text-xs text-primary-700 font-semibold" x-show="selectedRole" x-text="activeRoleLevelLabel"></span>
                    </div>

                    {{-- Hidden input for standard form submission --}}
                    <input type="hidden" name="department_id" :value="isStandaloneRole ? '' : selectedDept">

                    {{-- State 0: Standalone Role (Direktur / Komisaris) --}}
                    <template x-if="isStandaloneRole">
                        <div class="form-input flex items-center justify-between min-h-[46px] bg-surface-100/70 border-surface-200 cursor-not-allowed select-none text-charcoal-muted">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-surface-200 flex items-center justify-center text-charcoal-muted">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-charcoal">Berdiri Sendiri (Tanpa Bidang)</p>
                                    <p class="text-[11px] text-charcoal-muted">Role ini menaungi seluruh organisasi dan tidak terikat bidang.</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold uppercase bg-surface-200 text-charcoal-muted px-2 py-0.5 rounded border border-surface-300">
                                Berdiri Sendiri
                            </span>
                        </div>
                    </template>

                    {{-- State 1: No Role Selected Yet --}}
                    <template x-if="!selectedRole && !isStandaloneRole">
                        <div @click="openModal()"
                             class="form-input flex items-center justify-between min-h-[46px] cursor-pointer hover:border-amber-400 bg-surface-50/70 transition-all select-none border-dashed"
                             :class="{ 'border-red-400 bg-red-50/20': '{{ $errors->has('department_id') }}' }">
                            <div class="flex items-center gap-2.5 text-charcoal-muted">
                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="text-sm font-medium text-charcoal-muted">Pilih Role terlebih dahulu</span>
                            </div>
                            <span class="text-[11px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                Wajib Role
                            </span>
                        </div>
                    </template>

                    {{-- State 2: Role is Selected, But No Department Chosen Yet --}}
                    <template x-if="selectedRole && !isStandaloneRole && !selectedDepartmentObj">
                        <div @click="openModal()"
                             class="form-input flex items-center justify-between min-h-[46px] cursor-pointer hover:border-primary-400 hover:bg-primary-50/20 transition-all select-none"
                             :class="{ 'border-red-400 bg-red-50/20': '{{ $errors->has('department_id') }}' }">
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-primary-50 text-primary-700 border border-primary-200" x-show="selectedRole">
                            <svg class="w-3 h-3 mr-1 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="filterHint"></span>
                        </span>
                        <span class="text-charcoal-muted" x-show="!selectedRole">Wajib diisi untuk user dengan role Auditee (Manager, Asman, Staff).</span>
                    </div>

                    {{-- Custom department field when LAIN is selected --}}
                    <template x-if="String(selectedDept) === String(lainDeptId)">
                        <div class="mt-3 p-3 bg-surface-50 rounded-xl border border-surface-200">
                            <label class="form-label text-xs">Nama Bidang Baru / Kustom <span class="text-red-500">*</span></label>
                            <input type="text" name="custom_department" placeholder="Masukkan nama bidang atau unit kerja baru..." class="form-input @error('custom_department') border-red-400 @enderror" value="{{ old('custom_department') }}" required>
                        </div>
                    </template>

                    @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                    @error('custom_department') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Password (Opsional) --}}
                <div class="form-group">
                    <label for="password" class="form-label">Password Baru <span class="text-xs font-normal text-charcoal-muted">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah" class="form-input @error('password') border-red-400 @enderror">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi password baru" class="form-input">
                </div>

                {{-- Status Aktif --}}
                <div class="form-group md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-charcoal">Akun Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-surface-200">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Update User</button>
            </div>
        </form>
    </div>

    {{-- ========================================================
         MODAL POP-UP: STRUKTUR ORGANISASI & PILIH BIDANG
         ======================================================== --}}
    <div x-show="modalOpen"
         @keydown.escape.window="closeModal()"
         style="display: none;"
         class="fixed inset-0 z-[9999] overflow-y-auto bg-charcoal/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6"
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
                    <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center flex-shrink-0 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
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
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-charcoal select-none bg-white px-3 py-2 rounded-lg border border-surface-200 shadow-2xs hover:bg-surface-50">
                        <input type="checkbox" x-model="showAllDepartments" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                        <span class="font-medium">Tampilkan seluruh bidang struktur organisasi</span>
                    </label>
                </div>

                {{-- Tabs (Hanya muncul jika mode Tampilkan Seluruh Bidang aktif atau role eksekutif) --}}
                <div x-show="showAllDepartments || ['admin', 'direktur', 'komisaris'].includes(selectedRole?.toLowerCase())" class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                    <span class="text-charcoal-muted text-[11px] font-semibold uppercase tracking-wider mr-1">Tingkat:</span>
                    <button type="button" @click="setLevelTab('all')"
                            :class="activeLevelTab === 'all' ? 'bg-primary-600 text-white font-semibold shadow-xs' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                            class="px-3 py-1 rounded-full transition-all cursor-pointer">
                        Semua (<span x-text="departments.length"></span>)
                    </button>
                    <button type="button" @click="setLevelTab('manager')"
                            :class="activeLevelTab === 'manager' ? 'bg-purple-600 text-white font-semibold shadow-xs' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                            class="px-3 py-1 rounded-full transition-all cursor-pointer">
                        Manager (4)
                    </button>
                    <button type="button" @click="setLevelTab('asman')"
                            :class="activeLevelTab === 'asman' ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                            class="px-3 py-1 rounded-full transition-all cursor-pointer">
                        Assistant Manager (11)
                    </button>
                    <button type="button" @click="setLevelTab('staff')"
                            :class="activeLevelTab === 'staff' ? 'bg-sky-600 text-white font-semibold shadow-xs' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                            class="px-3 py-1 rounded-full transition-all cursor-pointer">
                        Staff Unit (18)
                    </button>
                    <button type="button" @click="setLevelTab('spi')"
                            :class="activeLevelTab === 'spi' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'bg-white text-charcoal hover:bg-surface-100 border border-surface-200'"
                            class="px-3 py-1 rounded-full transition-all cursor-pointer">
                        SPI (2)
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
                    <div class="bg-white rounded-xl border border-surface-200 p-4 shadow-2xs">
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
                                        ? 'bg-primary-50/80 border-primary-500 ring-2 ring-primary-500/20 shadow-xs'
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
</div>
@endsection

@push('scripts')
<script>
window.userRoleDepartmentForm = function(initialRole = '', initialDept = '') {
    return {
        selectedRole: initialRole,
        selectedDept: initialDept,
        tempSelectedDept: initialDept,
        autoFilter: true,
        showAllDepartments: false,
        modalOpen: false,
        searchQuery: '',
        activeLevelTab: 'all',
        departments: @json($departments),
        lainDeptId: '{{ \App\Models\Department::where('code', 'LAIN')->first()?->id ?? 8 }}',

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
                return 'Menampilkan seluruh 36 bidang struktur organisasi PT Pelayanan Energi Batam';
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

            // For admin, direktur, komisaris
            return this.departments;
        },

        get visibleDepartmentList() {
            let list = this.departments;

            // If not in show-all mode, strictly filter by role
            if (!this.showAllDepartments) {
                list = this.filteredDepartments;
            } else if (this.activeLevelTab !== 'all') {
                // If in show-all mode and a tab is selected
                if (this.activeLevelTab === 'spi') {
                    list = list.filter(d => d.level === 'kspi' || d.level === 'auditor' || String(d.id) === String(this.lainDeptId));
                } else {
                    list = list.filter(d => d.level === this.activeLevelTab || String(d.id) === String(this.lainDeptId));
                }
            }

            // Search query filter
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

        get tempSelectedDepartmentObj() {
            if (!this.tempSelectedDept) return null;
            return this.departments.find(d => String(d.id) === String(this.tempSelectedDept)) || null;
        },

        init() {
            this.$watch('selectedRole', () => {
                this.onRoleChange();
            });
        },

        onRoleChange() {
            if (this.isStandaloneRole) {
                this.selectedDept = '';
                this.tempSelectedDept = '';
                return;
            }
            const isValid = this.filteredDepartments.some(d => String(d.id) === String(this.selectedDept));
            if (!isValid && this.selectedDept && String(this.selectedDept) !== String(this.lainDeptId)) {
                this.selectedDept = '';
                this.tempSelectedDept = '';
            }
        },

        openModal() {
            if (this.isStandaloneRole) {
                return;
            }
            if (!this.selectedRole) {
                const roleEl = document.getElementById('role');
                if (roleEl) {
                    roleEl.focus();
                    roleEl.classList.add('border-primary-500', 'ring-2', 'ring-primary-500/30');
                    setTimeout(() => {
                        roleEl.classList.remove('border-primary-500', 'ring-2', 'ring-primary-500/30');
                    }, 2500);
                }
                alert('Silakan pilih Role terlebih dahulu agar bidang yang muncul sesuai dengan posisi pengguna.');
                return;
            }

            this.tempSelectedDept = this.selectedDept;
            this.searchQuery = '';
            this.showAllDepartments = false; // Strictly filtered to selected role!
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

        applySelection() {
            this.selectedDept = this.tempSelectedDept;
            this.modalOpen = false;
        },

        selectTemp(dept) {
            this.tempSelectedDept = String(dept.id);
        },

        selectAndApply(dept) {
            this.tempSelectedDept = String(dept.id);
            this.selectedDept = String(dept.id);
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
            this.tempSelectedDept = '';
        },

        setLevelTab(tab) {
            this.activeLevelTab = tab;
        },

        getLevelBadgeClass(level) {
            const map = {
                'manager': 'bg-purple-100 text-purple-700 border border-purple-200',
                'asman': 'bg-teal-100 text-teal-800 border border-teal-200',
                'staff': 'bg-sky-100 text-sky-800 border border-sky-200',
                'kspi': 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'auditor': 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'other': 'bg-gray-100 text-gray-700 border border-gray-200',
            };
            return map[level] || 'bg-gray-100 text-gray-700 border border-gray-200';
        },

        getLevelBadgeText(level) {
            const map = {
                'manager': 'Manager',
                'asman': 'Asman',
                'staff': 'Staff',
                'kspi': 'KSPI',
                'auditor': 'Auditor',
                'other': 'Lainnya',
            };
            return map[level] || (level ? level.toUpperCase() : 'UNIT');
        }
    };
};
</script>
@endpush
