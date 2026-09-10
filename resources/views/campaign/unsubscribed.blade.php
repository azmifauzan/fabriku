<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berhenti Berlangganan — Fabriku</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
            max-width: 480px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
        }
        .logo-wrap {
            margin-bottom: 24px;
        }
        .logo-wrap img {
            height: 36px;
            width: auto;
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: #f1f5f9;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .user-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #334155;
            text-align: left;
        }
        .user-box div {
            margin-bottom: 4px;
        }
        .user-box div:last-child {
            margin-bottom: 0;
        }
        .user-box strong {
            color: #0f172a;
        }
        .btn-resubscribe {
            display: inline-block;
            width: 100%;
            padding: 12px 20px;
            background-color: #4f46e5;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
        }
        .btn-resubscribe:hover {
            background-color: #4338ca;
        }
        .footer-link {
            margin-top: 20px;
            font-size: 13px;
        }
        .footer-link a {
            color: #64748b;
            text-decoration: underline;
        }
        .footer-link a:hover {
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap">
            <a href="/">
                <img src="/images/fabriku-word.png?v=2" alt="Fabriku" />
            </a>
        </div>

        <div class="icon-circle">
            ✉️
        </div>

        <h1>Berhenti Berlangganan</h1>
        <p>
            Anda telah berhasil berhenti berlangganan dari email tips mingguan dan informasi fitur Fabriku.
        </p>

        <div class="user-box">
            <div><strong>Email:</strong> {{ $user->email }}</div>
            <div><strong>Nama:</strong> {{ $user->name }}</div>
            @if($tenant)
                <div><strong>Bisnis:</strong> {{ $tenant->name }}</div>
            @endif
        </div>

        <p style="font-size: 13px; margin-bottom: 20px;">
            Apakah ini tidak sengaja? Anda dapat berlangganan kembali kapan saja melalui tombol di bawah:
        </p>

        <form action="{{ $resubscribeUrl }}" method="POST">
            @csrf
            <button type="submit" class="btn-resubscribe">
                Berlangganan Kembali (Resubscribe)
            </button>
        </form>

        <div class="footer-link">
            <a href="/">← Kembali ke Halaman Utama Fabriku</a>
        </div>
    </div>
</body>
</html>
