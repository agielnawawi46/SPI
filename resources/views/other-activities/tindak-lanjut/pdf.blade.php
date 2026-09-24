<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }}</title>
    <style>
        @page { size: landscape; margin: 20px 30px; }
        body { font-family: Arial, sans-serif; font-size: 8pt; color: #000; }
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { vertical-align: top; }
        .header-logo { width: 25%; }
        .header-logo img { max-height: 80px; }
        .header-text { width: 75%; text-align: right; font-size: 8pt; }
        .title-section { text-align: center; margin-bottom: 10px; font-weight: bold; font-size: 10pt; line-height: 1.3; clear: both;}
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; word-wrap: break-word; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 4px; vertical-align: top; text-align: center; }
        .content-table th { background-color: #d9d9d9; font-weight: bold; }
        .bg-yellow { background-color: #ffc000; height: 15px; }
        .bg-blue { background-color: #b4c6e7; }
        .sub-header { text-align: left; font-weight: bold; padding: 5px 0; font-size: 9pt; white-space: pre-wrap;}
        
        .signature-table { width: 100%; margin-top: 30px; text-align: center; font-size: 9pt; }
        .signature-table td { width: 50%; vertical-align: bottom; }
        .qr-img { width: 80px; height: 80px; margin: 10px auto; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('pln.jpg') }}" alt="Logo">
            </td>
            <td class="header-text">
                <table style="width: auto; float: right; text-align: left;">
                    <tr>
                        <td>No. Formulir</td><td>:</td><td>{{ $document->nomor_formulir ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td><td>:</td><td>{{ $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Halaman</td><td>:</td><td>1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="title-section">
        <div style="text-transform: uppercase;">{{ $document->header_text ?? "PT PELAYANAN ENERGI BATAM" }}</div>
        <div style="text-transform: uppercase;">{{ $document->judul_dokumen ?? "MONITORING DAN EVALUASI TINDAK LANJUT" }}</div>
        @if($document->periode_audit)
            <div style="text-transform: uppercase;">BERLAKU SAMPAI DENGAN: {{ \Carbon\Carbon::parse($document->periode_audit)->translatedFormat('d F Y') }}</div>
        @endif
        <div style="margin-top: 5px;">Nomor : {{ preg_replace('/^Nomor\s*:\s*/i', '', $document->title) }}</div>
    </div>

    @if($document->deskripsi_pembuka)
    <div class="sub-header">
        {{ $document->deskripsi_pembuka }}
    </div>
    @endif

    <table class="content-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No</th>
                <th rowspan="2" style="width: 11%;">LHA</th>
                <th rowspan="2" style="width: 11%;">Sebab</th>
                <th rowspan="2" style="width: 11%;">Akibat</th>
                <th rowspan="2" style="width: 11%;">Rekomendasi</th>
                <th rowspan="2" style="width: 12%;">PIC TL & Email</th>
                <th rowspan="2" style="width: 10%;">Evidence TL</th>
                <th colspan="3" style="width: 17%;">Status</th>
                <th rowspan="2" style="width: 9%;">Evaluasi TL</th>
                <th rowspan="2" style="width: 9%;">Tanggal Jatuh Tempo</th>
            </tr>
            <tr>
                <th style="width: 5%; padding: 4px;">Closed</th>
                <th style="width: 7%; padding: 4px;">On<br>Progress</th>
                <th style="width: 5%; padding: 4px;">TDD</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = json_decode($document->content, true) ?? [];
                $countClosed = 0;
                $countOnProgress = 0;
                $countTdd = 0;
            @endphp
            @forelse($items as $groupIndex => $group)
                @foreach($group['rows'] as $rowIndex => $item)
                    @php
                        if(!empty(trim($item['status_closed'] ?? ''))) $countClosed++;
                        if(!empty(trim($item['status_on_progress'] ?? ''))) $countOnProgress++;
                        if(!empty(trim($item['status_tdd'] ?? ''))) $countTdd++;
                    @endphp
                    <tr>
                        <td>{{ $rowIndex === 0 ? ($groupIndex + 1) : '' }}</td>
                        <td style="text-align: left; white-space: pre-wrap;">{{ $item['lha'] ?? '' }}</td>
                        <td style="text-align: left; white-space: pre-wrap;">{{ $item['sebab'] ?? '' }}</td>
                        <td style="text-align: left; white-space: pre-wrap;">{{ $item['akibat'] ?? '' }}</td>
                        <td style="text-align: left; white-space: pre-wrap;">{{ $item['rekomendasi'] ?? '' }}</td>
                        <td style="line-height: 1.2;">
                            <div style="font-weight: bold;">{{ $item['pic_tl'] ?? '' }}</div>
                            <div>{{ $item['pic_email'] ?? '' }}</div>
                        </td>
                        <td style="text-align: left;">{{ $item['evidence_tl'] ?? '' }}</td>
                        <td>{{ $item['status_closed'] ?? '' }}</td>
                        <td>{{ $item['status_on_progress'] ?? '' }}</td>
                        <td>{{ $item['status_tdd'] ?? '' }}</td>
                        <td style="text-align: left; white-space: pre-wrap;">{{ $item['evaluasi_tl'] ?? '' }}</td>
                        <td>{{ !empty($item['jatuh_tempo']) ? \Carbon\Carbon::parse($item['jatuh_tempo'])->format('d M Y') : '' }}</td>
                    </tr>
                @endforeach
                <tr>
                    @for ($i = 0; $i < 12; $i++)
                        <td class="bg-yellow"></td>
                    @endfor
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="padding: 20px;">Belum ada data tindak lanjut.</td>
                </tr>
            @endforelse
            <tr>
                <td class="bg-blue"></td>
                <td class="bg-blue" style="font-weight: bold; text-align: left; padding-left: 10px;">JUMLAH</td>
                @for ($i = 0; $i < 5; $i++)
                    <td class="bg-blue"></td>
                @endfor
                <td class="bg-blue" style="font-weight: bold;">{{ $countClosed ?: '' }}</td>
                <td class="bg-blue" style="font-weight: bold;">{{ $countOnProgress ?: '' }}</td>
                <td class="bg-blue" style="font-weight: bold;">{{ $countTdd ?: '' }}</td>
                <td class="bg-blue"></td>
                <td class="bg-blue"></td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <span style="font-weight: bold; text-transform: uppercase;">{{ $document->bidang_tugas ?? ($document->creator->department->name ?? 'AUDITOR') }}</span><br><br>
                @if($document->qr_code_path_auditor)
                    <img src="{{ public_path('storage/' . $document->qr_code_path_auditor) }}" class="qr-img" style="width: 80px; height: 80px;">
                @else
                    <br><br><br><br>
                @endif
                <br>
                <span style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">{{ $document->disusun_oleh ?? ($document->creator->name ?? '-') }}</span>
            </td>
            <td style="width: 50%; text-align: center;">
                <span style="font-weight: bold; text-transform: uppercase;">{{ $document->nama_jabatan ?? 'KEPALA SPI' }}</span><br><br>
                @if($document->status === 'approved' && $document->qr_code_path)
                    <img src="{{ public_path('storage/' . $document->qr_code_path) }}" class="qr-img" style="width: 80px; height: 80px;">
                @else
                    <br><br><br><br>
                @endif
                <br>
                <span style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">{{ $document->nama_kspi ?? (\App\Models\User::role('kspi')->first()?->name ?? 'KSPI') }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
