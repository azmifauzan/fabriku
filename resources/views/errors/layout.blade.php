<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Fabriku</title>
    
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    <!-- Google Fonts / Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>

    <script>
        // Apply saved theme before page load
        (function () {
            try {
                const theme = localStorage.getItem('fabriku-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
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
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden transition-colors duration-300">
    <!-- Background grid -->
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:14px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)]"></div>

    <!-- Background glowing spots -->
    <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-indigo-500/10 dark:bg-indigo-500/5 blur-3xl -z-10 animate-pulse-slow"></div>
    <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-80 h-80 rounded-full bg-purple-500/10 dark:bg-purple-500/5 blur-3xl -z-10 animate-pulse-slow"></div>

    <!-- Theme Toggle Button -->
    <div class="absolute top-4 right-4 z-50">
        <button onclick="toggleTheme()" class="p-3 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-slate-200/50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 shadow-md backdrop-blur-sm" aria-label="Toggle theme">
            <!-- Sun Icon -->
            <svg id="theme-sun" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <!-- Moon Icon -->
            <svg id="theme-moon" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div>

    <!-- Header Navigation -->
    <header class="py-6 px-8 flex justify-center md:justify-start">
        <a href="/" class="flex items-center gap-2 group transition-transform duration-200 hover:scale-[1.02]">
            <img src="/images/fabriku-logo-only.png?v=2" alt="Fabriku Logo" class="h-9 w-9 object-contain" />
            <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-7 object-contain dark:invert" />
        </a>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-xl w-full">
            <!-- Glassmorphic Card -->
            <div class="relative overflow-hidden rounded-3xl border border-slate-200/60 dark:border-slate-800/60 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl p-8 md:p-12 shadow-2xl text-center flex flex-col items-center">
                
                <!-- Glow highlight on card -->
                <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>

                <!-- Error Code -->
                <div class="text-7xl md:text-8xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 select-none mb-1">
                    @yield('code')
                </div>

                <!-- SVG Illustration with float effect -->
                <div class="w-48 h-48 md:w-56 md:h-56 my-2 flex items-center justify-center animate-float">
                    @yield('illustration')
                </div>

                <!-- Title/Heading -->
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight mb-3">
                    @yield('message')
                </h1>

                <!-- Description -->
                <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base leading-relaxed max-w-sm mb-8">
                    @yield('description')
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <button onclick="window.history.back()" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-semibold text-sm transition-all duration-200 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </button>
                    <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/35 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-400 dark:text-slate-600">
        &copy; {{ date('Y') }} Fabriku. Hak Cipta Dilindungi.
    </footer>

    <!-- Dark Mode Toggle Script -->
    <script>
        const sunIcon = document.getElementById('theme-sun');
        const moonIcon = document.getElementById('theme-moon');

        function updateToggleIcons() {
            if (document.documentElement.classList.contains('dark')) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('fabriku-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('fabriku-theme', 'dark');
            }
            updateToggleIcons();
        }

        // Initialize icons
        updateToggleIcons();
    </script>
</body>
</html>
