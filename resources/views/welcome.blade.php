<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMAK — Sistem Informasi Manajemen Audit & Kepatuhan</title>
    <meta name="description" content="SIMAK - Sistem Informasi Manajemen Audit & Kepatuhan PT Pelayanan Energi Batam">
    <link rel="icon" type="image/png" href="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}">
    <link rel="shortcut icon" href="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('welcomeSlider', () => ({
                isLogin: {{ ($errors->any() || session('error') || request()->has('login') || request()->is('login') || !empty($autoLogin)) ? 'true' : 'false' }},
                isLg: window.innerWidth >= 1024,
                toggleLogin() {
                    this.isLogin = !this.isLogin;
                },
                init() {
                    window.addEventListener('resize', () => {
                        this.isLg = window.innerWidth >= 1024;
                    });
                }
            }));
        });
    </script>
</head>
<body class="min-h-screen lg:h-screen lg:overflow-hidden bg-surface-50 text-charcoal m-0 p-0 antialiased font-sans flex flex-col justify-between selection:bg-primary-500 selection:text-white"
      x-data="welcomeSlider">

    {{-- ============================================
         NAVBAR
         ============================================ --}}
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-surface-200 flex-shrink-0">
        <div class="w-full px-3.5 sm:px-6 lg:px-8 py-3 sm:py-4 flex justify-between items-center gap-2">
            
            {{-- Brand Logos --}}
            <div class="flex items-center gap-2.5 sm:gap-5 min-w-0">
                <a href="{{ url('/') }}" class="flex items-center gap-2 sm:gap-3.5 min-w-0">
                    <img src="{{ asset('pln.jpg') }}?v={{ filemtime(public_path('pln.jpg')) }}" alt="PLN Batam" class="h-8 sm:h-11 md:h-12 w-auto object-contain mix-blend-multiply flex-shrink-0">
                    <img src="{{ asset('DI.png') }}" alt="Danantara Indonesia" class="h-5 sm:h-6 md:h-7 w-auto object-contain border-l border-surface-200 pl-2 sm:pl-3.5 block flex-shrink-0">
                    <img src="{{ asset('s2.png') }}?v={{ filemtime(public_path('s2.png')) }}" alt="Logo SIMAK" class="h-7 sm:h-9 md:h-10 w-auto object-contain border-l border-surface-200 pl-2 sm:pl-3.5 block flex-shrink-0">
                </a>
            </div>

            {{-- Right Action --}}
            <div class="flex-shrink-0 flex items-center gap-2 sm:gap-2.5">
                @auth
                    <form action="{{ route('logout') }}" method="POST" class="inline-flex m-0">
                        @csrf
                        <input type="hidden" name="switch_account" value="1">
                        <button type="submit"
                                id="btn-nav-ganti-akun"
                                title="Keluar & Ganti Akun"
                                class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold text-charcoal bg-surface-100 hover:bg-surface-200 border border-surface-300 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all cursor-pointer">
                            <svg class="w-4 h-4 text-charcoal-medium" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden sm:inline">Ganti Akun</span>
                        </button>
                    </form>
                    <a href="{{ route('dashboard') }}" 
                       id="btn-nav-dashboard"
                       class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl transition-all shadow-sm hover:shadow hover:-translate-y-0.5">
                        <span>Buka Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <button type="button"
                            @click="toggleLogin()" 
                            id="btn-nav-masuk"
                            class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-xl transition-all duration-300 shadow-sm hover:shadow hover:-translate-y-0.5 active:translate-y-0 focus:outline-none cursor-pointer"
                            :class="isLogin ? 'bg-surface-100 hover:bg-surface-200 text-charcoal border border-surface-300' : 'text-white bg-primary-600 hover:bg-primary-700 shadow-primary-600/25'">
                        <span x-show="!isLogin" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk</span>
                        </span>
                        <span x-show="isLogin" x-cloak class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Beranda</span>
                        </span>
                    </button>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ============================================
         HERO SECTION & SLIDING LOGIN INTERFACE
         ============================================ --}}
    <main class="flex-1 min-h-0 flex flex-col justify-center relative px-4 sm:px-6 lg:px-8 py-3 sm:py-4 lg:py-4 overflow-y-auto lg:overflow-hidden">
        
        {{-- Subtle Ambient Glows --}}
        <div class="absolute inset-0 pointer-events-none -z-10 overflow-hidden">
            <div class="absolute top-1/4 left-1/3 w-[450px] lg:w-[650px] h-[450px] lg:h-[650px] bg-gradient-to-tr from-primary-200/25 via-teal-100/20 to-transparent rounded-full blur-3xl opacity-70"></div>
            <div class="absolute bottom-10 right-10 w-80 h-80 bg-blue-100/30 rounded-full blur-3xl opacity-60"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#d8dce5_1px,transparent_1px)] [background-size:24px_24px] opacity-35"></div>
        </div>

        {{-- Main Container with Fixed Width & Overflow Hidden --}}
        <div class="w-full max-w-7xl mx-auto my-auto overflow-hidden">
            
            {{-- Sliding Flex Track --}}
            <div class="flex items-center transition-transform duration-700 ease-[cubic-bezier(0.25,1,0.5,1)] will-change-transform w-[200%] lg:w-[150%]"
                 :style="isLogin ? (isLg ? 'transform: translateX(-33.333333%);' : 'transform: translateX(-50%);') : 'transform: translateX(0%);'">
                
                {{-- ============================================
                     SLOT 1: HERO TYPOGRAPHY & ACTIONS
                     ============================================ --}}
                <div class="w-1/2 lg:w-1/3 flex-shrink-0 px-4 sm:px-6 lg:px-8 xl:px-10 flex flex-col items-center lg:items-start text-center lg:text-left transition-opacity duration-500"
                     :class="isLogin ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                    
                    {{-- Institutional Pill Badge --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs sm:text-sm font-semibold bg-white/95 border border-primary-200/80 text-primary-700 shadow-sm backdrop-blur-sm mb-3 sm:mb-4 lg:mb-4.5 transition-transform hover:scale-105">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                        <span>SIMAK • PT Pelayanan Energi Batam</span>
                    </div>

                    {{-- Main Title --}}
                    <h1 class="text-3xl sm:text-5xl lg:text-5xl xl:text-[3.5rem] 2xl:text-6xl font-extrabold tracking-tight text-charcoal leading-[1.14] sm:leading-[1.10] mb-3.5 sm:mb-4.5 lg:mb-5">
                        SIMAK <br>
                        <span class="bg-gradient-to-r from-primary-600 via-primary-500 to-teal-500 bg-clip-text text-transparent">
                            Sistem Informasi Manajemen Audit & Kepatuhan
                        </span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-sm sm:text-base lg:text-lg text-charcoal-medium max-w-xl xl:max-w-2xl leading-relaxed mb-4 sm:mb-5 lg:mb-7">
                        Platform terpadu untuk penyusunan, pengelolaan, dan pengawasan dokumen audit secara efisien, transparan, dan akuntabel di lingkungan <strong class="text-charcoal font-semibold">PT Pelayanan Energi Batam</strong>.
                    </p>

                    {{-- Mobile & Tablet Illustration (< 1024px) --}}
                    <div class="lg:hidden w-full flex items-center justify-center my-3 sm:my-4 relative">
                        <div class="absolute w-52 sm:w-64 h-52 sm:h-64 bg-gradient-to-tr from-primary-300/30 via-teal-200/20 to-amber-200/15 rounded-full blur-2xl -z-10 pointer-events-none"></div>
                        <img src="{{ asset('cart.png') }}?v={{ filemtime(public_path('cart.png')) }}" 
                             alt="SIMAK - Sistem Informasi Manajemen Audit & Kepatuhan - PT Pelayanan Energi Batam" 
                             class="w-full max-w-[240px] sm:max-w-[320px] max-h-[190px] sm:max-h-[250px] h-auto object-contain filter drop-shadow-xl transition-all duration-300">
                    </div>

                    {{-- Primary Action Button --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               id="btn-hero-dashboard"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 sm:px-8 py-3.5 sm:py-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg shadow-primary-600/25 hover:shadow-xl hover:shadow-primary-600/35 hover:-translate-y-0.5 active:translate-y-0 transition-all text-sm sm:text-base">
                                <span>Buka Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto m-0">
                                @csrf
                                <input type="hidden" name="switch_account" value="1">
                                <button type="submit"
                                        id="btn-hero-ganti-akun"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 sm:px-7 py-3.5 sm:py-4 bg-white hover:bg-surface-100 text-charcoal font-semibold rounded-xl border border-surface-300 shadow-sm hover:shadow hover:-translate-y-0.5 active:translate-y-0 transition-all text-sm sm:text-base cursor-pointer">
                                    <svg class="w-4 h-4 text-charcoal-medium" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Keluar / Ganti Akun</span>
                                </button>
                            </form>
                        @else
                            <button type="button"
                                    @click="isLogin = true" 
                                    id="btn-mulai-sesi"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 sm:px-9 py-3.5 sm:py-4 bg-charcoal hover:bg-black text-white font-semibold rounded-xl shadow-lg shadow-charcoal/20 hover:shadow-xl hover:shadow-charcoal/30 hover:-translate-y-0.5 active:translate-y-0 transition-all text-sm sm:text-base cursor-pointer">
                                <span>Mulai Sesi</span>
                                <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        @endauth
                    </div>

                    @auth
                        <div class="mt-3 inline-flex items-center gap-2 text-xs text-charcoal-muted">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Masuk sebagai <strong class="text-charcoal font-semibold">{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
                        </div>
                    @endauth

                    {{-- Trust Indicators --}}
                    <div class="mt-5 sm:mt-6 lg:mt-7 flex flex-wrap items-center justify-center lg:justify-start gap-3.5 sm:gap-4.5 xl:gap-6 text-xs xl:text-sm text-charcoal-muted">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Kertas Kerja Digital
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approval Multi-Jenjang
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Monitoring Real-time
                        </span>
                    </div>

                </div>

                {{-- ============================================
                     SLOT 2: CART ILLUSTRATION (SLIDES LEFT ON LOGIN)
                     ============================================ --}}
                <div class="hidden lg:flex lg:w-1/3 flex-shrink-0 px-4 sm:px-6 lg:px-8 xl:px-10 items-center justify-center relative">
                    <div class="absolute w-72 sm:w-88 lg:w-[460px] h-72 sm:h-88 lg:h-[460px] bg-gradient-to-tr from-primary-300/35 via-teal-200/25 to-amber-200/20 rounded-full blur-3xl -z-10 pointer-events-none"></div>

                    {{-- Floating Glass Accent Badges --}}
                    <div class="absolute -top-1 xl:-top-2 right-4 xl:right-8 z-20 bg-white/95 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-surface-200/90 shadow-lg shadow-primary-950/10 flex items-center gap-2 pointer-events-none">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-semibold text-charcoal">Dokumen Terintegrasi</span>
                    </div>

                    <div class="absolute -bottom-1 xl:-bottom-2 left-4 xl:left-8 z-20 bg-white/95 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-surface-200/90 shadow-lg shadow-primary-950/10 flex items-center gap-2 pointer-events-none">
                        <span class="text-xs font-bold text-primary-600">SIMAK PLN PEB</span>
                        <span class="text-xs text-charcoal-muted">| 100% Digital</span>
                    </div>

                    <img src="{{ asset('cart.png') }}?v={{ filemtime(public_path('cart.png')) }}" 
                         alt="SIMAK - Sistem Informasi Manajemen Audit & Kepatuhan - PT Pelayanan Energi Batam" 
                         style="max-height: min(450px, calc(100vh - 180px));"
                         class="relative z-10 w-full max-w-[460px] lg:max-w-[480px] xl:max-w-[530px] h-auto object-contain filter drop-shadow-2xl transition-all duration-500 ease-out hover:scale-[1.02]">
                </div>

                {{-- ============================================
                     SLOT 3: TABLE / CARD LOGIN (SLIDES IN ON LOGIN)
                     ============================================ --}}
                <div class="w-1/2 lg:w-1/3 flex-shrink-0 px-4 sm:px-6 lg:px-8 xl:px-10 flex items-center justify-center transition-opacity duration-500"
                     :class="!isLogin ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                    
                    <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-2xl sm:rounded-3xl border border-surface-200/80 shadow-2xl shadow-primary-950/10 p-6 sm:p-7 relative max-h-[calc(100vh-120px)] overflow-y-auto">
                        {{-- Top Accent Tag --}}
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-bold tracking-wider uppercase text-primary-700">Google Workspace SSO</span>
                            </div>
                            <button type="button" 
                                    @click="isLogin = false" 
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-charcoal-muted hover:text-charcoal px-2.5 py-1 rounded-lg hover:bg-surface-100 transition-colors cursor-pointer"
                                    title="Tutup & Kembali ke Beranda">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="hidden sm:inline">Tutup</span>
                            </button>
                        </div>

                        <div class="mb-5 text-left">
                            <h2 class="text-xl sm:text-2xl font-extrabold text-charcoal tracking-tight">Selamat Datang di SIMAK</h2>
                            <p class="text-xs sm:text-sm text-charcoal-medium mt-1">Akses masuk terpadu Sistem Informasi Manajemen Audit & Kepatuhan.</p>
                        </div>

                        {{-- Session Alerts --}}
                        @if (session('error'))
                            <div class="mb-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs sm:text-sm text-red-700 flex items-start gap-2.5">
                                <svg class="w-4 h-4 flex-shrink-0 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="leading-relaxed">{{ session('error') }}</span>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs sm:text-sm text-emerald-800 flex items-start gap-2.5">
                                <svg class="w-4 h-4 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="leading-relaxed">{{ session('success') }}</span>
                            </div>
                        @endif

                        {{-- Corporate Info Box --}}
                        <div class="mb-5 p-4 bg-surface-50 border border-surface-200 rounded-2xl text-left space-y-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-charcoal">
                                <div class="w-6 h-6 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <span>Single Sign-On (SSO) Resmi</span>
                            </div>
                            <p class="text-xs text-charcoal-muted leading-relaxed">
                                Silakan masuk menggunakan akun email resmi Google Workspace Anda (<strong class="text-charcoal font-semibold">@peb.co.id</strong>) yang telah didaftarkan oleh Administrator SPI.
                            </p>
                        </div>

                        {{-- Google Login CTA Button & Action --}}
                        <div class="space-y-3">
                            <a href="{{ route('auth.google') }}" 
                               id="btn-login-google"
                               class="w-full flex items-center justify-center gap-3 px-4 py-3.5 bg-white hover:bg-surface-50 text-charcoal text-xs sm:text-sm font-bold rounded-xl border border-surface-300 hover:border-primary-500 shadow-sm hover:shadow transition-all cursor-pointer group">
                                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <span>Masuk dengan Akun Google</span>
                            </a>

                            <button type="button" 
                                    @click="isLogin = false" 
                                    id="btn-login-kembali"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-charcoal-medium hover:text-charcoal bg-surface-100 hover:bg-surface-200 border border-surface-200 rounded-xl transition-all cursor-pointer"
                                    title="Kembali ke Beranda">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                <span>Kembali ke Beranda</span>
                            </button>
                        </div>

                        {{-- Footer Help Text --}}
                        <div class="mt-6 pt-4 border-t border-surface-200 text-center">
                            <p class="text-[11px] text-charcoal-muted">
                                Belum memiliki akun atau butuh penyesuaian hak akses? Hubungi <strong class="text-charcoal font-semibold">Administrator SPI</strong>.
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </main>

    {{-- ============================================
         FOOTER
         ============================================ --}}
    <footer class="w-full mt-auto bg-surface-50 border-t border-surface-200 flex-shrink-0">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-3.5 sm:py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-charcoal-muted">
                <p class="text-center sm:text-left">
                    &copy; {{ date('Y') }} PT Pelayanan Energi Batam. Seluruh hak cipta dilindungi.
                </p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>SIMAK &bull; Sistem Informasi Manajemen Audit & Kepatuhan</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
