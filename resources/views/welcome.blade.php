<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPI PT Pelayanan Energi Batam</title>
    <meta name="description" content="Sistem Informasi Intranet Satuan Pengawasan Internal PT Pelayanan Energi Batam">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-surface-50 text-charcoal m-0 p-0 antialiased font-sans flex flex-col selection:bg-primary-500 selection:text-white">

    {{-- ============================================
         NAVBAR
         ============================================ --}}
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-surface-200 flex-shrink-0">
        <div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 py-3.5 sm:py-4 flex justify-between items-center">
            
            {{-- Brand Logos --}}
            <div class="flex items-center gap-4 sm:gap-5">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('pln.jpg') }}" alt="PLN Batam" class="h-10 sm:h-12 w-auto object-contain mix-blend-multiply filter contrast-125">
                    <img src="{{ asset('DI.png') }}" alt="Danantara Indonesia" class="h-6 sm:h-7 w-auto object-contain border-l border-surface-200 pl-3 sm:pl-4 hidden sm:block">
                </a>
                
                <div class="hidden md:flex items-center gap-2.5 pl-4 border-l border-surface-200">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-primary-50 text-primary-700">
                        Intranet SPI
                    </span>
                    <span class="text-xs text-charcoal-muted">PT Pelayanan Energi Batam</span>
                </div>
            </div>

            {{-- Right Action --}}
            <div>
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl transition-all shadow-sm hover:shadow hover:-translate-y-0.5">
                        <span>Buka Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 px-5 sm:px-6 py-2 sm:py-2.5 rounded-xl transition-all shadow-sm hover:shadow hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span>Masuk</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ============================================
         HERO SECTION (Centered Single-Screen Experience)
         ============================================ --}}
    <main class="flex-1 flex flex-col justify-center relative px-6 sm:px-10 lg:px-12 py-8 sm:py-12 lg:py-16 overflow-hidden">
        
        {{-- Subtle Ambient Glows --}}
        <div class="absolute inset-0 pointer-events-none -z-10 overflow-hidden">
            <div class="absolute top-1/4 left-1/3 w-[450px] lg:w-[650px] h-[450px] lg:h-[650px] bg-gradient-to-tr from-primary-200/25 via-teal-100/20 to-transparent rounded-full blur-3xl opacity-70"></div>
            <div class="absolute bottom-10 right-10 w-80 h-80 bg-blue-100/30 rounded-full blur-3xl opacity-60"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#d8dce5_1px,transparent_1px)] [background-size:24px_24px] opacity-35"></div>
        </div>

        {{-- Main Grid Container --}}
        <div class="w-full max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 xl:gap-16 items-center">
                
                {{-- Left Column: Typography & Action --}}
                <div class="lg:col-span-6 xl:col-span-6 flex flex-col items-center lg:items-start text-center lg:text-left">
                    
                    {{-- Institutional Pill Badge --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white/95 border border-primary-200/80 text-primary-700 shadow-sm backdrop-blur-sm mb-5 transition-transform hover:scale-105">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                        <span>Satuan Pengawasan Internal • PT PEB</span>
                    </div>

                    {{-- Main Title --}}
                    <h1 class="text-3xl sm:text-5xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight text-charcoal leading-[1.18] sm:leading-[1.12] mb-4 sm:mb-5">
                        Sistem Informasi <br>
                        <span class="bg-gradient-to-r from-primary-600 via-primary-500 to-teal-500 bg-clip-text text-transparent">
                            Pengawasan Internal
                        </span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-sm sm:text-base lg:text-lg text-charcoal-medium max-w-xl leading-relaxed mb-7 sm:mb-8">
                        Platform terpadu untuk penyusunan, pengelolaan, dan pengawasan dokumen audit secara efisien, transparan, dan akuntabel di lingkungan <strong class="text-charcoal font-semibold">PT Pelayanan Energi Batam</strong>.
                    </p>

                    {{-- Primary Action Button --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg shadow-primary-600/25 hover:shadow-xl hover:shadow-primary-600/35 hover:-translate-y-0.5 active:translate-y-0 transition-all text-sm sm:text-base">
                                <span>Buka Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 bg-charcoal hover:bg-black text-white font-semibold rounded-xl shadow-lg shadow-charcoal/20 hover:shadow-xl hover:shadow-charcoal/30 hover:-translate-y-0.5 active:translate-y-0 transition-all text-sm sm:text-base">
                                <span>Mulai Sesi</span>
                                <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endauth
                    </div>

                    {{-- Trust Indicators --}}
                    <div class="mt-7 sm:mt-8 flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 text-xs text-charcoal-muted">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Kertas Kerja Digital
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approval Multi-Jenjang
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Monitoring Real-time
                        </span>
                    </div>

                </div>

                {{-- Right Column: Combined Hero Illustration (dok & dok2) --}}
                <div class="lg:col-span-6 xl:col-span-6 w-full relative flex items-center justify-center mt-8 lg:mt-0 select-none">
                    
                    {{-- Ambient Glow Aura matching PLN brand colors --}}
                    <div class="absolute w-80 sm:w-96 lg:w-[520px] h-72 sm:h-88 lg:h-[420px] bg-gradient-to-tr from-primary-300/35 via-teal-200/25 to-amber-200/20 rounded-full blur-3xl -z-10 pointer-events-none"></div>

                    {{-- Unified Combined Artwork --}}
                    <div class="relative w-full max-w-2xl flex items-center justify-center p-1 sm:p-2 group">
                        <img src="{{ asset('dok_combined.png') }}?v={{ filemtime(public_path('dok_combined.png')) }}" 
                             alt="Sistem Informasi Pengawasan Internal - PT Pelayanan Energi Batam" 
                             class="w-full max-w-[580px] lg:max-w-[640px] xl:max-w-[680px] h-auto object-contain filter drop-shadow-2xl transition-all duration-500 ease-out group-hover:scale-[1.02]">
                    </div>

                </div>

            </div>
        </div>

    </main>

    {{-- ============================================
         FOOTER
         ============================================ --}}
    <footer class="w-full mt-auto bg-surface-50 border-t border-surface-200 flex-shrink-0">
        <div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 py-4 sm:py-5">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-charcoal-muted">
                <p class="text-center sm:text-left">
                    &copy; {{ date('Y') }} PT Pelayanan Energi Batam. Seluruh hak cipta dilindungi.
                </p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Sistem Intranet Operasional SPI</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
