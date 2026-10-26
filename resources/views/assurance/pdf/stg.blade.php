<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.15;
            color: #000;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-logo {
            width: 25%;
        }
        .header-logo img {
            max-height: 80px;
        }
        .header-text {
            width: 75%;
            text-align: right;
            font-size: 8pt;
            color: #002060;
        }
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
            table-layout: fixed; /* enforce column widths */
            word-wrap: break-word;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .content-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .identitas-table {
            width: 100%;
            border: none;
        }
        .identitas-table td {
            border: none;
            padding: 2px 0;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .signature-section {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 40%;
            margin: 0 auto;
            text-align: center;
        }
        .footer-section {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            width: 100%;
            text-align: center;
        }
        .footer-section img {
            max-height: 80px;
        }
        /* Page margins */
        @page { margin: 20px 50px 20px 50px; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; }
        .text-justify { text-align: justify; }
        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>
<body>

    <div class="footer-section">
        <img src="{{ public_path('pln2.jpg') }}" alt="Footer">
    </div>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('pln.jpg') }}" alt="Logo">
            </td>
            <td class="header-text">
                <table align="right" style="text-align: left; border: none; border-collapse: collapse;">
                    @if($document->header_text)
                    <tr><td style="padding: 0; border: none; width: 65px;">No. Formulir</td><td style="padding: 0 4px; border: none;">:</td><td style="padding: 0; border: none;">{{ $document->header_text }}</td></tr>
                    @endif
                    @if($document->tanggal_formulir)
                    <tr><td style="padding: 0; border: none;">Tanggal</td><td style="padding: 0 4px; border: none;">:</td><td style="padding: 0; border: none;">{{ \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') }}</td></tr>
                    @endif
                    <tr><td style="padding: 0; border: none;">Halaman</td><td style="padding: 0 4px; border: none;">:</td><td style="padding: 0; border: none;">1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="title-section">
        <div class="title">{{ $document->judul_dokumen ?? 'SURAT TUGAS AUDIT' }}</div>
        <div class="subtitle">{{ $document->title }}</div>
    </div>

    <div class="text-justify">
        {!! nl2br(e($document->deskripsi_pembuka ?? '-')) !!}
    </div>

    <table class="content-table" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 70%;">Identitas Tim Audit</th>
                <th style="width: 25%;">Jabatan Tim</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @if($document->tim_audit && is_array($document->tim_audit))
                @foreach($document->tim_audit as $tim)
                <tr>
                    <td style="text-align: center;">{{ $counter++ }}.</td>
                    <td>
                        <table class="identitas-table">
                            <tr>
                                <td style="width: 60px;">Nama</td>
                                <td style="width: 10px;">:</td>
                                <td style="text-transform: uppercase;">{{ $tim['nama'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>NIP</td>
                                <td>:</td>
                                <td>{{ $tim['nip'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Jabatan</td>
                                <td>:</td>
                                <td style="text-transform: capitalize;">{{ $tim['jabatan_pegawai'] ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">{{ $tim['jabatan_tim'] ?? '-' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align: center;">-</td>
                    <td colspan="2" style="text-align: center; color: #777; font-style: italic; padding: 10px;">(Tidak ada susunan tim audit yang dicantumkan)</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="content-table" style="margin-top: -1px;">
        <tbody>
            @if(str_contains(strtolower($document->documentType?->slug ?? ''), 'qa'))
            <tr>
                <td style="width: 32%;">Periode QA</td>
                <td style="text-align: center; width: 2%;">:</td>
                <td style="width: 66%;">{{ $document->periode_audit ?? '-' }}</td>
            </tr>
            <tr>
                <td>Lingkup QA</td>
                <td style="text-align: center;">:</td>
                <td class="text-justify">
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
                <td>Tujuan QA</td>
                <td style="text-align: center;">:</td>
                <td class="text-justify">
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
                <td>Waktu Pelaksanaan QA</td>
                <td style="text-align: center;">:</td>
                <td>{{ $document->jadwal_audit ?? '-' }}</td>
            </tr>
            @else
            <tr>
                <td style="font-style: italic; width: 32%;">Audit Universe</td>
                <td style="text-align: center; width: 2%;">:</td>
                <td style="width: 66%;">{{ $document->auditee ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-style: italic;">Scope Audit</td>
                <td style="text-align: center;">:</td>
                <td class="text-justify">
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
                <td>Tujuan Audit</td>
                <td style="text-align: center;">:</td>
                <td class="text-justify">
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
                <td>Periode Audit</td>
                <td style="text-align: center;">:</td>
                <td>{{ $document->periode_audit ?? '-' }}</td>
            </tr>
            <tr>
                <td>Waktu Pelaksanaan Audit</td>
                <td style="text-align: center;">:</td>
                <td>{{ $document->jadwal_audit ?? '-' }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="text-justify">
        {!! nl2br(e($document->deskripsi_penutup ?? '-')) !!}
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div>{{ $document->tgl_stg ? $document->tgl_stg->format('d F Y') : '-' }}</div>
            <div style="font-weight: bold; margin-bottom: {{ $document->qr_code_path ? '6px' : '80px' }};">{{ $document->jabatan_direktur ?? '-' }}</div>
            @if($document->qr_code_path)
            <div style="margin-bottom: 6px;">
                <img src="{{ public_path('storage/' . $document->qr_code_path) }}" alt="QR Code" style="width: 75px; height: 75px;">
            </div>
            @endif
            <div style="font-weight: bold;">{{ $document->nama_direktur ?? '-' }}</div>
        </div>
    </div>

</body>
</html>
