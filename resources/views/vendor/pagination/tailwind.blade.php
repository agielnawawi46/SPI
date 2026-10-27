@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 bg-white border-t border-surface-100">

        {{-- Mobile Controls (Simple Prev/Next) --}}
        <div class="flex items-center justify-between w-full sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-charcoal-muted/50 bg-surface-50 border border-surface-200 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 rounded-lg shadow-2xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Sebelumnya
                </a>
            @endif

            <span class="text-xs font-medium text-charcoal-muted">
                Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 rounded-lg shadow-2xs transition-colors">
                    Berikutnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-charcoal-muted/50 bg-surface-50 border border-surface-200 rounded-lg cursor-not-allowed">
                    Berikutnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- Desktop Information & Numbered Links --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between w-full">
            <div>
                <p class="text-xs text-charcoal-muted">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-charcoal">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-semibold text-charcoal">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-semibold text-charcoal">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div>
                <div class="inline-flex items-center gap-1">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Sebelumnya" class="w-8 h-8 flex items-center justify-center text-charcoal-muted/40 bg-surface-50 border border-surface-200 rounded-lg cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="w-8 h-8 flex items-center justify-center text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-200 rounded-lg shadow-2xs transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-charcoal-muted/50 select-none">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="w-8 h-8 flex items-center justify-center text-xs font-bold text-white bg-primary-500 border border-primary-500 rounded-lg shadow-2xs select-none">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-200 rounded-lg shadow-2xs transition-colors" aria-label="Ke halaman {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="w-8 h-8 flex items-center justify-center text-charcoal bg-white border border-surface-200 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-200 rounded-lg shadow-2xs transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Berikutnya" class="w-8 h-8 flex items-center justify-center text-charcoal-muted/40 bg-surface-50 border border-surface-200 rounded-lg cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif

                </div>
            </div>
        </div>

    </nav>
@endif
