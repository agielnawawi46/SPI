@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page_title', 'Audit Trail')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Audit Trail</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="card-title">Log Aktivitas Sistem</h2>
            <p class="text-xs text-charcoal-muted mt-1">Catatan rekaman setiap aktivitas user di dalam sistem.</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-surface-200 bg-surface-50">
        <form action="{{ route('admin.audit-trail') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-charcoal-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user, deskripsi, IP..." class="form-input pl-9 text-sm py-1.5 h-9" x-data x-init="if(sessionStorage.getItem('search_focus') === 'true') { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); sessionStorage.removeItem('search_focus'); }" @input.debounce.500ms="sessionStorage.setItem('search_focus', 'true'); $event.target.form.submit()">
            </div>

            <select name="action" class="form-input text-sm py-1.5 h-9 w-full sm:w-48">
                <option value="">Semua Aksi</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ strtoupper($action) }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="form-input text-sm py-1.5 h-9 w-full sm:w-40">

            <button type="submit" class="btn-secondary h-9 px-4">Filter</button>
            @if(request()->anyFilled(['search', 'action', 'date']))
                <a href="{{ route('admin.audit-trail') }}" class="btn-secondary h-9 px-4 text-red-600 hover:text-red-700 hover:bg-red-50 border-transparent">Reset</a>
            @endif
        </form>
    </div>

    <div class="hidden md:block table-wrapper">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Deskripsi & IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="whitespace-nowrap">
                        <p class="text-sm text-charcoal font-medium">{{ $log->created_at->format('d M Y') }}</p>
                        <p class="text-xs text-charcoal-muted">{{ $log->created_at->format('H:i:s') }}</p>
                    </td>
                    <td>
                        @if($log->user)
                            <p class="text-sm font-semibold text-charcoal">{{ $log->user->name }}</p>
                            <p class="text-xs text-charcoal-muted">{{ $log->user->email }}</p>
                        @else
                            <span class="text-sm text-charcoal-muted italic">System / Deleted User</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $color = match($log->action) {
                                'login' => 'bg-green-100 text-green-700 border-green-200',
                                'logout' => 'bg-gray-100 text-gray-700 border-gray-200',
                                'create' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'update' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'delete' => 'bg-red-100 text-red-700 border-red-200',
                                'approve', 'reject' => 'bg-purple-100 text-purple-700 border-purple-200',
                                default => 'bg-surface-200 text-charcoal border-surface-300'
                            };
                        @endphp
                        <span class="badge border font-medium {{ $color }}">
                            {{ strtoupper($log->action) }}
                        </span>
                    </td>
                    <td>
                        <p class="text-sm text-charcoal">{{ $log->description ?? '-' }}</p>
                        <p class="text-xs text-charcoal-muted font-mono mt-0.5">
                            IP: {{ $log->ip_address ?? '-' }}
                            @if($log->url)
                                • <span class="truncate max-w-xs inline-block align-bottom">{{ parse_url($log->url, PHP_URL_PATH) }}</span>
                            @endif
                        </p>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-8 text-charcoal-muted text-sm">
                        Data audit trail tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4 p-4">
        @forelse($logs as $log)
        @php
            $color = match($log->action) {
                'login' => 'bg-green-100 text-green-700 border-green-200',
                'logout' => 'bg-gray-100 text-gray-700 border-gray-200',
                'create' => 'bg-blue-100 text-blue-700 border-blue-200',
                'update' => 'bg-amber-100 text-amber-700 border-amber-200',
                'delete' => 'bg-red-100 text-red-700 border-red-200',
                'approve', 'reject' => 'bg-purple-100 text-purple-700 border-purple-200',
                default => 'bg-surface-200 text-charcoal border-surface-300'
            };
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-surface-200 overflow-hidden">
            <div class="p-4">
                <div class="flex justify-between items-start gap-4 mb-3">
                    <div class="flex items-center gap-3">
                        @if($log->user)
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-primary-700 text-sm font-bold">{{ strtoupper(substr($log->user->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-charcoal text-sm leading-snug">{{ $log->user->name }}</h3>
                            <p class="text-xs text-charcoal-muted">{{ $log->user->email }}</p>
                        </div>
                        @else
                        <div class="w-10 h-10 rounded-full bg-surface-200 flex items-center justify-center flex-shrink-0">
                            <span class="text-charcoal-muted text-sm font-bold">?</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-charcoal-muted italic text-sm leading-snug">System / Deleted</h3>
                        </div>
                        @endif
                    </div>
                    <span class="flex-shrink-0 badge border font-medium {{ $color }}">
                        {{ strtoupper($log->action) }}
                    </span>
                </div>
                
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted">Waktu</span>
                        <div class="text-right">
                            <span class="font-medium text-charcoal block">{{ $log->created_at->format('d M Y') }}</span>
                            <span class="text-xs text-charcoal-muted">{{ $log->created_at->format('H:i:s') }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col text-sm border-b border-surface-100 pb-2">
                        <span class="text-charcoal-muted mb-1">Deskripsi</span>
                        <span class="font-medium text-charcoal leading-relaxed">{{ $log->description ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-charcoal-muted">Info Jaringan</span>
                        <div class="text-right font-mono text-xs text-charcoal-muted">
                            IP: {{ $log->ip_address ?? '-' }}
                            @if($log->url)
                                <br> <span class="truncate max-w-[200px] inline-block align-bottom" title="{{ $log->url }}">{{ parse_url($log->url, PHP_URL_PATH) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-charcoal-muted text-sm border border-surface-200 rounded-xl bg-surface-50">
            Data audit trail tidak ditemukan.
        </div>
        @endforelse
    </div>

    @if($logs->hasPages())
    <div class="p-4 border-t border-surface-200">
        {{ $logs->links('pagination::tailwind') }}
    </div>
    @endif
</div>
@endsection
