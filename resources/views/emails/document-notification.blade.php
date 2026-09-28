<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
        <h2 style="color: #0056b3;">{{ $title }}</h2>
        
        <p style="font-size: 16px;">Halo,</p>
        
        <p style="font-size: 16px;">
            {{ $messageContent }}
        </p>
        
        <div style="margin-top: 30px;">
            <a href="{{ $link }}" style="display: inline-block; padding: 10px 20px; background-color: #0056b3; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Lihat Dokumen
            </a>
        </div>
        
        <p style="margin-top: 30px; font-size: 14px; color: #777;">
            Terima kasih,<br>
            Sistem Informasi SPI
        </p>
    </div>
</body>
</html>
