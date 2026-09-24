<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — SPI PT Pelayanan Energi Batam</title>
    <meta name="description" content="Sistem Informasi Intranet Satuan Pengawasan Internal PT Pelayanan Energi Batam">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-surface-50">

<div class="min-h-screen flex items-center justify-center p-6 relative bg-gradient-to-br from-surface-50 to-surface-100">

    {{-- Login Container --}}
    <div class="w-full max-w-4xl flex flex-col md:flex-row items-center gap-12 lg:gap-16">
        
        {{-- Left Side: Logo & Title (Outside Card) --}}
        <div class="md:w-1/2 w-full flex flex-col items-center justify-center text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 lg:w-24 lg:h-24 bg-primary-500 rounded-2xl mb-6 shadow-lg">
                <svg class="w-12 h-12 lg:w-14 lg:h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h2 class="text-3xl lg:text-4xl font-bold text-charcoal mb-2">SPI Documents</h2>
            <p class="text-base lg:text-lg font-medium text-primary-700">PT Pelayanan Energi Batam</p>
        </div>

        {{-- Right Side: Login Form Card --}}
        <div class="md:w-1/2 w-full">
            <div class="card p-8 lg:p-10 shadow-card-md">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-charcoal">Selamat Datang</h2>
                    <p class="text-charcoal-muted mt-1 text-sm">Masuk ke Sistem Informasi SPI</p>
                </div>

                {{-- Session Error --}}
                @if (session('error'))
                    <div class="alert alert-error mb-6">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form id="login-form" action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                value="{{ old('email') }}"
                                placeholder="nama@spi.internal"
                                class="form-input pl-10 @error('email') border-red-400 @enderror"
                                required
                            >
                        </div>
                        @error('email')
                            <p class="form-error">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group" x-data="{ showPwd: false }">
                        <label for="password" class="form-label">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                name="password"
                                :type="showPwd ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="form-input pl-10 pr-10 @error('password') border-red-400 @enderror"
                                required
                            >
                            <button type="button" @click="showPwd = !showPwd" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-charcoal-muted hover:text-charcoal">
                                <svg x-show="!showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary-500 focus:ring-primary-400">
                        <label for="remember" class="ml-2 text-sm text-charcoal-medium">Ingat saya</label>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center gap-3 mt-4">
                        {{-- Back to Home --}}
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-charcoal-muted hover:text-primary-700 bg-surface-100 hover:bg-surface-200 px-4 py-3 rounded-lg transition-all duration-300 border border-surface-200 hover:border-primary-200 group flex-shrink-0" title="Kembali ke Beranda">
                            <svg class="w-4 h-4 text-charcoal group-hover:text-primary-600 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            Kembali
                        </a>

                        {{-- Submit --}}
                        <button type="submit" id="login-btn" class="btn-primary flex-1 h-[46px]">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Masuk ke Sistem
                        </button>
                    </div>
            </form>
            </div>

            <p class="text-center text-xs text-charcoal-muted mt-6">
                Copyright &copy; {{ date('Y') }} IT Infrastructure — PT Pelayanan Energi Batam
            </p>
        </div>

    </div>
</div>

</body>
</html>
