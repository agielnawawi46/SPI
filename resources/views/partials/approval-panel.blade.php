{{-- Reusable Approval Panel Partial --}}
{{-- Usage: @include('partials.approval-panel', ['document' => $document, 'submitRoute' => route('...')]) --}}

@php 
    $pendingApproval = $document->approvals()->whereNull('action')->first(); 
    $user = Auth::user();
@endphp

{{-- QR Code Resmi & Stepper Container saat Approved / In-Review --}}
<div class="grid grid-cols-1 {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:grid-cols-3' : '' }} gap-4 mt-6">
    
    {{-- Stepper Riwayat Persetujuan --}}
    @if($document->approvals->isNotEmpty())
    <div class="card {{ ($document->status === 'approved' && $document->qr_code_path) ? 'lg:col-span-2' : '' }} border border-surface-200 shadow-2xs">
        <div class="card-body py-4 px-5 overflow-x-auto">
            <h3 class="text-sm font-semibold text-charcoal mb-4">Riwayat Persetujuan Digital</h3>
            <div class="flex items-start w-full min-w-[360px]">
                @foreach($document->approvals->sortBy('created_at') as $approval)
                <div class="relative flex-1 text-center group px-1">
                    @if(!$loop->last)
                    <div class="absolute top-3.5 left-[50%] w-full h-0.5 {{ $approval->action === 'approve' ? 'bg-emerald-400' : 'bg-surface-200' }} z-0 mt-[1px]"></div>
                    @endif

                    <div class="relative z-10 mx-auto w-7 h-7 flex items-center justify-center rounded-full border-2 border-white shadow-sm {{ 
                        $approval->action === 'approve' ? 'bg-emerald-100 text-emerald-600' : 
                        ($approval->action === 'reject' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600') 
                    }}">
                        @if($approval->action === 'approve')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @elseif($approval->action === 'reject')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>

                    <div class="mt-2">
                        <h4 class="text-[11px] font-semibold text-charcoal leading-tight">
                            Level {{ $approval->approval_level }} &mdash; <span class="capitalize">{{ $approval->approval_role }}</span>
                        </h4>
                        <p class="text-[10px] font-bold mt-1 leading-none {{ 
                            $approval->action === 'approve' ? 'text-emerald-600' : 
                            ($approval->action === 'reject' ? 'text-red-600' : 'text-amber-600') 
                        }}">
                            {{ $approval->action === 'approve' ? 'Disetujui' : ($approval->action === 'reject' ? 'Revisi' : 'Menunggu') }}
                        </p>
                        @if($approval->acted_at)
                        <p class="text-[10px] text-charcoal-muted mt-1 leading-none">{{ $approval->acted_at->format('d M Y, H:i') }}</p>
                        @endif
                        @if($approval->comments)
                        <p class="text-[10px] text-charcoal mt-1 bg-surface-50 p-1.5 rounded border border-surface-200 text-left font-medium">"{{ $approval->comments }}"</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- QR Code Resmi saat Approved --}}
    @if($document->status === 'approved' && $document->qr_code_path)
    <div class="card bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 lg:col-span-1 h-full shadow-2xs">
        <div class="card-body p-4 md:p-5 flex flex-row justify-center items-center gap-4 h-full">
            <div class="bg-white p-2 rounded-2xl shadow-sm flex-shrink-0">
                <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="w-20 h-20 md:w-24 md:h-24 object-contain">
            </div>
            <div class="text-left">
                <h3 class="font-bold text-emerald-950 text-sm md:text-base">Dokumen Resmi Digital</h3>
                <p class="text-[11px] text-emerald-700 mt-1 font-medium">Tgl: {{ $document->approved_at?->format('d M Y') }}</p>
                @if($document->uuid)
                <a href="{{ route('document.verify', $document->uuid) }}" target="_blank" class="mt-3 text-[10px] font-bold text-emerald-800 bg-white hover:bg-emerald-50 py-1.5 px-3 rounded-full shadow-2xs transition-colors border border-emerald-200 inline-flex items-center gap-1 uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Verifikasi
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Submit for Review Card --}}
@if(in_array($document->status, ['draft', 'revised']) && ($document->created_by === Auth::id() || Auth::user()->hasRole('admin')))
    <div class="card border-l-4 border-l-amber-400 shadow-2xs mt-4">
        <div class="card-body p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-charcoal">Kirim untuk Direview</h3>
                <p class="text-sm text-charcoal-muted mt-1">Dokumen ini belum diajukan. Kirim ke pejabat terkait untuk direview dan disahkan.</p>
            </div>
            <form action="{{ $submitRoute }}" method="POST" class="flex-shrink-0" onsubmit="return confirm('Kirim dokumen ini untuk direview oleh KSPI?')">
                @csrf
                <button type="submit" class="btn-primary text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim untuk Direview (KSPI)
                </button>
            </form>
        </div>
    </div>
@endif

{{-- Approval Action Panel (for KSPI / Direktur) --}}
@if($pendingApproval && Auth::user()->hasRole($pendingApproval->approval_role))
    <div class="card border-l-4 border-l-blue-400 shadow-2xs mt-4">
        <div class="card-body p-5">
            <h3 class="font-semibold text-charcoal mb-1">Tindakan Persetujuan &mdash; {{ strtoupper(str_replace('_', ' ', $pendingApproval->approval_role)) }}</h3>
            <p class="text-sm text-charcoal-muted mb-4">Periksa isi dokumen. Anda dapat menyetujui atau mengembalikan untuk direvisi.</p>
            <form action="{{ route('approvals.action', $pendingApproval) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label">Catatan (opsional untuk setujui, wajib untuk revisi)</label>
                    <textarea name="comments" class="form-textarea w-full text-sm" rows="3" placeholder="Tuliskan catatan persetujuan atau revisi..."></textarea>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" name="action" value="approve" class="btn-primary text-sm flex items-center justify-center gap-2"
                        onclick="return confirm('Setujui dokumen ini?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui Dokumen
                    </button>
                    <button type="submit" name="action" value="reject" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-sm flex items-center justify-center gap-2"
                        onclick="return confirm('Kembalikan dokumen ini untuk direvisi?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Kembalikan (Minta Revisi)
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

