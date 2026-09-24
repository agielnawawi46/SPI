<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPI PT Pelayanan Energi Batam</title>
    <meta name="description" content="Sistem Informasi Intranet Satuan Pengawasan Internal PT Pelayanan Energi Batam">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-surface-50 text-charcoal m-0 p-0 antialiased font-sans flex flex-col min-h-screen">

    {{-- Navbar (Clean & Minimal) --}}
    <nav class="w-full px-6 py-5 md:px-12 flex justify-between items-center bg-white border-b border-surface-200">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <span class="font-bold text-lg text-charcoal tracking-tight">SPI Documents</span>
        </div>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-charcoal hover:text-primary-600 transition-colors">Ke Dashboard &rarr;</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 px-5 py-2.5 rounded-lg transition-colors shadow-sm">Masuk</a>
            @endauth
        </div>
    </nav>

    {{-- Main Container (Asymmetrical Split Layout) --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-6 md:px-12 py-12 lg:py-20 grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">
        
        {{-- Left: Typography & CTA --}}
        <div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.15] tracking-tight text-charcoal mb-6">
                Sistem Informasi <br>
                <span class="text-primary-600">Pengawasan Internal</span>
            </h1>
            <p class="text-lg md:text-xl text-charcoal-medium mb-10 max-w-lg leading-relaxed">
                Platform terpadu untuk penyusunan, pengelolaan, dan pengawasan dokumen audit secara efisien di lingkungan PT Pelayanan Energi Batam.
            </p>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary btn-lg inline-flex items-center px-8">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3.5 bg-charcoal text-white font-semibold rounded-lg hover:bg-charcoal-dark transition-colors shadow-md text-lg">
                    Mulai Sesi
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endauth
        </div>

        {{-- Right: Features (Minimalist Cards) --}}
        <div class="space-y-4">
            @foreach([
                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Manajemen Dokumen', 'desc' => 'Penyusunan & pengelolaan kertas kerja audit terintegrasi yang lebih efisien.'],
                ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Sistem Approval', 'desc' => 'Persetujuan berjenjang antar departemen dengan validasi akurat.'],
                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => 'Monitoring Real-time', 'desc' => 'Pemantauan tindak lanjut temuan secara transparan dan instan.'],
            ] as $feature)
            <div class="flex items-start gap-5 p-6 rounded-xl border border-surface-200 bg-white hover:border-primary-200 hover:shadow-sm transition-all duration-300">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-surface-100 flex items-center justify-center text-primary-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-charcoal mb-1">{{ $feature['title'] }}</h3>
                    <p class="text-charcoal-medium leading-relaxed text-sm">{{ $feature['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </main>

    {{-- Footer (Solid) --}}
    <footer class="w-full px-6 md:px-12 py-6 border-t border-surface-200 bg-white mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-charcoal-muted">
            <p>&copy; {{ date('Y') }} PT Pelayanan Energi Batam. Seluruh hak cipta dilindungi.</p>
            <p class="font-medium">IT Infrastructure Department</p>
        </div>
    </footer>

</body>
</html>
