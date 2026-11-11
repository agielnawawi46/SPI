<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.25;
            color: #000;
        }
        /* Page margins reserved for repeating header and footer */
        @page {
            margin-top: 78pt;
            margin-bottom: 75pt;
            margin-left: 45pt;
            margin-right: 45pt;
        }
        header {
            position: fixed;
            top: -66pt;
            left: 0pt;
            right: 0pt;
            width: 100%;
            height: 55pt;
        }
        footer {
            position: fixed;
            bottom: -65pt;
            left: 0pt;
            right: 0pt;
            width: 100%;
            height: 60pt;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-logo {
            width: 25%;
        }
        .header-logo img {
            max-height: 48pt;
            width: auto;
        }
        .header-text {
            width: 75%;
            text-align: right;
            font-size: 8pt;
            color: #002060;
        }
        .footer-section {
            width: 100%;
        }
        .footer-section img {
            width: 100%;
            height: auto;
            display: block;
        }
        .pagenum:before {
            content: counter(page);
        }
        .title-section {
            text-align: center;
            margin-bottom: 12pt;
            page-break-inside: avoid;
        }
        .title {
            font-size: 11pt;
            font-weight: normal;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 11pt;
            font-weight: normal;
            margin: 0;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .content-table thead {
            display: table-header-group;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
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
            padding: 1px 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .signature-section {
            width: 100%;
            margin-top: 20pt;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            margin: 0 auto;
            text-align: center;
        }
        .text-justify { text-align: justify; }
    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <img src="{{ public_path('pln.jpg') }}" alt="Logo">
                </td>
                <td class="header-text">
                    <table align="right" style="text-align: left; border: none; border-collapse: collapse;">
                        @if($document->header_text || $document->nomor_formulir)
                        <tr><td style="padding: 0; border: none; width: 65px;">No. Formulir</td><td style="padding: 0 4px; border: none;">:</td><td style="padding: 0; border: none;">{{ $document->header_text ?? $document->nomor_formulir }}</td></tr>
                        @endif
                        @if($document->tanggal_formulir)
                        <tr><td style="padding: 0; border: none;">Tanggal</td><td style="padding: 0 4px; border: none;">:</td><td style="padding: 0; border: none;">{{ \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') }}</td></tr>
                        @endif
                        <tr><td style="padding: 0; border: none;">Halaman</td><td style="padding: 0 4px; border: none;">:</td><td style="padding: 0; border: none;"><span class="pagenum"></span></td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div class="footer-section">
            <img src="{{ public_path('pln2.jpg') }}" alt="Footer">
        </div>
    </footer>

    <main>
        <div class="title-section">
            <div class="title">{{ $document->judul_dokumen ?? (str_contains(strtolower($document->documentType?->slug ?? ''), 'qa') ? 'SURAT TUGAS AUDIT QA' : 'SURAT TUGAS AUDIT') }}</div>
            <div class="subtitle">{{ $document->title }}</div>
        </div>

        <div class="text-justify" style="margin-bottom: 10pt;">
            {!! nl2br(e($document->deskripsi_pembuka ?? '-')) !!}
        </div>

        <table class="content-table" style="margin-bottom: 0;">
            <colgroup>
                <col style="width: 6%;">
                <col style="width: 69%;">
                <col style="width: 25%;">
            </colgroup>
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;" width="6%">No.</th>
                    <th style="width: 69%; text-align: center;" width="69%">Identitas {{ str_contains(strtolower($document->documentType?->slug ?? ''), 'qa') ? 'Tim QA' : 'Tim Audit' }}</th>
                    <th style="width: 25%; text-align: center;" width="25%">Jabatan Tim</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @if($document->tim_audit && is_array($document->tim_audit))
                    @foreach($document->tim_audit as $tim)
                    <tr>
                        <td style="width: 6%; text-align: center; vertical-align: top;" width="6%">{{ $counter++ }}.</td>
                        <td style="width: 69%; vertical-align: top;" width="69%">
                            <table class="identitas-table">
                                <tr>
                                    <td style="width: 55px;" width="55">Nama</td>
                                    <td style="width: 10px; text-align: center;" width="10">:</td>
                                    <td style="text-transform: uppercase;">{{ $tim['nama'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 55px;" width="55">NIP</td>
                                    <td style="width: 10px; text-align: center;" width="10">:</td>
                                    <td>{{ $tim['nip'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 55px;" width="55">Jabatan</td>
                                    <td style="width: 10px; text-align: center;" width="10">:</td>
                                    <td style="text-transform: capitalize;">{{ $tim['jabatan_pegawai'] ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 25%; text-align: center; vertical-align: middle;" width="25%">{{ $tim['jabatan_tim'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="width: 6%; text-align: center;" width="6%">-</td>
                        <td colspan="2" style="width: 94%; text-align: center; color: #777; font-style: italic; padding: 10px;" width="94%">(Tidak ada susunan tim yang dicantumkan)</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @php
            $formatTextLines = function($text) {
                if (!$text) return ['-'];
                $raw = explode("\n", str_replace("\r", "", $text));
                $items = [];
                foreach ($raw as $line) {
                    $t = trim($line);
                    if ($t) {
                        if (strlen($t) > 300) {
                            $parts = preg_split('/(?<=[:\.\?\!])\s+/', $t, -1, PREG_SPLIT_NO_EMPTY);
                            if (count($parts) === 1 && str_contains($t, ':')) {
                                $parts = preg_split('/(?<=:)/', $t, -1, PREG_SPLIT_NO_EMPTY);
                            }
                            foreach ($parts as $p) {
                                if (trim($p)) $items[] = trim($p);
                            }
                        } else {
                            $items[] = $t;
                        }
                    }
                }
                return !empty($items) ? $items : ['-'];
            };

            $scopeItems = $formatTextLines($document->lingkup_audit);
            $tujuanItems = $formatTextLines($document->tujuan_audit);
            $isQa = str_contains(strtolower($document->documentType?->slug ?? ''), 'qa');
        @endphp

        <table class="content-table" style="margin-top: -1px;">
            <colgroup>
                <col style="width: 32%;">
                <col style="width: 3%;">
                <col style="width: 65%;">
            </colgroup>
            <tbody>
                @if($isQa)
                <tr>
                    <td style="width: 32%; font-style: italic;" width="32%">Periode QA</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td style="width: 65%;" width="65%">{{ $document->periode_audit ?? '-' }}</td>
                </tr>
                
                {{-- Lingkup QA --}}
                @foreach($scopeItems as $idx => $line)
                    @php
                        $isList = preg_match('/^(\d+\.|[-•a-zA-Z]\.)\s/', $line);
                        $isFirst = ($idx === 0);
                    @endphp
                    <tr>
                        <td style="width: 32%; font-style: italic;" width="32%">{{ $isFirst ? 'Lingkup QA' : '' }}</td>
                        <td style="width: 3%; text-align: center;" width="3%">{{ $isFirst ? ':' : '' }}</td>
                        <td class="text-justify" style="width: 65%; {{ $isList ? 'padding-left: 15px; text-indent: -15px;' : '' }}" width="65%">
                            {{ $line }}
                        </td>
                    </tr>
                @endforeach

                {{-- Tujuan QA --}}
                @foreach($tujuanItems as $idx => $line)
                    @php
                        $isList = preg_match('/^(\d+\.|[-•a-zA-Z]\.)\s/', $line);
                        $isFirst = ($idx === 0);
                    @endphp
                    <tr>
                        <td style="width: 32%;" width="32%">{{ $isFirst ? 'Tujuan QA' : '' }}</td>
                        <td style="width: 3%; text-align: center;" width="3%">{{ $isFirst ? ':' : '' }}</td>
                        <td class="text-justify" style="width: 65%; {{ $isList ? 'padding-left: 15px; text-indent: -15px;' : '' }}" width="65%">
                            {{ $line }}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td style="width: 32%;" width="32%">Waktu Pelaksanaan QA</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td style="width: 65%;" width="65%">{{ $document->jadwal_audit ?? '-' }}</td>
                </tr>
                @else
                <tr>
                    <td style="width: 32%; font-style: italic;" width="32%">Audit Universe</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td style="width: 65%;" width="65%">{{ $document->auditee ?? '-' }}</td>
                </tr>

                
                <tr>
                    <td style="width: 32%; font-style: italic;" width="32%">Scope Audit</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td class="text-justify" style="width: 65%;" width="65%">{!! nl2br(e($document->lingkup_audit ?? '-')) !!}</td>
                </tr>
                <tr>
                    <td style="width: 32%;" width="32%">Tujuan Audit</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td class="text-justify" style="width: 65%;" width="65%">{!! nl2br(e($document->tujuan_audit ?? '-')) !!}</td>
                </tr>
<tr><td style="width: 32%;" width="32%">Periode Audit</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td style="width: 65%;" width="65%">{{ $document->periode_audit ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="width: 32%;" width="32%">Waktu Pelaksanaan Audit</td>
                    <td style="width: 3%; text-align: center;" width="3%">:</td>
                    <td style="width: 65%;" width="65%">{{ $document->jadwal_audit ?? '-' }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="text-justify" style="margin-top: 10pt; margin-bottom: 15pt;">
            {!! nl2br(e($document->deskripsi_penutup ?? '-')) !!}
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div>{{ $document->tgl_stg ? $document->tgl_stg->format('d F Y') : ($document->created_at ? $document->created_at->format('d F Y') : '-') }}</div>
                <div style="font-weight: bold; margin-bottom: {{ $document->qr_code_path ? '6px' : '60px' }};">{{ $document->jabatan_direktur ?? '-' }}</div>
                @if($document->qr_code_path)
                <div style="margin-bottom: 6px;">
                    <img src="{{ public_path('storage/' . $document->qr_code_path) }}" alt="QR Code" style="width: 70px; height: 70px;">
                </div>
                @endif
                <div style="font-weight: bold;">{{ $document->nama_direktur ?? '-' }}</div>
            </div>
        </div>
    </main>

</body>
</html>
