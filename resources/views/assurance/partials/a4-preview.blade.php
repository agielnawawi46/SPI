<!-- A4 Paper Container -->
<div class="bg-white shadow-xl mx-auto p-10 max-w-[210mm] min-h-[297mm] mb-8 text-black text-[11pt] flex flex-col leading-[1.15]" style="font-family: Arial, sans-serif;">
    <div class="flex items-center justify-between mb-4 pb-2">
        <div class="w-1/4">
            <img src="{{ asset('pln.jpg') }}" alt="Header STG" class="max-h-24 object-contain">
        </div>
        <div class="w-3/4 text-right text-[#002060] text-[8pt] leading-snug">
            <table class="inline-block text-left border-none">
                @if($document->header_text)
                <tr><td class="p-0 border-none w-[65px]">No. Formulir</td><td class="p-0 px-1 border-none">:</td><td class="p-0 border-none">{{ $document->header_text }}</td></tr>
                @endif
                @if($document->tanggal_formulir)
                <tr><td class="p-0 border-none">Tanggal</td><td class="p-0 px-1 border-none">:</td><td class="p-0 border-none">{{ \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') }}</td></tr>
                @endif
                <tr><td class="p-0 border-none">Halaman</td><td class="p-0 px-1 border-none">:</td><td class="p-0 border-none">1</td></tr>
            </table>
        </div>
    </div>
    
    <div class="text-center mb-6">
        <h4 class="font-bold text-[12pt] uppercase">{{ $document->judul_dokumen ?? 'SURAT TUGAS AUDIT' }}</h4>
        <p class="font-medium text-[12pt]">{{ $document->title }}</p>
    </div>
    
    <div class="mb-4">
        <p class="whitespace-pre-line text-justify">{{ $document->deskripsi_pembuka ?? '-' }}</p>
    </div>

    <div class="mt-4 mb-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border border-black" style="table-layout: fixed; margin-bottom: -1px;">
                <thead>
                    <tr>
                        <th class="border border-black px-2 py-2 text-center font-semibold bg-gray-50" style="width: 5%;">No.</th>
                        <th class="border border-black px-4 py-2 text-center font-semibold bg-gray-50" style="width: 70%;">Identitas Tim Audit</th>
                        <th class="border border-black px-4 py-2 text-center font-semibold bg-gray-50" style="width: 25%;">Jabatan Tim</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @if($document->tim_audit && is_array($document->tim_audit))
                        @foreach($document->tim_audit as $tim)
                        <tr>
                            <td class="border border-black px-2 py-2 text-center align-top">{{ $counter++ }}.</td>
                            <td class="border border-black px-4 py-2 align-top">
                                <div class="grid grid-cols-[70px_10px_1fr] gap-x-2">
                                    <div>Nama</div><div>:</div><div class="uppercase">{{ $tim['nama'] ?? '-' }}</div>
                                    <div>NIP</div><div>:</div><div>{{ $tim['nip'] ?? '-' }}</div>
                                    <div>Jabatan</div><div>:</div><div class="capitalize">{{ $tim['jabatan_pegawai'] ?? '-' }}</div>
                                </div>
                            </td>
                            <td class="border border-black px-4 py-2 text-center align-middle">{{ $tim['jabatan_tim'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <table class="w-full text-left border-collapse border border-black" style="table-layout: fixed;">
                <tbody>
                    <tr>
                        <td class="border border-black px-4 py-2 align-top italic" style="width: 32%;">Audit Universe</td>
                        <td class="border border-black px-2 py-2 text-center align-top" style="width: 2%;">:</td>
                        <td class="border border-black px-4 py-2 align-top" style="width: 66%;">{{ $document->auditee ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 align-top italic">Scope Audit</td>
                        <td class="border border-black px-2 py-2 text-center align-top">:</td>
                        <td class="border border-black px-4 py-2 align-top text-justify">
                            @foreach(explode("\n", str_replace("\r", "", $document->lingkup_audit ?? '-')) as $line)
                                @php
                                    $trimmedLine = trim($line);
                                    $isList = preg_match('/^(\d+\.|[-•a-zA-Z]\.)\s/', $trimmedLine);
                                @endphp
                                @if($trimmedLine)
                                    <div style="{{ $isList ? 'padding-left: 15px; text-indent: -15px;' : '' }}">{{ $trimmedLine }}</div>
                                @else
                                    <div style="height: 6px;"></div>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 align-top">Tujuan Audit</td>
                        <td class="border border-black px-2 py-2 text-center align-top">:</td>
                        <td class="border border-black px-4 py-2 align-top text-justify">
                            @foreach(explode("\n", str_replace("\r", "", $document->tujuan_audit ?? '-')) as $line)
                                @php
                                    $trimmedLine = trim($line);
                                    $isList = preg_match('/^(\d+\.|[-•a-zA-Z]\.)\s/', $trimmedLine);
                                @endphp
                                @if($trimmedLine)
                                    <div style="{{ $isList ? 'padding-left: 15px; text-indent: -15px;' : '' }}">{{ $trimmedLine }}</div>
                                @else
                                    <div style="height: 6px;"></div>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 align-top">Periode Audit</td>
                        <td class="border border-black px-2 py-2 text-center align-top">:</td>
                        <td class="border border-black px-4 py-2 align-top">{{ $document->periode_audit ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 align-top">Waktu Pelaksanaan Audit</td>
                        <td class="border border-black px-2 py-2 text-center align-top">:</td>
                        <td class="border border-black px-4 py-2 align-top">{{ $document->jadwal_audit ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <p class="whitespace-pre-line text-justify">{{ $document->deskripsi_penutup ?? '-' }}</p>
    </div>

    <div class="flex flex-col items-center mt-12 mb-8">
        <p>{{ $document->tgl_stg ? $document->tgl_stg->format('d F Y') : '-' }}</p>
        <p class="font-bold mb-4">{{ $document->jabatan_direktur ?? '-' }}</p>
        @if($document->qr_code_path)
        <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" class="w-24 h-24 object-contain my-2">
        @else
        <div class="h-20"></div>
        @endif
        <div class="h-4"></div>
        <p class="font-bold">{{ $document->nama_direktur ?? '-' }}</p>
    </div>

    <div class="flex justify-center mt-auto">
        <img src="{{ asset('pln2.jpg') }}" alt="Footer STG" class="max-h-24 object-contain">
    </div>
</div>
