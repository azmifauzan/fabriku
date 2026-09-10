<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaignData['subject'] ?? 'Fitur Unggulan Fabriku' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px 12px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #7c3aed 100%);
            color: #ffffff;
            padding: 36px 30px;
            text-align: center;
        }
        .logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }
        .header p {
            margin: 6px 0 0 0;
            color: #e0e7ff;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .content {
            padding: 32px 28px;
        }
        .badge {
            display: inline-block;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 9999px;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .headline {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 14px;
            line-height: 1.35;
        }
        .greeting {
            font-size: 15px;
            color: #475569;
            margin-bottom: 16px;
        }
        .intro-box {
            background-color: #f8fafc;
            border-left: 4px solid #6366f1;
            padding: 16px 20px;
            border-radius: 0 8px 8px 0;
            margin: 18px 0 24px 0;
            color: #334155;
            font-size: 14.5px;
            line-height: 1.6;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 24px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .benefit-list {
            list-style: none;
            padding: 0;
            margin: 0 0 24px 0;
        }
        .benefit-list li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #334155;
            line-height: 1.5;
        }
        .benefit-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 1px;
            width: 18px;
            height: 18px;
            background: #10b981;
            color: white;
            font-size: 11px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-align: center;
            line-height: 18px;
        }
        .steps-box {
            background: #fdf4ff;
            border: 1px solid #f0abfc;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 20px 0 24px 0;
        }
        .steps-box h3 {
            margin: 0 0 12px 0;
            color: #86198f;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .steps-list {
            margin: 0;
            padding-left: 20px;
            color: #4a044e;
            font-size: 13.5px;
        }
        .steps-list li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .pro-tip-box {
            background: #ecfdf5;
            border: 1px dashed #10b981;
            border-radius: 10px;
            padding: 16px 20px;
            margin: 20px 0;
            font-size: 13.5px;
            color: #065f46;
            line-height: 1.5;
        }
        .pro-tip-box strong {
            color: #047857;
            display: block;
            margin-bottom: 4px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 16px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 34px;
            border-radius: 10px;
            box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);
            transition: all 0.2s ease;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 28px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }
        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-box">
                <img src="{{ config('app.url') }}/images/fabriku-word.png?v=2" alt="Fabriku" height="30" style="display: block; height: 30px; max-height: 30px; filter: brightness(0) invert(1);" />
            </div>
            <h1>Fitur Unggulan Mingguan</h1>
            <p>Tips & Panduan Operasional Bisnis</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Category Badge -->
            <span class="badge">
                {{ $campaignData['category_label'] ?? 'Semua Bisnis' }} • {{ $campaignData['feature_name'] ?? 'Tips Bisnis' }}
            </span>

            <!-- Headline -->
            <h2 class="headline">{{ $campaignData['headline'] ?? 'Tingkatkan Performa Bisnis Anda' }}</h2>

            <!-- Greeting -->
            <p class="greeting">
                Halo <strong>{{ $adminUser->name }}</strong> ({{ $tenant->name }}),
            </p>

            <!-- Intro Box -->
            <div class="intro-box">
                {{ $campaignData['intro'] ?? '' }}
            </div>

            <!-- Key Benefits -->
            @if(!empty($campaignData['benefit_points']))
                <div class="section-title">
                    <span>✨</span> Mengapa Fitur Ini Penting untuk Anda:
                </div>
                <ul class="benefit-list">
                    @foreach($campaignData['benefit_points'] as $benefit)
                        <li>{{ $benefit }}</li>
                    @endforeach
                </ul>
            @endif

            <!-- Step by Step -->
            @if(!empty($campaignData['step_by_step']))
                <div class="steps-box">
                    <h3>🚀 Cara Mencobanya di Fabriku:</h3>
                    <ol class="steps-list">
                        @foreach($campaignData['step_by_step'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif

            <!-- Pro Tip -->
            @if(!empty($campaignData['pro_tip']))
                <div class="pro-tip-box">
                    <strong>💡 Tips Rahasia Konsultan:</strong>
                    {{ $campaignData['pro_tip'] }}
                </div>
            @endif

            <!-- CTA Button -->
            <div class="cta-container">
                <a href="{{ config('app.url') }}{{ $campaignData['cta_path'] ?? '/dashboard' }}" class="cta-button" target="_blank">
                    {{ $campaignData['cta_text'] ?? 'Buka Dashboard Fabriku →' }}
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Fabriku</strong> — Platform Operasional & Produksi Bisnis Anda</p>
            <p>Email ini dikirimkan khusus kepada Admin terdaftar pada <strong>{{ $tenant->name }}</strong>.</p>
            @if(!empty($unsubscribeUrl))
                <p style="margin-top: 14px; font-size: 11px; color: #94a3b8; line-height: 1.5;">
                    Jika Anda tidak ingin menerima tips mingguan & update fitur ini lagi, silakan 
                    <a href="{{ $unsubscribeUrl }}" style="color: #64748b; text-decoration: underline;">berhenti berlangganan (unsubscribe)</a>.
                </p>
            @endif
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} Fabriku. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
