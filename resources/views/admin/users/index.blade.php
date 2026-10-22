@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page_title', 'Manajemen User')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Manajemen User</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header flex flex-col items-start text-left gap-3">
        <div>
            <h2 class="card-title">Daftar Pengguna Sistem</h2>
            <p class="text-xs text-charcoal-muted mt-1">Kelola data user, role, dan hak akses aplikasi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.import') }}" class="btn-outline btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import CSV
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah User
            </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2 max-w-md">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, NIP..." class="form-input pl-9 text-sm py-1.5 h-9" x-data x-init="if(sessionStorage.getItem('search_focus') === 'true') { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); sessionStorage.removeItem('search_focus'); }" @input.debounce.500ms="sessionStorage.setItem('search_focus', 'true'); $event.target.form.submit()">
            </div>
            <button type="submit" class="btn-secondary h-9 px-3">Cari</button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn-outline h-9 px-3 text-red-600 hover:bg-red-50 hover:border-red-200">Reset</a>
            @endif
        </form>
    </div>

    <div class="hidden md:block table-wrapper">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th>User Info</th>
                    <th>NIP & Posisi</th>
                    <th>Bidang</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-primary-700 text-xs font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-charcoal">{{ $user->name }}</p>
                                <p class="text-xs text-charcoal-muted">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm text-charcoal">{{ $user->nip ?? '-' }}</p>
                        <p class="text-xs text-charcoal-muted">{{ $user->position ?? '-' }}</p>
                    </td>
                    <td>
                        @if($user->department)
                            <span class="badge badge-primary">{{ $user->department->code }}</span>
                        @else
                            <span class="text-xs text-charcoal-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-surface-200 text-charcoal font-medium border border-surface-300">
                            {{ ucfirst(str_replace('_', ' ', $user->role_name)) }}
                        </span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-approved">Aktif</span>
                        @else
                            <span class="badge badge-rejected">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-icon btn-secondary" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </a>
                        @if($user->id !== auth()->id() && $user->email !== 'it.infra@peb.co.id')
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-outline text-red-600 hover:bg-red-50 hover:border-red-200" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-charcoal-muted text-sm">
                        Data pengguna tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4 p-4">
        @forelse($users as $user)
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-primary-700 font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-charcoal text-base">{{ $user->name }}</h3>
                        <p class="text-xs text-charcoal-muted">{{ $user->email }}</p>
                    </div>
                </div>
                
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">NIP</span>
                        <span class="font-medium text-charcoal">{{ $user->nip ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Posisi</span>
                        <span class="font-medium text-charcoal">{{ $user->position ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Bidang</span>
                        <span>
                            @if($user->department)
                                <span class="badge badge-primary">{{ $user->department->code }}</span>
                            @else
                                <span class="text-xs text-charcoal-muted">-</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Role</span>
                        <span class="badge bg-surface-200 text-charcoal font-medium border border-surface-300">
                            {{ ucfirst(str_replace('_', ' ', $user->role_name)) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-charcoal-muted">Status</span>
                        <span>
                            @if($user->is_active)
                                <span class="badge badge-approved">Aktif</span>
                            @else
                                <span class="badge badge-rejected">Nonaktif</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-surface-50 px-4 py-3 flex items-center justify-end border-t border-surface-200 gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-outline btn-sm bg-white">Edit</a>
                @if($user->id !== auth()->id() && $user->email !== 'it.infra@peb.co.id')
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-outline btn-sm bg-white text-red-600 hover:bg-red-50 hover:border-red-200">
                        Hapus
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-charcoal-muted text-sm border border-surface-200 rounded-xl bg-surface-50">
            Data pengguna tidak ditemukan.
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="p-4 border-t border-surface-200">
        {{ $users->links('pagination::tailwind') }}
    </div>
    @endif
</div>
@endsection
