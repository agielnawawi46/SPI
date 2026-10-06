@props([
    'module' => 'all',
    'placeholder' => 'Cari dokumen...',
])

<div class="relative w-full max-w-sm z-50" x-data="liveSearch('{{ $module }}')">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input 
            type="text"
            name="search"
            x-model="query" 
            x-init="query = '{{ addslashes(request('search')) }}'"
            @input.debounce.350ms="search" 
            @focus="show = true" 
            @click.away="show = false"
            class="form-input pl-10 w-full rounded-lg border border-surface-200 bg-surface-50 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all" 
            placeholder="{{ $placeholder }}"
            autocomplete="off"
        >
        <span x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2" style="display: none;">
            <svg class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </span>
        <button x-show="query.length > 0 && !loading" @click="clear" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-charcoal-muted hover:text-charcoal transition-colors" style="display: none;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Dropdown Results --}}
    <div x-show="show && query.length >= 2" 
         x-transition
         class="absolute z-[60] w-full md:w-[150%] max-w-lg mt-1 bg-white border border-surface-200 rounded-xl shadow-card-lg overflow-hidden flex flex-col"
         style="display: none; max-height: 400px; left: 0;">
        
        <div class="overflow-y-auto p-0 m-0">
            {{-- Empty State --}}
            <div x-show="!loading && results.length === 0" class="p-6 text-center">
                <svg class="w-8 h-8 text-surface-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium text-charcoal">Tidak ditemukan</p>
                <p class="text-xs text-charcoal-muted mt-1">Tidak ada dokumen cocok dengan "<span x-text="query" class="font-semibold text-charcoal"></span>"</p>
            </div>

            {{-- Results List --}}
            <template x-for="(doc, i) in results" :key="doc.id || i">
                <a :href="doc.url || '#'" class="flex items-start gap-3 p-3 border-b border-surface-100 hover:bg-surface-50 transition-colors last:border-b-0">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-[0.7rem] font-bold text-primary-600 tracking-wide" x-html="highlight(doc.no, query)"></span>
                            <span class="text-[0.62rem] font-semibold px-1.5 py-0.5 rounded-full bg-surface-200 text-charcoal-muted" x-text="doc.module_label"></span>
                        </div>
                        <div class="text-[0.82rem] font-medium text-charcoal truncate mb-1" x-html="highlight(doc.nama, query)"></div>
                        <div class="flex flex-wrap items-center gap-1.5 text-[0.7rem] text-charcoal-muted">
                            <span x-text="doc.type"></span>
                            <span>&middot;</span>
                            <span x-text="doc.tanggal"></span>
                            <template x-if="doc.status_label">
                                <div class="flex items-center gap-1.5">
                                    <span>&middot;</span>
                                    <span :class="doc.status_color" x-text="doc.status_label" class="!text-[0.65rem] !px-1.5 !py-0.5 !rounded-md"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </a>
            </template>
        </div>
        
        <div x-show="!loading && results.length > 0" class="px-4 py-2 bg-surface-50 border-t border-surface-200 flex justify-between items-center text-xs text-charcoal-muted">
            <span>Ditemukan <strong class="text-charcoal" x-text="total"></strong> dokumen</span>
        </div>
    </div>
</div>

@push('scripts')
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('liveSearch', (module = 'all') => ({
        query: '',
        results: [],
        total: 0,
        loading: false,
        show: false,
        module: module,
        abortCtrl: null,

        search() {
            if (this.query.length < 2) {
                this.results = [];
                this.total = 0;
                return;
            }

            this.loading = true;
            this.show = true;
            if (this.abortCtrl) this.abortCtrl.abort();
            this.abortCtrl = new AbortController();

            const url = '{{ route("dashboard.search") }}?q=' + encodeURIComponent(this.query) + '&module=' + this.module;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: this.abortCtrl.signal
            })
            .then(res => res.json())
            .then(data => {
                this.results = data.results;
                this.total = data.total;
                this.loading = false;
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    this.loading = false;
                    console.error('Search error:', err);
                }
            });
        },

        clear() {
            this.query = '';
            this.results = [];
            this.total = 0;
            this.show = false;
        },

        highlight(text, q) {
            if (!text || !q) return this.escape(text);
            const safe = this.escape(text);
            const safeQ = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return safe.replace(new RegExp(safeQ, 'gi'), m => `<mark class="bg-yellow-200 rounded px-0.5 text-inherit">${m}</mark>`);
        },

        escape(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    }));
});
</script>
@endonce
@endpush
