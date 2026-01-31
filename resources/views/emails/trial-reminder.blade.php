<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Trial Fabriku</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: #f5576c;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
        }
        .price-box {
            background: white;
            padding: 20px;
            border: 2px solid #667eea;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
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
        <h1>⏰ Pengingat Trial Anda</h1>
    </div>
    <div class="content">
        <p>Halo <strong>{{ $tenant->name }}</strong>,</p>
        
        <div class="warning-box">
            <h3>⚠️ Trial Anda akan berakhir dalam <strong>{{ $daysLeft }} hari</strong></h3>
            <p>Tanggal berakhir: <strong>{{ $tenant->subscription_expires_at->format('d F Y') }}</strong></p>
        </div>

        <p>Kami berharap Anda menikmati pengalaman menggunakan Fabriku selama periode trial!</p>

        <p>Untuk terus menggunakan semua fitur Fabriku tanpa gangguan, silakan upgrade ke paket berbayar sebelum trial Anda berakhir.</p>

        <div class="price-box">
            <h3>💎 Paket Langganan</h3>
            <p><strong>Bulanan:</strong> Rp 25.000/bulan</p>
            <p><strong>Tahunan:</strong> Rp 250.000/tahun <span style="color: #28a745;">(Hemat 17%!)</span></p>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/subscription" class="button">Upgrade Sekarang →</a>
        </div>

        <h3>✨ Manfaat Upgrade:</h3>
        <ul>
            <li>Akses tanpa batas ke semua fitur</li>
            <li>Penyimpanan data unlimited</li>
            <li>Support prioritas</li>
            <li>Update fitur terbaru</li>
            <li>Keamanan data terjamin</li>
        </ul>

        <p>Jika Anda memiliki pertanyaan tentang paket langganan, jangan ragu untuk menghubungi kami.</p>

        <p>Salam hangat,<br>
        <strong>Tim Fabriku</strong></p>
    </div>
    
    <div class="footer">
        <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        <p>&copy; {{ date('Y') }} Fabriku. All rights reserved.</p>
    </div>
</body>
</html>
