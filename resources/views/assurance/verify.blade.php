<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen | SPI PEB</title>
    <link rel="icon" type="image/png" href="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}">
    <link rel="shortcut icon" href="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            background-color: #f9fafb; 
            font-family: 'Inter', sans-serif; 
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 py-10">
    <div class="max-w-3xl w-full bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-gray-100 flex flex-col md:flex-row relative">
        
        {{-- Left Side: Status & Details --}}
        <div class="w-full md:w-1/2 p-8 text-center relative border-b md:border-b-0 md:border-r border-gray-100 border-dashed">
            {{-- Decorative top blur --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-1 bg-green-400 rounded-b-full"></div>

            {{-- Logo/Header Area --}}
            <div class="mb-8 mt-2">
                <p class="text-[10px] font-extrabold tracking-[0.2em] text-gray-400 uppercase">Sistem Pengendalian Internal</p>
            </div>

            {{-- Verification Status --}}
            <div class="flex justify-center mb-5">
                <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center relative">
                    <div class="absolute inset-0 rounded-full border border-green-100 animate-ping opacity-20"></div>
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-green-500 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Dokumen Valid</h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Telah diverifikasi dan disetujui secara resmi.</p>

            <hr class="my-8 border-gray-100 border-dashed">

            {{-- Document Details --}}
            <div class="space-y-5 text-left">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nomor Surat</p>
                    <p class="text-gray-900 font-semibold text-lg">{{ $document->title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kategori</p>
                        <p class="text-gray-900 font-medium capitalize">{{ str_replace('_', ' ', $document->audit_type) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Jenis</p>
                        <p class="text-gray-900 font-medium capitalize">{{ $document->documentType?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Approval Signatures --}}
        <div class="w-full md:w-1/2 p-8 flex flex-col">
            <div class="text-left flex-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-5">Otorisasi Oleh</p>
                
                <div class="relative space-y-6">
                    {{-- Timeline Connector Line --}}
                    <div class="absolute left-5 top-5 bottom-5 w-[2px] bg-gray-100 -translate-x-1/2 z-0"></div>

                    @foreach($document->approvals->where('action', 'approve')->sortBy('created_at') as $approval)
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-green-500 shadow-sm border border-gray-100 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="bg-white py-0.5">
                            <p class="text-sm font-bold text-gray-900">{{ $approval->approver?->name ?? 'Sistem' }}</p>
                            <p class="text-[11px] text-gray-500">{{ $approval->approval_role === 'kspi' ? 'KSPI' : ucwords(str_replace('-', ' ', $approval->approval_role)) }} <span class="mx-1">•</span> {{ $approval->acted_at ? $approval->acted_at->format('d F Y, H:i') : '-' }}</p>
                        </div>
                        <div class="ml-auto text-green-500 bg-white pl-2">
                            <svg class="w-5 h-5 opacity-50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-gray-50 text-center md:text-left">
                <p class="text-[10px] font-medium text-gray-400">© {{ date('Y') }} SPI PEB. Sistem Verifikasi Dokumen Digital.</p>
            </div>
        </div>

    </div>
</body>
</html>
