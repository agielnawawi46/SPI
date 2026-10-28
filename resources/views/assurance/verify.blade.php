<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Dokumen — SIMAK PT Pelayanan Energi Batam</title>
    <link rel="icon" type="image/png" href="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}">
    <link rel="shortcut icon" href="{{ asset('pln.png') }}?v={{ filemtime(public_path('pln.png')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-50 flex items-center justify-center p-4 py-10">
    <div class="max-w-3xl w-full bg-white rounded-3xl shadow-card-lg overflow-hidden border border-surface-200 flex flex-col md:flex-row relative">
        
        {{-- Left Side: Status & Details --}}
        <div class="w-full md:w-1/2 p-8 text-center relative border-b md:border-b-0 md:border-r border-surface-200">
            {{-- Decorative top accent --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-36 h-1.5 bg-emerald-500 rounded-b-full"></div>

            {{-- Header / Brand --}}
            <div class="flex items-center justify-center gap-2 mb-6 mt-1">
                <img src="{{ asset('pln.png') }}" alt="PLN / PEB" class="h-7 w-auto object-contain">
                <span class="text-xs font-bold tracking-wider text-charcoal-muted uppercase">SIMAK • PT Pelayanan Energi Batam</span>
            </div>

            {{-- Verification Status --}}
            <div class="flex justify-center mb-4">
                <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center relative">
                    <div class="absolute inset-0 rounded-full border border-emerald-200 animate-ping opacity-25"></div>
                    <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>
            <h1 class="text-2xl font-extrabold text-charcoal tracking-tight">Dokumen Sah & Valid</h1>
            <p class="text-xs text-charcoal-muted mt-1.5 font-medium">Dokumen ini resmi diterbitkan & disahkan melalui SIMAK PT Pelayanan Energi Batam.</p>

            <hr class="my-6 border-surface-200">

            {{-- Document Details --}}
            <div class="space-y-4 text-left">
                <div>
                    <p class="text-[10px] font-bold text-charcoal-muted uppercase tracking-wider mb-1">Judul / Nomor Dokumen</p>
                    <p class="text-charcoal font-bold text-base leading-snug">{{ $document->title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-charcoal-muted uppercase tracking-wider mb-1">Kategori Audit</p>
                        <p class="text-charcoal font-semibold capitalize text-sm">{{ str_replace('_', ' ', $document->audit_type) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-charcoal-muted uppercase tracking-wider mb-1">Jenis Dokumen</p>
                        <p class="text-charcoal font-semibold capitalize text-sm">{{ $document->documentType?->name ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-charcoal-muted uppercase tracking-wider mb-1">Tanggal Pengesahan</p>
                    <p class="text-charcoal font-semibold text-sm">{{ $document->approved_at ? $document->approved_at->format('d F Y, H:i') . ' WIB' : ($document->updated_at ? $document->updated_at->format('d F Y, H:i') . ' WIB' : '-') }}</p>
                </div>
            </div>
        </div>

        {{-- Right Side: Approval Signatures --}}
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-between bg-surface-50/50">
            <div class="text-left flex-1">
                <div class="flex items-center justify-between mb-5">
                    <p class="text-[10px] font-bold text-charcoal-muted uppercase tracking-wider">Riwayat Otorisasi Pejabat</p>
                    <span class="badge badge-approved text-[10px]">Terverifikasi Digital</span>
                </div>
                
                <div class="relative space-y-5">
                    @forelse($document->approvals->where('action', 'approve')->sortBy('created_at') as $approval)
                    <div class="relative z-10 flex items-start gap-3.5 p-3 rounded-xl bg-white border border-surface-200 shadow-2xs">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-charcoal truncate">{{ $approval->approver?->name ?? 'Pejabat Berwenang' }}</p>
                            <p class="text-[11px] text-charcoal-muted mt-0.5">
                                {{ $approval->approval_role === 'kspi' ? 'Kepala Satuan Pengawasan Internal (KSPI)' : ucwords(str_replace('-', ' ', $approval->approval_role)) }}
                            </p>
                            <p class="text-[10px] text-charcoal-muted/80 mt-1 font-mono">
                                {{ $approval->acted_at ? $approval->acted_at->format('d/m/Y H:i:s') : '-' }} WIB
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-white border border-surface-200 text-center">
                        <p class="text-xs text-charcoal-muted">Disahkan secara resmi oleh Satuan Pengawasan Internal.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-surface-200 text-center md:text-left">
                <p class="text-[10px] text-charcoal-muted">
                    &copy; {{ date('Y') }} PT Pelayanan Energi Batam &bull; Sertifikat Keaslian Dokumen Elektronik SIMAK.
                </p>
            </div>
        </div>

    </div>
</body>
</html>

