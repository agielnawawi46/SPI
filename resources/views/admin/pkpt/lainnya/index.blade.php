@extends('layouts.app')

@section('title', 'Lainnya (Auditee & Jadwal)')
@section('page_title', 'Lainnya (Auditee & Jadwal)')

@section('breadcrumbs')
    <a href="{{ route('pkpt.index') }}" class="breadcrumb-item">PKPT</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Lainnya</span>
@endsection

@section('content')

<div class="card">
    <div class="card-header flex flex-col items-start text-left gap-3">
        <div>
            <h2 class="card-title">Daftar Auditee & Waktu Pelaksanaan</h2>
            <p class="text-xs text-charcoal-muted mt-1">Kelola rincian objek audit, auditee, dan jadwal pelaksanaan dari seluruh dokumen PKPT.</p>
        </div>
        @can('pkpt.create')
        <div>
            <a href="{{ route('pkpt.lainnya.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Baru
            </a>
        </div>
        @endcan
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('pkpt.lainnya.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <x-live-search module="kegiatan-lainnya" placeholder="Cari objek audit atau PKPT..." />
            
            <button type="submit" class="btn-secondary">Cari</button>
            @if(request()->has('search') && request('search') != '')
                <a href="{{ route('pkpt.lainnya.index') }}" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-16 text-center">No</th>
                    <th>PKPT Terkait</th>
                    <th>Objek Audit</th>
                    <th>Auditee</th>
                    <th>Waktu Pelaksanaan</th>
                    @can('pkpt.edit')
                    <th class="text-right">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td class="text-center text-charcoal-muted">{{ $items->firstItem() + $index }}</td>
                    <td>
                        <p class="font-medium text-charcoal">{{ $item->pkpt->title }}</p>
                        <p class="text-xs text-charcoal-muted">Tahun: {{ $item->pkpt->period_year }}</p>
                    </td>
                    <td>
                        <span class="font-medium text-charcoal">{{ $item->audit_object }}</span>
                    </td>
                    <td>
                        @if($item->auditeeDepartment)
                            <span class="badge badge-primary">{{ $item->auditeeDepartment->code }}</span> - <span class="text-xs">{{ $item->auditeeDepartment->name }}</span>
                        @elseif($item->custom_auditee_name)
                            <span class="badge bg-gray-100 text-gray-800 border-gray-200">Lainnya</span> - <span class="text-xs">{{ $item->custom_auditee_name }}</span>
                        @else
                            <span class="text-charcoal-muted italic">-</span>
                        @endif
                    </td>
                    <td>
                        @if($item->planned_start && $item->planned_end)
                            <span class="text-sm font-medium text-charcoal">{{ $item->planned_start->format('d M Y') }}</span>
                            <span class="text-charcoal-muted text-xs mx-1">s/d</span>
                            <span class="text-sm font-medium text-charcoal">{{ $item->planned_end->format('d M Y') }}</span>
                        @else
                            <span class="text-charcoal-muted italic">Belum dijadwalkan</span>
                        @endif
                    </td>
                    @can('pkpt.edit')
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('pkpt.lainnya.show', $item) }}" class="btn-icon bg-primary-500 text-white hover:bg-primary-400 hover:opacity-80 shadow-sm transition-all" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('pkpt.lainnya.edit', $item) }}" class="btn-icon bg-amber-500 text-white hover:bg-amber-400 hover:opacity-80 shadow-sm transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @can('pkpt.delete')
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-item-{{ $item->id }}')" class="btn-icon bg-red-500 text-white hover:bg-red-400 hover:opacity-80 shadow-sm transition-all" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
                        </div>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="empty-state-title">Belum Ada Data</p>
                            <p class="empty-state-description">Data rincian auditee dan jadwal pelaksanaan PKPT masih kosong atau tidak ditemukan.</p>
                            @can('pkpt.create')
                            <a href="{{ route('pkpt.lainnya.create') }}" class="btn-primary mt-4">Tambah Sekarang</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden space-y-4 p-4">
        @forelse($items as $index => $item)
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-start gap-4 mb-3">
                    <h3 class="font-semibold text-charcoal text-base leading-snug break-words line-clamp-2">
                        <a href="{{ route('pkpt.lainnya.show', $item) }}" class="hover:text-primary-600 transition-colors">
                            {{ $item->audit_object }}
                        </a>
                    </h3>
                </div>
                
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">PKPT</span>
                        <span class="font-medium text-charcoal text-right">{{ $item->pkpt->title }}<br><span class="text-xs text-charcoal-muted">Tahun: {{ $item->pkpt->period_year }}</span></span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Auditee</span>
                        <span class="font-medium text-charcoal text-right">
                            @if($item->auditeeDepartment)
                                {{ $item->auditeeDepartment->code }} - {{ $item->auditeeDepartment->name }}
                            @elseif($item->custom_auditee_name)
                                {{ $item->custom_auditee_name }} (Lainnya)
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-charcoal-muted">Waktu</span>
                        <span class="font-medium text-charcoal text-right">
                            @if($item->planned_start && $item->planned_end)
                                {{ $item->planned_start->format('d/m/Y') }} - {{ $item->planned_end->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            @can('pkpt.edit')
            <div class="bg-surface-50 px-4 py-3 border-t border-surface-100 flex justify-end gap-2">
                <a href="{{ route('pkpt.lainnya.show', $item) }}" class="btn-secondary btn-sm text-primary-600 flex-1 justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Detail
                </a>
                <a href="{{ route('pkpt.lainnya.edit', $item) }}" class="btn-secondary btn-sm text-primary-600 flex-1 justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @can('pkpt.delete')
                <button type="button" x-data @click="$dispatch('open-modal', 'delete-item-{{ $item->id }}')" class="btn-secondary btn-sm text-red-600 flex-1 justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
                @endcan
            </div>
            @endcan
        </div>
        @empty
        <div class="empty-state bg-white rounded-xl shadow-sm border border-surface-200 p-8">
            <div class="empty-state-icon">
                <svg class="w-8 h-8 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="empty-state-title">Belum Ada Data</p>
        </div>
        @endforelse
    </div>
    
    @if($items->hasPages())
    <div class="pagination-wrapper">
        {{ $items->links() }}
    </div>
    @endif
</div>

{{-- Delete Modals --}}
@foreach($items as $item)
    @can('pkpt.delete')
    <div x-data="{ show: false }" 
         x-show="show"
         @open-modal.window="if ($event.detail === 'delete-item-{{ $item->id }}') show = true"
         @close-modal.window="show = false"
         @keydown.escape.window="show = false"
         class="modal-overlay"
         style="display: none;">
        
        <div class="modal-box max-w-md" @click.stop x-transition>
            <div class="modal-header">
                <h3 class="card-title text-red-600">Konfirmasi Hapus</h3>
                <button @click="show = false" class="text-charcoal-muted hover:text-charcoal"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-charcoal">Apakah Anda yakin ingin menghapus data <strong>{{ $item->audit_object }}</strong>? Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button @click="show = false" class="btn-secondary">Batal</button>
                <form action="{{ route('pkpt.lainnya.destroy', $item) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endforeach

@endsection
