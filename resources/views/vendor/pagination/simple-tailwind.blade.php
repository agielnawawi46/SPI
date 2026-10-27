@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman Sederhana" class="flex items-center justify-between gap-3 px-4 py-3 bg-white border-t border-surface-100">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-charcoal-muted/50 bg-surface-50 border border-surface-200 rounded-lg cursor-not-allowed select-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-200 rounded-lg shadow-2xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Sebelumnya
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-200 rounded-lg shadow-2xs transition-colors">
                Berikutnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-charcoal-muted/50 bg-surface-50 border border-surface-200 rounded-lg cursor-not-allowed select-none">
                Berikutnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </nav>
@endif

