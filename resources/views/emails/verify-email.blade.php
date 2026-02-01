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
            background-color: #f4f4f4;
        }
        .email-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .content {
            background: #ffffff;
            padding: 30px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white !important;
            padding: 14px 35px;
            text-decoration: none;
            border-radius: 5px;
            margin: 25px 0;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            background: #5568d3;
            box-shadow: 0 6px 8px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background: #f0f4ff;
            padding: 20px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-text {
            color: #f5576c;
            font-weight: bold;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
        }
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #667eea, transparent);
            margin: 25px 0;
        }
        .alt-link {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Fabriku</div>
            <h1 style="margin: 0; font-size: 24px;">📧 Verifikasi Alamat Email</h1>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $userName }}</strong>,</p>
            
            <p>Terima kasih telah mendaftar di Fabriku! Untuk melanjutkan, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:</p>

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verifikasi Email Saya</a>
            </div>

            <div class="info-box">
                <p style="margin: 0;"><strong>⚠️ Penting:</strong></p>
                <p style="margin: 5px 0 0 0;">Link verifikasi ini akan <span class="warning-text">kadaluarsa dalam 60 menit</span>. Jika link sudah kadaluarsa, Anda dapat meminta link verifikasi baru dari halaman login.</p>
            </div>

            <div class="divider"></div>

            <p><strong>Tidak dapat mengklik tombol?</strong><br>
            Salin dan tempel URL berikut ke browser Anda:</p>
            
            <div class="alt-link">
                {{ $verificationUrl }}
            </div>

            <div class="divider"></div>

            <p style="color: #666; font-size: 14px;">Jika Anda tidak membuat akun di Fabriku, abaikan email ini dan tidak ada tindakan lebih lanjut yang diperlukan.</p>

            <p style="margin-top: 30px;">Salam hangat,<br>
            <strong>Tim Fabriku</strong></p>
        </div>
        
        <div class="footer">
            <p style="margin: 5px 0;">Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} Fabriku. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
