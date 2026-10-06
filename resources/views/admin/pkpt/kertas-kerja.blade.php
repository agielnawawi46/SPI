@extends('layouts.app')

@section('title', 'Kertas Kerja PKPT')
@section('page_title', 'Kertas Kerja PKPT')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Kertas Kerja PKPT</span>
@endsection

@section('content')
<div class="space-y-6">

    <div class="card">
        <div class="card-header bg-primary-50 border-b border-primary-100 flex justify-between items-center">
            <div>
                <h2 class="card-title text-primary-800">Kertas Kerja - {{ $pkpt->title }}</h2>
                <p class="text-sm text-primary-600 mt-1">Tahun Periode: {{ $pkpt->period_year }}</p>
            </div>
            <a href="{{ route('pkpt.kertas-kerja.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
        <div class="card-body">
            <p class="text-charcoal-muted text-sm mb-6">
                Silakan unggah dokumen pendukung Kertas Kerja PKPT pada form yang tersedia di bawah ini. Dokumen yang diunggah akan otomatis menggantikan dokumen sebelumnya pada kategori yang sama.
            </p>


                @php
                    $categories = [
                        'RJP' => 'Rencana Jangka Panjang',
                        'RKAP' => 'Rencana Kerja & Anggaran Perusahaan',
                        'Profil Risiko' => 'Dokumen Profil Risiko',
                        'Arahan PS' => 'Arahan Pemegang Saham',
                        'Arahan Dekom' => 'Arahan Dewan Komisaris',
                        'Arahan Direktur' => 'Arahan Direktur'
                    ];
                    
                    // Group uploaded files by category
                    $uploadedFiles = [];
                    if($document && $document->attachments) {
                        foreach($document->attachments as $att) {
                            $uploadedFiles[$att->description] = $att;
                        }
                    }
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($categories as $key => $label)
                        <div class="p-4 border {{ isset($uploadedFiles[$key]) ? 'border-green-200 bg-green-50' : 'border-surface-200 bg-white' }} rounded-lg shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-semibold text-charcoal">{{ $key }}</h4>
                                        <p class="text-xs text-charcoal-muted">{{ $label }}</p>
                                    </div>
                                    @if(isset($uploadedFiles[$key]))
                                        <span class="badge bg-green-100 text-green-700 border-green-200">Terunggah</span>
                                    @endif
                                </div>
                                
                                @if(isset($uploadedFiles[$key]))
                                    <div class="mb-4 p-3 bg-white border border-green-100 rounded-md flex items-center justify-between">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="text-sm font-medium text-charcoal truncate" title="{{ $uploadedFiles[$key]->file_name }}">{{ $uploadedFiles[$key]->file_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 flex-shrink-0">
                                            <a href="{{ Storage::url($uploadedFiles[$key]->pdf_path ?: $uploadedFiles[$key]->file_path) }}" target="_blank" title="Lihat" class="text-primary-600 hover:text-primary-800 flex items-center justify-center bg-primary-50 hover:bg-primary-100 rounded-md p-1.5 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ Storage::url($uploadedFiles[$key]->file_path) }}" download target="_blank" title="Unduh" class="text-primary-600 hover:text-primary-800 flex items-center justify-center bg-primary-50 hover:bg-primary-100 rounded-md p-1.5 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            @if($document && $document->status === 'draft')
                                            <form action="{{ route('pkpt.destroy-kertas-kerja-attachment', [$pkpt, $uploadedFiles[$key]->id]) }}" method="POST" class="inline-block m-0 p-0 leading-none" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus" class="text-red-600 hover:text-red-800 flex items-center justify-center bg-red-50 hover:bg-red-100 rounded-md p-1.5 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($document && $document->status === 'draft')
                                <form action="{{ route('pkpt.store-kertas-kerja', $pkpt) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <label class="text-xs font-medium text-charcoal block mb-1">
                                        {{ isset($uploadedFiles[$key]) ? 'Ganti File' : 'Unggah File Baru' }}
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="file" name="attachments[{{ $key }}]" class="form-input flex-1 text-sm bg-white h-[38px] cursor-pointer" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                                        <button type="submit" class="btn-primary text-xs py-2 px-4 h-[38px] flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                            Simpan
                                        </button>
                                    </div>
                                    <p class="text-[10.5px] text-charcoal-muted mt-1.5">Format: PDF, DOC, DOCX, XLS, XLSX. Maks: 300MB.</p>
                                </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($document && $document->status === 'draft')
                    <div class="mt-8 flex justify-end pt-6 border-t border-surface-200">
                        <form action="{{ route('approvals.submit', ['type' => 'document', 'id' => $document->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin sudah selesai mengunggah semua file Kertas Kerja? Setelah klik selesai, dokumen akan dikunci (Closed).')">
                            @csrf
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Selesai & Tutup (Closed)
                            </button>
                        </form>
                    </div>
                @elseif($document && $document->status !== 'draft')
                    <div class="mt-8 pt-6 border-t border-surface-200">
                        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Kertas Kerja PKPT ini telah selesai dan ditutup.
                        </div>
                    </div>
                @endif
        </div>
    </div>
</div>

@endsection
