<!DOCTYPE html>
<html>
<head>
    <title>Reminder Tindak Lanjut</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f7;">
    <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        
        {{-- Header Banner --}}
        @if($type === 'overdue')
        <div style="background: linear-gradient(135deg, #dc2626, #b91c1c); padding: 24px 30px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 20px;">🔴 TERLAMBAT — Tindak Lanjut Belum Selesai</h2>
            <p style="color: #fecaca; margin: 8px 0 0; font-size: 14px;">Sudah melewati jatuh tempo {{ abs($daysRemaining) }} hari</p>
        </div>
        @elseif($type === 'urgent')
        <div style="background: linear-gradient(135deg, #ea580c, #c2410c); padding: 24px 30px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 20px;">🟠 MENDESAK — Sisa {{ $daysRemaining }} Hari</h2>
            <p style="color: #fed7aa; margin: 8px 0 0; font-size: 14px;">Segera selesaikan sebelum batas waktu berakhir</p>
        </div>
        @elseif($type === 'initial')
        <div style="background: linear-gradient(135deg, #0056b3, #003d82); padding: 24px 30px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 20px;">📋 Notifikasi Tindak Lanjut Baru</h2>
            <p style="color: #bfdbfe; margin: 8px 0 0; font-size: 14px;">Dokumen monitoring telah disetujui</p>
        </div>
        @else
        <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 24px 30px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 20px;">🔔 Pengingat Mingguan Tindak Lanjut</h2>
            @if($daysRemaining !== null)
            <p style="color: #bfdbfe; margin: 8px 0 0; font-size: 14px;">Sisa waktu: {{ $daysRemaining }} hari sebelum jatuh tempo</p>
            @endif
        </div>
        @endif

        {{-- Body Content --}}
        <div style="padding: 30px;">
            <p style="margin-top: 0;">Halo <strong>{{ $followUp->pic_name ?? 'Bapak/Ibu' }}</strong>,</p>
            
            <p>
                @if($type === 'initial')
                    Dokumen Monitoring Tindak Lanjut <strong>{{ $followUp->document->title }}</strong> telah disetujui. Terdapat temuan yang memerlukan tindak lanjut dari Anda.
                @elseif($type === 'overdue')
                    Tindak lanjut untuk dokumen <strong>{{ $followUp->document->title }}</strong> telah <strong style="color: #dc2626;">melewati batas waktu {{ abs($daysRemaining) }} hari yang lalu</strong>. Mohon segera menyelesaikan dan mengunggah bukti tindak lanjut.
                @elseif($type === 'urgent')
                    Tindak lanjut untuk dokumen <strong>{{ $followUp->document->title }}</strong> akan <strong style="color: #ea580c;">jatuh tempo dalam {{ $daysRemaining }} hari</strong>. Mohon segera mengunggah bukti tindak lanjut.
                @else
                    Ini adalah pengingat otomatis bahwa Anda memiliki tindak lanjut yang belum selesai untuk dokumen <strong>{{ $followUp->document->title }}</strong>.
                @endif
            </p>
            
            {{-- Info Box --}}
            <div style="background-color: #f8fafc; padding: 20px; border-left: 4px solid {{ $type === 'overdue' ? '#dc2626' : ($type === 'urgent' ? '#ea580c' : '#0056b3') }}; margin: 20px 0; border-radius: 0 6px 6px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; vertical-align: top; width: 130px; font-weight: bold; color: #475569; font-size: 13px;">Temuan (LHA):</td>
                        <td style="padding: 6px 0; font-size: 13px; white-space: pre-wrap;">{{ $followUp->finding }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; vertical-align: top; font-weight: bold; color: #475569; font-size: 13px;">Rekomendasi:</td>
                        <td style="padding: 6px 0; font-size: 13px; white-space: pre-wrap;">{{ $followUp->recommendation }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; vertical-align: top; font-weight: bold; color: #475569; font-size: 13px;">Batas Waktu:</td>
                        <td style="padding: 6px 0; font-size: 13px;">
                            <span style="color: {{ $type === 'overdue' ? '#dc2626' : ($type === 'urgent' ? '#ea580c' : '#1e40af') }}; font-weight: bold;">
                                {{ $followUp->due_date ? \Carbon\Carbon::parse($followUp->due_date)->translatedFormat('d F Y') : '-' }}
                            </span>
                            @if($daysRemaining !== null && $type !== 'initial')
                                <span style="color: #94a3b8; font-size: 12px;">
                                    ({{ $daysRemaining < 0 ? 'Terlambat ' . abs($daysRemaining) . ' hari' : $daysRemaining . ' hari lagi' }})
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            
            <p>Silakan login ke sistem SPI untuk mengunggah bukti (evidence) tindak lanjut sebelum batas waktu berakhir.</p>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ route('login') }}" style="background-color: {{ $type === 'overdue' ? '#dc2626' : ($type === 'urgent' ? '#ea580c' : '#0056b3') }}; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Login ke Sistem SPI</a>
            </p>
        </div>
        
        {{-- Footer --}}
        <div style="background-color: #f8fafc; padding: 16px 30px; border-top: 1px solid #e2e8f0;">
            <p style="font-size: 11px; color: #94a3b8; text-align: center; margin: 0;">
                Email ini dikirimkan secara otomatis oleh Sistem SPI. Mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>
