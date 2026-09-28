@extends('layouts.app')
@section('title', $document->title)
@section('page_title', 'Detail Laporan Kinerja')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">{{ $document->title }}</h2>
            <p class="text-xs text-charcoal-muted mt-1">
                @if($document->report_month)
                    Periode: {{ \Carbon\Carbon::create(null, $document->report_month)->format('F') }} {{ $document->report_year }} —
                @endif
                Dibuat oleh: {{ $document->creator?->name }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="badge {{ $document->status_color }}">{{ $document->status_label }}</span>
            @if($document->canBeEditedBy(Auth::user()))
                <a href="{{ route('performance-reports.edit', $document) }}" class="btn-primary btn-sm">Tambah Lampiran</a>
            @endif
            <a href="{{ route('performance-reports.index') }}" class="btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        @if(!empty($document->content))
        <div class="mb-6 p-4 bg-surface-50 rounded-lg border border-surface-200">
            <h3 class="text-sm font-semibold mb-2">Deskripsi</h3>
            <p class="text-sm text-charcoal whitespace-pre-wrap">{{ $document->content }}</p>
        </div>
        @endif
        @if($document->attachments->count() > 0)
        <div class="mt-6">
            <h3 class="text-sm font-semibold mb-3">Lampiran</h3>
            <ul class="space-y-2">
                @foreach($document->attachments as $att)
                    <li class="flex items-center justify-between p-3 bg-surface-50 rounded-lg border border-surface-200">
                          <div class="flex-1 min-w-0">
                              <p class="text-sm font-medium">{{ $att->file_name }}</p>
                              <p class="text-xs text-charcoal-muted">{{ number_format($att->file_size/1024, 2) }} KB</p>
                          </div>
                          @if($att->pdf_path)
                              <a href="{{ asset('storage/' . $att->pdf_path) }}" target="_blank" class="btn-primary btn-sm flex items-center gap-1.5">
                                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                  Preview PDF
                              </a>
                          @endif
                          <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="btn-outline btn-sm flex items-center gap-1.5">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                              Unduh
                          </a>
                      </li>
                  @endforeach
            </ul>
        </div>
        @endif

        @include('partials.approval-panel', [
            'document'    => $document,
            'submitRoute' => route('approvals.submit', ['type' => 'document', 'id' => $document->id])
        ])
    </div>
</div>
@endsection
