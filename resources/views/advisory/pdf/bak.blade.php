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
            font-weight: normal;
        }
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: fixed;
            word-wrap: break-word;
        }
        .content-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .content-table .label-col {
            width: 38%;
        }
        .content-table .colon-col {
            width: 3%;
            text-align: center;
        }
        .content-table .value-col {
            width: 59%;
            text-align: justify;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            table-layout: fixed;
        }
        .signature-table td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
            width: 50%;
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
        .cover-content {
            text-align: center;
            margin-top: 40px;
            color: #006563;
        }
        .cover-title {
            font-weight: bold;
            font-size: 21pt;
            text-transform: uppercase;
            margin-bottom: 40px;
        }
        .cover-auditee {
            font-weight: bold;
            font-size: 21pt;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .cover-meta {
            font-size: 14pt;
            font-weight: bold;
        }
        @page { margin: 20px 50px 20px 50px; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }
        .page-break { page-break-after: always; }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>

    <div class="footer-section">
        <img src="{{ public_path('pln2.jpg') }}" alt="Footer">
    </div>

    <!-- PAGE 1: COVER -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('pln.jpg') }}" alt="Logo">
            </td>
            <td class="header-text">
                <table style="width: auto; float: right; text-align: left;">
                    <tr>
                        <td>
                            <p style="margin: 0;">No. Formulir</p>
                            <p style="margin: 0;">Tanggal</p>
                            <p style="margin: 0;">Halaman</p>
                        </td>
                        <td>
                            <p style="margin: 0;">:</p>
                            <p style="margin: 0;">:</p>
                            <p style="margin: 0;">:</p>
                        </td>
                        <td>
                            <p style="margin: 0;">{{ $document->nomor_formulir ?? 'FMR-003-06' }}</p>
                            <p style="margin: 0;">{{ $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') : ($document->created_at ? $document->created_at->translatedFormat('d F Y') : '-') }}</p>
                            <p style="margin: 0;"><span class="pagenum"></span></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="cover-content">
        <div class="cover-title">{{ $document->judul_dokumen ?? 'BERITA ACARA KONSULTANSI' }}</div>
        <div class="cover-auditee">{{ $document->auditee ?? 'SATUAN PENGAWASAN INTERN' }}</div>
        <div class="cover-meta">
            <p style="margin: 5px 0;">NOMOR : {{ $document->title }}</p>
            <p style="margin: 5px 0;">TGL {{ $document->tgl_stg ? \Carbon\Carbon::parse($document->tgl_stg)->translatedFormat('d M Y') : '-' }}</p>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- PAGE 2: FORMULIR -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('pln.jpg') }}" alt="Logo">
            </td>
            <td class="header-text">
                <table style="width: auto; float: right; text-align: left;">
                    <tr>
                        <td>
                            <p style="margin: 0;">No. Formulir</p>
                            <p style="margin: 0;">Tanggal</p>
                            <p style="margin: 0;">Halaman</p>
                        </td>
                        <td>
                            <p style="margin: 0;">:</p>
                            <p style="margin: 0;">:</p>
                            <p style="margin: 0;">:</p>
                        </td>
                        <td>
                            <p style="margin: 0;">{{ $document->nomor_formulir ?? 'FMR-003-06' }}</p>
                            <p style="margin: 0;">{{ $document->tanggal_formulir ? \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') : ($document->created_at ? $document->created_at->translatedFormat('d F Y') : '-') }}</p>
                            <p style="margin: 0;"><span class="pagenum"></span></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="title-section">
        <div class="title">FORMULIR LAYANAN KONSULTASI</div>
        <div class="subtitle">Nomor: {{ $document->title }}</div>
    </div>

    <table class="content-table">
        <tbody>
            <tr>
                <td class="label-col">Nama / Jabatan</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->nama_jabatan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Nomor Induk Pegawai (NIP)</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Bidang Tugas (Bidang/Satuan/Tim Kerja)</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->bidang_tugas ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col" style="height: 60px;">Deskripsi Permasalahan</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->deskripsi_permasalahan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col" style="height: 60px;">Tujuan Konsultasi</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->tujuan_konsultasi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Waktu penyelesaian yang dibutuhkan</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->waktu_penyelesaian ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col" style="height: 60px;">Pengungkapan Potensi Pengurangan Independensi dan Objektifitas Auditor</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->pengungkapan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col" style="height: 60px;">Review Permohonan Konsultasi *)</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $document->review_permohonan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col" style="height: 100px;">Kesimpulan *)</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <div style="margin-bottom: 10px;">
                        <span class="font-bold">Terima (dilanjutkan)</span>
                    </div>
                    <div>
                        <p style="margin: 0 0 5px 0;">Alasan :</p>
                        <p style="margin: 0;">{{ $document->alasan ?? '-' }}</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 20px;"></td>
            </tr>
        </tbody>
    </table>

    <p style="font-size: 10pt; font-style: italic; margin-bottom: 15px;">*) diisi oleh KSPI</p>

    <table class="signature-table">
        <tbody>
            <tr>
                <td>
                    <p style="margin-top: 0; margin-bottom: 0;">&nbsp;</p>
                    <p style="margin-top: 5px; margin-bottom: 30px;">Kepala Satuan Pengawasan Intern</p>
                    <div style="height: 70px;">
                        @if($document->qr_code_path)
                            <img src="{{ public_path('storage/' . $document->qr_code_path) }}" alt="QR Code KSPI" style="height: 70px; width: 70px;">
                        @endif
                    </div>
                    <p class="font-bold uppercase" style="margin-top: 10px; margin-bottom: 0;">{{ $document->nama_kspi ?? '' }}</p>
                </td>
                <td>
                    <p style="margin-top: 0; margin-bottom: 0;">Batam, {{ $document->created_at->format('d F Y') }}</p>
                    <p style="margin-top: 5px; margin-bottom: 30px;">Pimpinan Klien</p>
                    <div style="height: 70px;">
                        @if($document->qr_code_path_auditee)
                            <img src="{{ public_path('storage/' . $document->qr_code_path_auditee) }}" alt="QR Code Auditee" style="height: 70px; width: 70px;">
                        @endif
                    </div>
                    <p class="font-bold uppercase" style="margin-top: 10px; margin-bottom: 0;">{{ $document->nama_pimpinan_klien ?? (explode('/', $document->nama_jabatan)[0] ?? '______________________') }}</p>
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
