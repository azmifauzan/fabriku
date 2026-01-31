<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Fabriku</title>
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
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
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
        <h1>🎉 Selamat Datang di Fabriku!</h1>
    </div>
    <div class="content">
        <p>Halo <strong>{{ $user->name }}</strong>,</p>
        
        <p>Terima kasih telah mendaftar di Fabriku! Akun Anda untuk <strong>{{ $tenant->name }}</strong> telah berhasil diaktivasi.</p>
        
        <div class="info-box">
            <h3>✨ Informasi Trial Anda:</h3>
            <ul>
                <li><strong>Nama Bisnis:</strong> {{ $tenant->name }}</li>
                <li><strong>Kategori Bisnis:</strong> {{ $tenant->getCategoryLabel() }}</li>
                <li><strong>Periode Trial:</strong> 30 hari</li>
                <li><strong>Berakhir pada:</strong> {{ $tenant->subscription_expires_at->format('d F Y') }}</li>
            </ul>
        </div>

        <p>Selama periode trial, Anda dapat menggunakan semua fitur Fabriku secara gratis!</p>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/login" class="button">Mulai Sekarang →</a>
        </div>

        <h3>📚 Langkah Selanjutnya:</h3>
        <ol>
            <li>Login ke dashboard Anda</li>
            <li>Lengkapi profil bisnis Anda</li>
            <li>Tambahkan produk dan material</li>
            <li>Mulai kelola produksi Anda!</li>
        </ol>

        <p>Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.</p>

        <p>Salam hangat,<br>
        <strong>Tim Fabriku</strong></p>
    </div>
    
    <div class="footer">
        <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        <p>&copy; {{ date('Y') }} Fabriku. All rights reserved.</p>
    </div>
</body>
</html>
