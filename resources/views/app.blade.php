<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Fabriku') }} — Operasional UMKM dalam satu alur</title>
        <meta name="description" content="Fabriku adalah aplikasi manajemen produksi dan penjualan untuk UMKM Indonesia (garment, makanan, kerajinan, kosmetik, retail, produksi rumahan, jasa): kelola bahan baku, produksi, stok, dan penjualan dalam satu alur.">
        <meta property="og:title" content="Fabriku — Operasional UMKM dalam satu alur">
        <meta property="og:description" content="Aplikasi manajemen produksi dan penjualan untuk UMKM Indonesia: bahan baku, produksi, stok, dan penjualan dalam satu alur.">
        <meta property="og:type" content="website">

        <!-- Apply saved theme before app loads (prevents FOUC) -->
        <script>
            (function () {
                try {
                    const theme = localStorage.getItem('fabriku-theme');

                    if (theme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
