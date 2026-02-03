<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Fabriku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white !important;
            padding: 14px 40px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #5a6fd6;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📧 Verifikasi Email Anda</h1>
    </div>
    <div class="content">
        <p>Halo <strong>{{ $user->name }}</strong>,</p>
        
        <p>Terima kasih telah mendaftar di Fabriku!</p>
        
        <p>Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda dan mengaktifkan akun.</p>

        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">✓ Verifikasi Email Saya</a>
        </div>

        <div class="info-box">
            <p><strong>⏰ Link ini akan kadaluarsa dalam 60 menit.</strong></p>
            <p style="margin: 0;">Jika Anda tidak membuat akun di Fabriku, abaikan email ini.</p>
        </div>

        <p>Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:</p>
        <p style="word-break: break-all; font-size: 12px; color: #666;">{{ $verificationUrl }}</p>

        <p>Salam hangat,<br>
        <strong>Tim Fabriku</strong></p>
    </div>
    
    <div class="footer">
        <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        <p>&copy; {{ date('Y') }} Fabriku. All rights reserved.</p>
    </div>
</body>
</html>
