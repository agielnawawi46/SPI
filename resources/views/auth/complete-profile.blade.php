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
</head>
<body class="min-h-screen bg-surface-50 text-charcoal antialiased font-sans flex flex-col justify-between selection:bg-primary-500 selection:text-white">

    {{-- Top Navbar --}}
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-surface-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center gap-4">
            {{-- Brand Logos --}}
            <div class="flex items-center gap-2.5 sm:gap-4">
                <img src="{{ asset('pln.jpg') }}" alt="PLN Batam" class="h-8 sm:h-10 w-auto object-contain mix-blend-multiply filter contrast-125">
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
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
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
                        Akun Google Workspace Anda telah terverifikasi. Mohon lengkapi NIP dan Bidang kerja Anda untuk mengaktifkan akses sistem.
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
                <form action="{{ route('profile.complete.store') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- NIP --}}
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

                    {{-- Bidang / Departemen --}}
                    <div>
                        <label for="department_id" class="block text-xs font-bold text-charcoal mb-1.5">
                            Bidang / Departemen <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-charcoal-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <select name="department_id" 
                                    id="department_id" 
                                    required
                                    class="form-select !pl-10 @error('department_id') !border-red-500 @enderror">
                                <option value="" disabled {{ old('department_id', $user->department_id) ? '' : 'selected' }}>-- Pilih Bidang / Departemen Anda --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} {{ $dept->code ? '(' . $dept->code . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('department_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jabatan (Posisi) --}}
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

                    {{-- Role Information Banner --}}
                    <div class="p-4 bg-primary-50/60 border border-primary-200/80 rounded-2xl flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="text-xs text-charcoal leading-relaxed">
                            <strong class="font-bold text-primary-800">Hak Akses Default: Staff (Auditee)</strong>
                            <p class="text-charcoal-medium mt-0.5">
                                Akun Anda otomatis diaktifkan sebagai peran Staff/Auditee. Jika Anda merupakan Auditor SPI atau memerlukan wewenang khusus, Administrator SPI dapat memperbarui peran Anda setelah data profil ini disimpan.
                            </p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm hover:shadow hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer">
                            <span>Simpan Profil & Masuk ke Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </main>

    {{-- Footer --}}
    <footer class="w-full bg-white border-t border-surface-200 py-3.5 px-4 text-center text-xs text-charcoal-muted">
        &copy; {{ date('Y') }} PT Pelayanan Energi Batam &bull; SIMAK (Sistem Informasi Manajemen Audit & Kepatuhan)
    </footer>

</body>
</html>

