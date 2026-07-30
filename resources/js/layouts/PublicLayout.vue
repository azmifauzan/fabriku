<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <div class="landing min-h-screen bg-slate-50 dark:bg-gray-900 text-gray-800 selection:bg-indigo-600 selection:text-white">
        <nav class="relative z-50 border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-[74px] max-w-[1440px] items-center justify-between px-5 sm:px-8 lg:px-12">
                <Link href="/" aria-label="Fabriku, kembali ke atas" class="flex shrink-0 items-center gap-2 overflow-visible">
                    <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-9 w-14 shrink-0 object-contain" />
                    <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 w-[92px] shrink-0 object-contain object-left" />
                </Link>

                <div class="hidden items-center gap-8 text-[13px] font-bold tracking-[0.12em] uppercase md:flex">
                    <Link href="/#cara-kerja" class="underline-offset-8 hover:underline">Cara kerja</Link>
                    <Link href="/#fitur" class="underline-offset-8 hover:underline">Modul</Link>
                    <Link href="/#harga" class="underline-offset-8 hover:underline">Harga</Link>
                    <Link href="/#faq" class="underline-offset-8 hover:underline">FAQ</Link>
                    <Link href="/blog" class="underline-offset-8 hover:underline">Blog</Link>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <Link href="/login" class="hidden px-2 py-2 text-sm font-bold min-[420px]:inline-flex sm:px-4">Masuk</Link>
                    <Link
                        href="/register"
                        class="group inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-indigo-700 sm:px-5"
                    >
                        Coba gratis
                        <ArrowRight :size="16" class="transition-transform group-hover:translate-x-1" />
                    </Link>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>

        <footer class="bg-white">
            <div class="mx-auto grid max-w-[1440px] gap-x-8 gap-y-3 px-5 py-10 sm:px-8 md:grid-cols-3 md:items-end lg:px-12">
                <div>
                    <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-6 w-[120px] object-contain object-left" />
                    <p class="mt-4 max-w-xs text-sm font-medium text-slate-500">Operasional UMKM dalam satu alur yang bisa dipahami seluruh tim.</p>
                </div>
                <div class="flex flex-wrap gap-5 text-sm font-bold md:justify-center">
                    <Link href="/#fitur" class="hover:underline">Modul</Link>
                    <Link href="/#harga" class="hover:underline">Harga</Link>
                    <Link href="/#faq" class="hover:underline">FAQ</Link>
                    <a href="https://github.com/azmifauzan/fabriku" target="_blank" rel="noopener noreferrer" class="hover:underline">GitHub</a>
                </div>
                <p class="text-sm font-medium text-slate-500 md:text-right">© 2026 Fabriku · Dibuat untuk UMKM Indonesia.</p>

                <div class="hidden md:block" aria-hidden="true"></div>
                <div class="flex flex-wrap gap-5 text-sm font-bold md:justify-center">
                    <Link href="/privasi" class="hover:underline">Privasi</Link>
                    <Link href="/syarat-ketentuan" class="hover:underline">Syarat & Ketentuan</Link>
                </div>
                <p class="text-xs font-medium text-slate-400 md:text-right">
                    Design by
                    <a
                        href="https://satsetui.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-slate-500 hover:text-indigo-600 hover:underline"
                        >SatsetUI</a
                    >
                    and Managed by
                    <a
                        href="https://satsetops.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-slate-500 hover:text-indigo-600 hover:underline"
                        >SatsetOps</a
                    >
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}
</style>
