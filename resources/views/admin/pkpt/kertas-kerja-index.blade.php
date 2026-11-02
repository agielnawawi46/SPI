@extends('layouts.app')

@section('title', 'Kertas Kerja PKPT')
@section('page_title', 'Kertas Kerja PKPT')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-link">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Kertas Kerja</span>
@endsection

@section('content')
<div class="card" x-data="{ showModal: false, selectedPkpt: '' }">
    <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="card-title">Daftar Kertas Kerja PKPT</h2>
            <p class="text-xs text-charcoal-muted mt-1">Kelola dan unggah lampiran Kertas Kerja (RJP, RKAP, dll) untuk PKPT yang telah ditetapkan.</p>
        </div>
        @can('pkpt.create')
        <div>
            <button @click="showModal = true" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kertas Kerja
            </button>
        </div>
        @endcan
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('pkpt.kertas-kerja.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <x-live-search module="kertas-kerja-pkpt" placeholder="Cari judul atau tahun PKPT..." />
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('pkpt.kertas-kerja.index') }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-16 text-center">No</th>
                    <th>Judul PKPT</th>
                    <th>Tahun</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $index => $doc)
                <tr>
                    <td class="text-center text-charcoal-muted">{{ $documents->firstItem() + $index }}</td>
                    <td class="font-medium text-charcoal">{{ $doc->pkpt->title ?? '-' }}</td>
                    <td>{{ $doc->pkpt->period_year ?? '-' }}</td>
                    <td>{{ $doc->creator->name ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $doc->status_color }}">{{ $doc->status_label }}</span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('pkpt.kertas-kerja', $doc->pkpt_id) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat/Kelola Kertas Kerja">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if(($doc->attachments_count ?? 0) > 0)
                            <a href="{{ route('pkpt.kertas-kerja.download-zip', $doc->pkpt_id) }}" class="btn-icon relative bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all" title="Unduh Semua Berkas ({{ $doc->attachments_count }} Berkas .ZIP)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span class="absolute -top-1 -right-1 px-1 min-w-[15px] h-[15px] text-[9.5px] font-bold bg-amber-400 text-charcoal rounded-full flex items-center justify-center border border-white leading-none shadow-2xs">ZIP</span>
                            </a>
                            @else
                            <button type="button" disabled class="btn-icon bg-surface-200 text-charcoal-muted cursor-not-allowed shadow-sm transition-all disabled:opacity-100" title="Unduh Berkas (Belum ada berkas)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            @endif
                            @if($doc->status !== 'draft' && auth()->user()->hasRole('admin'))
                            <form action="{{ route('pkpt.reopen-kertas-kerja', $doc->pkpt_id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci (reopen) Kertas Kerja PKPT ini? Dokumen akan kembali ke status Draft.')">
                                @csrf
                                <button type="submit" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all cursor-pointer" title="Buka Kunci Closed (Reopen ke Draft)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                </button>
                            </form>
                            @endif
                            
                            @can('pkpt.delete')
                            <form action="{{ route('pkpt.destroy-kertas-kerja', $doc->pkpt_id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data Kertas Kerja PKPT {{ addslashes($doc->pkpt->title ?? '') }}? Data yang dihapus tidak dapat dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all cursor-pointer" title="Hapus Kertas Kerja">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h3 class="empty-state-title">Belum ada Kertas Kerja PKPT</h3>
                            <p class="empty-state-description">Silakan klik Tambah Kertas Kerja untuk mulai melampirkan berkas pada PKPT Anda.</p>
                            @can('pkpt.create')
                            <button @click="showModal = true" class="btn-primary mt-2">
                                Tambah Kertas Kerja
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden p-4 pt-0 space-y-4">
        @forelse($documents as $doc)
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-start gap-4 mb-3">
                    <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                        <a href="{{ route('pkpt.kertas-kerja', $doc->pkpt_id) }}" class="hover:text-primary-600 transition-colors">
                            {{ $doc->pkpt->title ?? '-' }}
                        </a>
                    </h3>
                    <span class="flex-shrink-0 badge {{ $doc->status_color }}">
                        {{ $doc->status_label }}
                    </span>
                </div>
                
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Tahun</span>
                        <span class="font-medium text-charcoal">{{ $doc->pkpt->period_year ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-charcoal-muted">Dibuat Oleh</span>
                        <span class="font-medium text-charcoal">{{ $doc->creator->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-surface-50 px-4 py-3 flex items-center justify-end gap-3 border-t border-surface-200">
                <a href="{{ route('pkpt.kertas-kerja', $doc->pkpt_id) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat/Kelola Kertas Kerja">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                @if(($doc->attachments_count ?? 0) > 0)
                <a href="{{ route('pkpt.kertas-kerja.download-zip', $doc->pkpt_id) }}" class="btn-icon relative bg-emerald-600 text-white hover:bg-emerald-500 hover:opacity-90 shadow-sm transition-all" title="Unduh Semua Berkas ({{ $doc->attachments_count }} Berkas .ZIP)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="absolute -top-1 -right-1 px-1 min-w-[15px] h-[15px] text-[9.5px] font-bold bg-amber-400 text-charcoal rounded-full flex items-center justify-center border border-white leading-none shadow-2xs">ZIP</span>
                </a>
                @else
                <button type="button" disabled class="btn-icon bg-surface-200 text-charcoal-muted cursor-not-allowed shadow-sm transition-all disabled:opacity-100" title="Unduh Berkas (Belum ada berkas)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </button>
                @endif
                @if($doc->status !== 'draft' && auth()->user()->hasRole('admin'))
                <form action="{{ route('pkpt.reopen-kertas-kerja', $doc->pkpt_id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin membuka kunci (reopen) Kertas Kerja PKPT ini? Dokumen akan kembali ke status Draft.')">
                    @csrf
                    <button type="submit" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all cursor-pointer" title="Buka Kunci Closed (Reopen ke Draft)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </button>
                </form>
                @endif
                
                @can('pkpt.delete')
                <form action="{{ route('pkpt.destroy-kertas-kerja', $doc->pkpt_id) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data Kertas Kerja PKPT {{ addslashes($doc->pkpt->title ?? '') }}? Data yang dihapus tidak dapat dikembalikan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all cursor-pointer" title="Hapus Kertas Kerja">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                @endcan
            </div>
        </div>
        @empty
        <div class="empty-state border border-surface-200 rounded-xl mt-4 mx-4 mb-4">
            <div class="empty-state-icon">
                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="empty-state-title">Belum ada Kertas Kerja PKPT</h3>
            <p class="empty-state-description">Silakan klik Tambah Kertas Kerja untuk mulai melampirkan berkas pada PKPT Anda.</p>
            @can('pkpt.create')
            <button @click="showModal = true" class="btn-primary mt-2">
                Tambah Kertas Kerja
            </button>
            @endcan
        </div>
        @endforelse
    </div>

    @if($documents->hasPages())
    <div class="pagination-wrapper">
        {{ $documents->links() }}
    </div>
    @endif

    {{-- Modal Tambah Kertas Kerja --}}
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity bg-charcoal/60 backdrop-blur-sm" @click="showModal = false"></div>

            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-charcoal">Tambah Kertas Kerja PKPT</h3>
                    <button @click="showModal = false" class="text-charcoal-muted hover:text-charcoal transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mb-5">
                    <label class="form-label">Pilih Dokumen PKPT</label>
                    <div class="relative">
                        <select x-model="selectedPkpt" class="form-input appearance-none bg-white pl-4 pr-10 py-2.5">
                            <option value="" disabled selected>-- Pilih PKPT --</option>
                            @foreach($availablePkpts as $pkpt)
                                <option value="{{ $pkpt->id }}">Tahun {{ $pkpt->period_year }} &mdash; {{ $pkpt->title }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-charcoal-muted">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @if($availablePkpts->isEmpty())
                        <p class="text-xs text-amber-600 mt-2">Semua PKPT yang ada saat ini sudah dibuatkan Kertas Kerjanya.</p>
                    @else
                        <p class="text-xs text-charcoal-muted mt-2">Hanya menampilkan PKPT yang belum dibuatkan Kertas Kerjanya.</p>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-surface-200">
                    <button type="button" @click="showModal = false" class="btn-secondary px-5 py-2">Batal</button>
                    <button type="button" 
                            @click="if(selectedPkpt) window.location.href = '/pkpt/' + selectedPkpt + '/kertas-kerja'" 
                            class="btn-primary px-5 py-2"
                            :disabled="!selectedPkpt"
                            :class="{ 'opacity-50 cursor-not-allowed': !selectedPkpt }">
                        Tambahkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
