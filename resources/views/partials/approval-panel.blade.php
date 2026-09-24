{{-- Reusable Approval Panel Partial --}}
{{-- Usage: @include('partials.approval-panel', ['document' => $document, 'submitRoute' => route('...')]) --}}

@php $pendingApproval = $document->approvals()->whereNull('action')->first(); @endphp

{{-- Submit for Review --}}
@if(in_array($document->status, ['draft', 'revised']) && ($document->created_by === Auth::id() || Auth::user()->hasRole('admin')))
    <div class="border-t border-surface-200 pt-4 mt-4 flex justify-end">
        <form action="{{ $submitRoute }}" method="POST" onsubmit="return confirm('Dokumen ini akan dikirim untuk direview oleh KSPI. Lanjutkan?')">
            @csrf
            <button type="submit" class="btn-success">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Kirim untuk Direview (KSPI)
            </button>
        </form>
    </div>
@endif

{{-- Approval Action Panel (for KSPI / Direktur) --}}
@if($pendingApproval && Auth::user()->hasRole($pendingApproval->approval_role))
    <div class="mt-6 border border-amber-200 bg-amber-50 rounded-lg p-6">
        <h3 class="text-base font-semibold text-amber-800 mb-1">Tindakan Persetujuan — {{ strtoupper($pendingApproval->approval_role) }}</h3>
        <p class="text-sm text-amber-700 mb-4">Periksa isi dokumen. Anda dapat menyetujui atau mengembalikan untuk direvisi.</p>
        <form action="{{ route('approvals.action', $pendingApproval) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="form-label text-amber-800">Catatan (opsional untuk approve, wajib untuk revisi)</label>
                <textarea name="comments" class="form-textarea bg-white" rows="3" placeholder="Tuliskan catatan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" name="action" value="approve" class="btn-success">✓ Setujui</button>
                <button type="submit" name="action" value="reject" class="btn-danger">↩ Kembalikan (Revisi)</button>
            </div>
        </form>
    </div>
@endif

{{-- Approval Timeline History --}}
@if($document->approvals->count() > 0)
    <div class="mt-6 border-t border-surface-200 pt-6">
        <h3 class="text-sm font-semibold text-charcoal mb-4">Riwayat Persetujuan</h3>
        <div class="approval-timeline">
            @foreach($document->approvals as $ap)
                <div class="approval-step">
                    <div class="approval-step-icon {{ $ap->isApproved() ? 'done' : ($ap->isRejected() ? 'rejected' : 'pending') }}">
                        @if($ap->isApproved())
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($ap->isRejected())
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div class="approval-step-content border-b border-surface-100 last:border-0 pb-4">
                        <p class="approval-step-title">
                            {{ strtoupper($ap->approval_role) }}
                            @if($ap->isApproved()) <span class="text-green-600">(Disetujui)</span>
                            @elseif($ap->isRejected()) <span class="text-red-600">(Dikembalikan)</span>
                            @else <span class="text-amber-500">(Menunggu)</span>
                            @endif
                        </p>
                        @if($ap->approver)
                            <p class="approval-step-sub">Oleh: {{ $ap->approver->name }} — {{ $ap->acted_at?->format('d M Y, H:i') }}</p>
                        @endif
                        @if($ap->comments)
                            <div class="mt-2 text-sm bg-surface-50 p-3 rounded border border-surface-200">
                                <strong>Catatan:</strong> {{ $ap->comments }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
