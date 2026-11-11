<!-- A4 Paper Preview Container -->
<style>
    @page {
        size: A4 portrait;
        margin: 0;
    }
    @media print {
        body {
            background: white !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-hidden,
        .navbar,
        .sidebar,
        .btn-primary,
        .btn-secondary,
        header,
        nav,
        aside {
            display: none !important;
        }
        .a4-page-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            page-break-after: always !important;
            break-after: page !important;
        }
        .stg-preview-sheet {
            box-shadow: none !important;
            margin: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            min-height: 297mm !important;
            max-height: 297mm !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }

    .a4-page-wrapper {
        margin-bottom: 2rem;
    }
    .a4-badge-bar {
        max-width: 210mm;
        margin: 0 auto 0.5rem auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #64748b;
        padding: 0 0.5rem;
        font-weight: 500;
    }
    .a4-badge-pill {
        background: #f1f5f9;
        color: #475569;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.6875rem;
        border: 1px solid #e2e8f0;
    }
    .stg-preview-sheet {
        box-sizing: border-box;
        width: 210mm;
        height: 297mm;
        min-height: 297mm;
        max-height: 297mm;
        background: #ffffff;
        margin: 0 auto;
        padding: 35px 40px;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.25;
        color: #000000;
    }
    .stg-sheet-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 4px;
        flex-shrink: 0;
    }
    .stg-sheet-header .logo-box {
        width: 25%;
    }
    .stg-sheet-header .logo-box img {
        height: 55px;
        max-height: 55px;
        width: auto;
        aspect-ratio: 720 / 270;
        object-fit: contain;
        display: block;
    }
    .stg-sheet-header .meta-box {
        width: 75%;
        text-align: right;
        color: #002060;
        font-size: 8pt;
        line-height: 1.3;
    }
    .stg-sheet-header .meta-table {
        display: inline-block;
        text-align: left;
        border-collapse: collapse;
        border: none;
    }
    .stg-sheet-header .meta-table td {
        padding: 0 2px;
        border: none;
        vertical-align: top;
    }
    .stg-sheet-content {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        margin-bottom: 8px;
    }
    .stg-sheet-footer {
        margin-top: auto;
        padding-top: 4px;
        flex-shrink: 0;
        width: 100%;
    }
    .stg-sheet-footer img {
        width: 100%;
        height: auto;
        aspect-ratio: 2277 / 270;
        display: block;
    }
    .stg-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        table-layout: fixed;
        font-size: 9.5pt;
    }
    .stg-table th {
        border: 1px solid #000000;
        padding: 5px 6px;
        text-align: center;
        font-weight: bold;
        background-color: #f8fafc;
    }
    .stg-table td {
        padding: 4px 6px;
        vertical-align: top;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .stg-identitas-grid {
        display: grid;
        grid-template-columns: 60px 10px 1fr;
        column-gap: 4px;
        font-size: 9pt;
        line-height: 1.3;
    }
</style>

@php
    $isQa = str_contains(strtolower($document->documentType?->slug ?? ''), 'qa');

    $formatTextChunks = function($text) {
        if (!$text) return ['-'];
        $raw = explode("\n", str_replace("\r", "", $text));
        $items = [];
        foreach ($raw as $line) {
            $t = trim($line);
            if ($t) {
                if (strlen($t) > 250) {
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

    $pembukaItems = $formatTextChunks($document->deskripsi_pembuka);
    $scopeItems = $formatTextChunks($document->lingkup_audit);
    $tujuanItems = $formatTextChunks($document->tujuan_audit);
    $penutupItems = $formatTextChunks($document->deskripsi_penutup);
@endphp

<!-- Container for Paginated A4 Sheets -->
<div id="stg-paginated-container"></div>

<!-- Hidden Staging Content Source (Parsed into memory by script) -->
<div id="stg-raw-source" style="position: absolute; left: -9999px; top: 0; width: 713px; visibility: hidden; pointer-events: none;" aria-hidden="true">
    
    <!-- Header Template -->
    <div id="stg-tpl-header" class="stg-sheet-header">
        <div class="logo-box">
            <img src="{{ asset('pln.jpg') }}" alt="PLN Logo">
        </div>
        <div class="meta-box">
            <table class="meta-table">
                @if($document->header_text || $document->nomor_formulir)
                <tr><td style="width: 65px;">No. Formulir</td><td>:</td><td>{{ $document->header_text ?? $document->nomor_formulir }}</td></tr>
                @endif
                @if($document->tanggal_formulir)
                <tr><td>Tanggal</td><td>:</td><td>{{ \Carbon\Carbon::parse($document->tanggal_formulir)->translatedFormat('d F Y') }}</td></tr>
                @endif
                <tr><td>Halaman</td><td>:</td><td class="stg-page-number">1</td></tr>
            </table>
        </div>
    </div>

    <!-- Footer Template -->
    <div id="stg-tpl-footer" class="stg-sheet-footer">
        <img src="{{ asset('pln2.jpg') }}" alt="PLN Footer">
    </div>

    <!-- Raw Items to Distribute Across Pages -->
    <div id="stg-raw-items">
        <!-- 1. Title Block -->
        <div class="stg-item-general" style="text-align: center; margin-bottom: 16px;">
            <h4 style="font-size: 11pt; font-weight: normal; text-transform: uppercase; margin-bottom: 4px;">{{ $document->judul_dokumen ?? ($isQa ? 'SURAT TUGAS AUDIT QA' : 'SURAT TUGAS AUDIT') }}</h4>
            <p style="font-size: 11pt; font-weight: normal;">{{ $document->title }}</p>
        </div>

        <!-- 2. Pembuka Paragraphs -->
        <div class="stg-pembuka-group" style="margin-bottom: 12px;">
            @foreach($pembukaItems as $line)
            <p class="stg-item-general" style="text-align: justify; word-break: break-word; overflow-wrap: break-word; margin-bottom: 6px; font-size: 10pt; line-height: 1.35;">{{ $line }}</p>
            @endforeach
        </div>

        <!-- 3. Tim Table Rows (Inside a valid table so browser parses tr tags correctly) -->
        <table>
            <tbody id="stg-tim-rows">
                @php $counter = 1; @endphp
                @if($document->tim_audit && is_array($document->tim_audit))
                    @foreach($document->tim_audit as $tim)
                    <tr class="stg-item-tim-row">
                        <td style="border: 1px solid #000; text-align: center; vertical-align: top; width: 6%;">{{ $counter++ }}.</td>
                        <td style="border: 1px solid #000; vertical-align: top; width: 69%;">
                            <div class="stg-identitas-grid">
                                <div>Nama</div><div>:</div><div style="text-transform: uppercase; font-weight: 500;">{{ $tim['nama'] ?? '-' }}</div>
                                <div>NIP</div><div>:</div><div>{{ $tim['nip'] ?? '-' }}</div>
                                <div>Jabatan</div><div>:</div><div style="text-transform: capitalize;">{{ $tim['jabatan_pegawai'] ?? '-' }}</div>
                            </div>
                        </td>
                        <td style="border: 1px solid #000; text-align: center; vertical-align: middle; width: 25%; font-weight: 500;">{{ $tim['jabatan_tim'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr class="stg-item-tim-row">
                        <td style="border: 1px solid #000; text-align: center;">-</td>
                        <td colspan="2" style="border: 1px solid #000; text-align: center; color: #64748b; font-style: italic; padding: 8px;">(Tidak ada susunan tim yang dicantumkan)</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- 4. Detail Table Rows (Inside a valid table) -->
        <table>
            <tbody id="stg-detail-rows">
                @if($isQa)
                    <!-- Periode QA -->
                    <tr class="stg-item-detail-row" data-section="periode_qa">
                        <td style="border: 1px solid #000; font-style: italic;">Periode QA</td>
                        <td style="border: 1px solid #000; text-align: center;">:</td>
                        <td style="border: 1px solid #000;">{{ $document->periode_audit ?? '-' }}</td>
                    </tr>
                    <!-- Lingkup QA -->
                    @foreach($scopeItems as $idx => $line)
                    <tr class="stg-item-detail-row" data-section="lingkup_qa" data-idx="{{ $idx }}" data-total="{{ count($scopeItems) }}">
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; font-style: italic;">{{ $idx === 0 ? 'Lingkup QA' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">{{ $idx === 0 ? ':' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: justify; word-break: break-word; overflow-wrap: break-word; line-height: 1.35;">{{ $line }}</td>
                    </tr>
                    @endforeach
                    <!-- Tujuan QA -->
                    @foreach($tujuanItems as $idx => $line)
                    <tr class="stg-item-detail-row" data-section="tujuan_qa" data-idx="{{ $idx }}" data-total="{{ count($tujuanItems) }}">
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000;">{{ $idx === 0 ? 'Tujuan QA' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">{{ $idx === 0 ? ':' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: justify; word-break: break-word; overflow-wrap: break-word; line-height: 1.35;">{{ $line }}</td>
                    </tr>
                    @endforeach
                    <!-- Waktu QA -->
                    <tr class="stg-item-detail-row" data-section="waktu_qa">
                        <td style="border: 1px solid #000;">Waktu Pelaksanaan QA</td>
                        <td style="border: 1px solid #000; text-align: center;">:</td>
                        <td style="border: 1px solid #000;">{{ $document->jadwal_audit ?? '-' }}</td>
                    </tr>
                @else
                    <!-- Universe -->
                    <tr class="stg-item-detail-row" data-section="universe">
                        <td style="border: 1px solid #000; font-style: italic;">Audit Universe</td>
                        <td style="border: 1px solid #000; text-align: center;">:</td>
                        <td style="border: 1px solid #000;">{{ $document->auditee ?? '-' }}</td>
                    </tr>
                    <!-- Scope Audit -->
                    @foreach($scopeItems as $idx => $line)
                    <tr class="stg-item-detail-row" data-section="scope" data-idx="{{ $idx }}" data-total="{{ count($scopeItems) }}">
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; font-style: italic;">{{ $idx === 0 ? 'Scope Audit' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">{{ $idx === 0 ? ':' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: justify; word-break: break-word; overflow-wrap: break-word; line-height: 1.35;">{{ $line }}</td>
                    </tr>
                    @endforeach
                    <!-- Tujuan Audit -->
                    @foreach($tujuanItems as $idx => $line)
                    <tr class="stg-item-detail-row" data-section="tujuan" data-idx="{{ $idx }}" data-total="{{ count($tujuanItems) }}">
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000;">{{ $idx === 0 ? 'Tujuan Audit' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">{{ $idx === 0 ? ':' : '' }}</td>
                        <td style="border-left: 1px solid #000; border-right: 1px solid #000; text-align: justify; word-break: break-word; overflow-wrap: break-word; line-height: 1.35;">{{ $line }}</td>
                    </tr>
                    @endforeach
                    <!-- Periode Audit -->
                    <tr class="stg-item-detail-row" data-section="periode">
                        <td style="border: 1px solid #000;">Periode Audit</td>
                        <td style="border: 1px solid #000; text-align: center;">:</td>
                        <td style="border: 1px solid #000;">{{ $document->periode_audit ?? '-' }}</td>
                    </tr>
                    <!-- Waktu Pelaksanaan -->
                    <tr class="stg-item-detail-row" data-section="waktu">
                        <td style="border: 1px solid #000;">Waktu Pelaksanaan Audit</td>
                        <td style="border: 1px solid #000; text-align: center;">:</td>
                        <td style="border: 1px solid #000;">{{ $document->jadwal_audit ?? '-' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- 5. Penutup Paragraphs -->
        <div class="stg-penutup-group" style="margin-top: 12px; margin-bottom: 16px;">
            @foreach($penutupItems as $line)
            <p class="stg-item-general" style="text-align: justify; word-break: break-word; overflow-wrap: break-word; margin-bottom: 6px; font-size: 10pt; line-height: 1.35;">{{ $line }}</p>
            @endforeach
        </div>

        <!-- 6. Signature Section -->
        <div class="stg-item-general" style="display: flex; flex-direction: column; align-items: center; margin-top: 24px; margin-bottom: 16px; page-break-inside: avoid;">
            <p>{{ $document->tgl_stg ? $document->tgl_stg->format('d F Y') : ($document->created_at ? $document->created_at->format('d F Y') : '-') }}</p>
            <p style="font-weight: bold; margin-bottom: 12px;">{{ $document->jabatan_direktur ?? '-' }}</p>
            @if($document->qr_code_path)
            <img src="{{ asset('storage/' . $document->qr_code_path) }}" alt="QR Code" style="width: 80px; height: 80px; object-fit: contain; margin: 4px 0;">
            @else
            <div style="height: 60px;"></div>
            @endif
            <div style="height: 8px;"></div>
            <p style="font-weight: bold;">{{ $document->nama_direktur ?? '-' }}</p>
        </div>
    </div>

</div>

<!-- Client-Side Exact A4 Dynamic Pagination Engine -->
<script>
(function() {
    function initStgPagination() {
        const container = document.getElementById('stg-paginated-container');
        const headerTpl = document.getElementById('stg-tpl-header');
        const footerTpl = document.getElementById('stg-tpl-footer');
        const rawItems = document.getElementById('stg-raw-items');
        if (!container || !headerTpl || !footerTpl || !rawItems) return;

        container.innerHTML = '';
        let pages = [];
        let currentPage = null;

        function createNewPage() {
            const pageNum = pages.length + 1;

            const wrapper = document.createElement('div');
            wrapper.className = 'a4-page-wrapper';

            // Top badge bar
            const badge = document.createElement('div');
            badge.className = 'a4-badge-bar print-hidden';
            badge.innerHTML = `
                <span style="display: flex; align-items: center; gap: 6px;">
                    <svg style="width: 14px; height: 14px; color: #0284c7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span><strong>Lembar <span class="stg-badge-cur">${pageNum}</span></strong> dari <span class="stg-badge-total">1</span></span>
                </span>
                <span class="a4-badge-pill">Ukuran A4 (210 &times; 297 mm)</span>
            `;
            wrapper.appendChild(badge);

            // Sheet card
            const sheet = document.createElement('div');
            sheet.className = 'stg-preview-sheet';

            // Kop Surat Header
            const header = headerTpl.cloneNode(true);
            header.removeAttribute('id');
            const pageNumSpan = header.querySelector('.stg-page-number');
            if (pageNumSpan) pageNumSpan.textContent = pageNum;
            sheet.appendChild(header);

            // Content Area
            const content = document.createElement('div');
            content.className = 'stg-sheet-content';
            sheet.appendChild(content);

            // Footer
            const footer = footerTpl.cloneNode(true);
            footer.removeAttribute('id');
            sheet.appendChild(footer);

            wrapper.appendChild(sheet);
            container.appendChild(wrapper);

            const pageObj = {
                wrapper: wrapper,
                sheet: sheet,
                content: content,
                activeTable: null,
                activeTableType: null
            };
            pages.push(pageObj);
            return pageObj;
        }

        function isOverflow(page) {
            if ((page.sheet.scrollHeight - page.sheet.clientHeight) > 1) {
                return true;
            }
            if ((page.content.scrollHeight - page.content.clientHeight) > 1) {
                return true;
            }
            const footer = page.sheet.querySelector('.stg-sheet-footer');
            if (footer) {
                const footerRect = footer.getBoundingClientRect();
                const lastEl = page.content.lastElementChild;
                if (lastEl) {
                    const lastElRect = lastEl.getBoundingClientRect();
                    if (lastElRect.bottom > (footerRect.top - 8)) {
                        return true;
                    }
                }
            }
            return false;
        }

        function ensureTable(page, type) {
            if (page.activeTable && page.activeTableType === type) {
                return page.activeTable;
            }

            if (page.activeTable) {
                closeTableBottom(page.activeTable);
            }

            const table = document.createElement('table');
            table.className = 'stg-table';
            table.style.marginBottom = (type === 'tim' ? '-1px' : '0');

            if (type === 'tim') {
                table.innerHTML = `
                    <colgroup>
                        <col style="width: 6%;">
                        <col style="width: 69%;">
                        <col style="width: 25%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="width: 6%;">No.</th>
                            <th style="width: 69%;">Identitas {{ $isQa ? 'Tim QA' : 'Tim Audit' }}</th>
                            <th style="width: 25%;">Jabatan Tim</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;
            } else {
                table.innerHTML = `
                    <colgroup>
                        <col style="width: 32%;">
                        <col style="width: 3%;">
                        <col style="width: 65%;">
                    </colgroup>
                    <tbody></tbody>
                `;
            }

            page.content.appendChild(table);
            page.activeTable = table;
            page.activeTableType = type;
            return table;
        }

        function closeTableBottom(table) {
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                lastRow.querySelectorAll('td').forEach(td => {
                    td.style.setProperty('border-bottom', '1px solid black', 'important');
                });
            }
        }

        currentPage = createNewPage();

        // Collect all items in sequential order
        const itemNodes = [];

        // 1. Title
        const titleEl = rawItems.querySelector('.stg-item-general');
        if (titleEl) itemNodes.push({ type: 'general', el: titleEl.cloneNode(true) });

        // 2. Pembuka Paragraphs
        rawItems.querySelectorAll('.stg-pembuka-group .stg-item-general').forEach(p => {
            itemNodes.push({ type: 'general', el: p.cloneNode(true) });
        });

        // 3. Tim Rows
        rawItems.querySelectorAll('.stg-item-tim-row').forEach(tr => {
            itemNodes.push({ type: 'tim-row', el: tr.cloneNode(true) });
        });

        // 4. Detail Rows
        rawItems.querySelectorAll('.stg-item-detail-row').forEach(tr => {
            itemNodes.push({
                type: 'detail-row',
                el: tr.cloneNode(true),
                section: tr.getAttribute('data-section'),
                idx: parseInt(tr.getAttribute('data-idx') || '0'),
                total: parseInt(tr.getAttribute('data-total') || '1')
            });
        });

        // 5. Penutup Paragraphs
        rawItems.querySelectorAll('.stg-penutup-group .stg-item-general').forEach(p => {
            itemNodes.push({ type: 'general', el: p.cloneNode(true) });
        });

        // 6. Signature Block
        const allGenerals = rawItems.querySelectorAll('.stg-item-general');
        const sigEl = allGenerals[allGenerals.length - 1];
        if (sigEl && sigEl !== titleEl) itemNodes.push({ type: 'general', el: sigEl.cloneNode(true) });

        // Place items with dynamic overflow detection
        itemNodes.forEach((item) => {
            if (item.type === 'tim-row') {
                let table = ensureTable(currentPage, 'tim');
                let tbody = table.querySelector('tbody');
                tbody.appendChild(item.el);

                if (isOverflow(currentPage)) {
                    tbody.removeChild(item.el);
                    if (tbody.children.length === 0) {
                        table.remove();
                        currentPage.activeTable = null;
                        currentPage.activeTableType = null;
                    } else {
                        closeTableBottom(table);
                    }

                    currentPage = createNewPage();
                    table = ensureTable(currentPage, 'tim');
                    tbody = table.querySelector('tbody');
                    tbody.appendChild(item.el);
                }
            } else if (item.type === 'detail-row') {
                let table = ensureTable(currentPage, 'detail');
                let tbody = table.querySelector('tbody');
                
                if (tbody.children.length === 0) {
                    item.el.querySelectorAll('td').forEach(td => td.style.setProperty('border-top', '1px solid black', 'important'));
                    const tds = item.el.querySelectorAll('td');
                    if (tds.length === 3 && !tds[0].textContent.trim()) {
                        if (item.section === 'scope') {
                            tds[0].innerHTML = '<em>Scope Audit (lanjutan)</em>';
                            tds[1].textContent = ':';
                        } else if (item.section === 'tujuan') {
                            tds[0].innerHTML = '<em>Tujuan Audit (lanjutan)</em>';
                            tds[1].textContent = ':';
                        } else if (item.section === 'lingkup_qa') {
                            tds[0].innerHTML = '<em>Lingkup QA (lanjutan)</em>';
                            tds[1].textContent = ':';
                        } else if (item.section === 'tujuan_qa') {
                            tds[0].innerHTML = '<em>Tujuan QA (lanjutan)</em>';
                            tds[1].textContent = ':';
                        }
                    }
                }
                if (item.idx === item.total - 1) {
                    item.el.querySelectorAll('td').forEach(td => td.style.setProperty('border-bottom', '1px solid black', 'important'));
                }

                tbody.appendChild(item.el);

                if (isOverflow(currentPage)) {
                    tbody.removeChild(item.el);
                    if (tbody.children.length === 0) {
                        table.remove();
                        currentPage.activeTable = null;
                        currentPage.activeTableType = null;
                    } else {
                        closeTableBottom(table);
                    }

                    currentPage = createNewPage();
                    table = ensureTable(currentPage, 'detail');
                    tbody = table.querySelector('tbody');

                    item.el.querySelectorAll('td').forEach(td => td.style.setProperty('border-top', '1px solid black', 'important'));
                    const tds = item.el.querySelectorAll('td');
                    if (tds.length === 3 && !tds[0].textContent.trim()) {
                        if (item.section === 'scope') {
                            tds[0].innerHTML = '<em>Scope Audit (lanjutan)</em>';
                            tds[1].textContent = ':';
                        } else if (item.section === 'tujuan') {
                            tds[0].innerHTML = '<em>Tujuan Audit (lanjutan)</em>';
                            tds[1].textContent = ':';
                        } else if (item.section === 'lingkup_qa') {
                            tds[0].innerHTML = '<em>Lingkup QA (lanjutan)</em>';
                            tds[1].textContent = ':';
                        } else if (item.section === 'tujuan_qa') {
                            tds[0].innerHTML = '<em>Tujuan QA (lanjutan)</em>';
                            tds[1].textContent = ':';
                        }
                    }
                    tbody.appendChild(item.el);
                }
            } else {
                if (currentPage.activeTable) {
                    closeTableBottom(currentPage.activeTable);
                    currentPage.activeTable = null;
                    currentPage.activeTableType = null;
                }

                currentPage.content.appendChild(item.el);

                if (isOverflow(currentPage)) {
                    currentPage.content.removeChild(item.el);
                    currentPage = createNewPage();
                    currentPage.content.appendChild(item.el);
                }
            }
        });

        if (currentPage && currentPage.activeTable) {
            closeTableBottom(currentPage.activeTable);
        }

        // Final safety pass across all pages: ensure every table's bottom row is sealed with a solid border
        pages.forEach(p => {
            if (p.activeTable) {
                closeTableBottom(p.activeTable);
            }
            p.content.querySelectorAll('table.stg-table').forEach(tbl => {
                closeTableBottom(tbl);
            });
        });

        // Finalize page numbering and badges
        const total = pages.length;
        pages.forEach((p, idx) => {
            const curBadge = p.wrapper.querySelector('.stg-badge-cur');
            const totBadge = p.wrapper.querySelector('.stg-badge-total');
            if (curBadge) curBadge.textContent = idx + 1;
            if (totBadge) totBadge.textContent = total;

            const headerPage = p.sheet.querySelector('.stg-page-number');
            if (headerPage) headerPage.textContent = idx + 1;
        });

        window.stgTotalPages = total;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStgPagination);
    } else {
        initStgPagination();
    }
    window.addEventListener('load', initStgPagination);
})();
</script>
