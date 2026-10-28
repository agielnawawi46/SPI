<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #4a5568; line-height: 1.6; background-color: #f7fafc; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
        
        <!-- Header -->
        <div style="border-bottom: 2px solid #edf2f7; padding-bottom: 20px; margin-bottom: 20px;">
            <h2 style="color: #2b6cb0; margin: 0; font-size: 24px;">SIMAK</h2>
            <p style="color: #718096; margin: 5px 0 0 0; font-size: 14px;">Sistem Informasi Manajemen Audit & Kepatuhan — PT Pelayanan Energi Batam</p>
        </div>
        
        <!-- Salutation -->
        <p style="font-size: 16px; color: #2d3748; margin-bottom: 15px;">Yth. Bapak/Ibu <strong>{{ $recipientName ?? 'Pengguna' }}</strong>,</p>
        
        <p style="font-size: 15px; color: #4a5568; margin-bottom: 25px;">
            Berikut adalah pemberitahuan terbaru dari SIMAK (Sistem Informasi Manajemen Audit & Kepatuhan):
        </p>
        
        <!-- Message Content box -->
        <div style="background-color: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin-bottom: 25px; border-radius: 0 4px 4px 0;">
            <h3 style="margin-top: 0; margin-bottom: 10px; color: #2c5282; font-size: 16px;">{{ $title }}</h3>
            <p style="margin: 0; font-size: 15px; color: #2a4365;">{{ $messageContent }}</p>
        </div>
        
        <!-- Action Button -->
        <div style="margin-top: 30px; margin-bottom: 30px; text-align: center;">
            <a href="{{ $link }}" style="display: inline-block; padding: 12px 24px; background-color: #3182ce; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; box-shadow: 0 2px 4px rgba(49, 130, 206, 0.3);">
                Lihat Detail Dokumen
            </a>
        </div>
        
        <p style="font-size: 15px; color: #4a5568;">
            Mohon untuk segera memeriksa dokumen tersebut melalui tautan yang telah disediakan di atas.
        </p>
        
        <!-- Footer -->
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #edf2f7; font-size: 14px; color: #718096;">
            <p style="margin: 0 0 5px 0;">Hormat kami,</p>
            <p style="margin: 0; font-weight: bold; color: #4a5568;">Administrator SIMAK</p>
            <p style="margin: 5px 0 0 0; font-size: 12px;">Email ini dihasilkan secara otomatis oleh sistem, mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
