<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const mobileMenuOpen = ref(false);
const navigation = [
    { label: 'Cara kerja', href: '/#cara-kerja' },
    { label: 'Fitur', href: '/#fitur' },
    { label: 'Website usaha', href: '/#website' },
    { label: 'Harga', href: '/#harga' },
    { label: 'Blog', href: '/blog' },
];

const closeMenu = () => {
    mobileMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-[#f5f7fa] text-slate-900 selection:bg-indigo-700 selection:text-white">
        <a
            href="#main-content"
            class="sr-only z-[100] rounded-md bg-white px-4 py-3 font-bold text-[#163761] focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700"
        >
            Lewati ke konten utama
        </a>

        <header class="relative z-40 border-b border-slate-200 bg-[#f5f7fa]" @keydown.esc="closeMenu">
            <div class="mx-auto flex min-h-[72px] max-w-7xl items-center justify-between gap-3 px-4 sm:px-7 lg:px-10">
                <Link
                    href="/"
                    aria-label="Fabriku, kembali ke beranda"
                    class="flex min-h-11 shrink-0 items-center gap-1.5 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-700"
                    @click="closeMenu"
                >
                    <img src="/images/fabriku-logo-only.png?v=3" alt="" class="h-8 w-10 shrink-0 object-contain" />
                    <img src="/images/fabriku-word.png?v=3" alt="Fabriku" class="h-4 w-[76px] shrink-0 object-contain object-left" />
                </Link>

                <nav aria-label="Navigasi utama" class="hidden items-center gap-5 text-sm font-semibold lg:flex">
                    <Link
                        v-for="item in navigation"
                        :key="item.label"
                        :href="item.href"
                        class="rounded-sm px-1 py-3 text-slate-700 hover:text-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                    <Link
                        href="/login"
                        class="hidden min-h-11 items-center rounded-md px-3 text-sm font-semibold text-[#163761] hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 sm:inline-flex"
                    >
                        Masuk
                    </Link>
                    <Link
                        href="/register"
                        class="inline-flex min-h-11 items-center justify-center rounded-md bg-indigo-700 px-3 text-xs font-bold whitespace-nowrap text-white hover:bg-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 sm:px-4 sm:text-sm"
                    >
                        Coba gratis
                    </Link>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 text-xs font-bold text-[#163761] hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700 lg:hidden"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="mobile-menu"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        {{ mobileMenuOpen ? 'Tutup' : 'Menu' }}
                    </button>
                </div>
            </div>

            <nav v-if="mobileMenuOpen" id="mobile-menu" aria-label="Navigasi seluler" class="border-t border-slate-200 bg-white px-4 py-3 lg:hidden">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    class="flex min-h-11 items-center border-b border-slate-100 px-2 text-sm font-semibold text-[#163761] last:border-0 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-indigo-700"
                    @click="closeMenu"
                >
                    {{ item.label }}
                </Link>
                <Link
                    href="/login"
                    class="mt-2 flex min-h-11 items-center px-2 text-sm font-semibold text-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-indigo-700"
                    @click="closeMenu"
                >
                    Masuk ke Fabriku
                </Link>
            </nav>
        </header>

        <main id="main-content">
            <slot />
        </main>

        <footer class="border-t border-slate-200 bg-[#eef2f7]">
            <div class="mx-auto grid max-w-7xl gap-8 px-5 py-9 sm:px-8 md:grid-cols-[1fr_auto] md:items-end lg:px-10">
                <div>
                    <Link
                        href="/"
                        aria-label="Fabriku, beranda"
                        class="inline-flex min-h-11 items-center gap-2 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                    >
                        <img src="/images/fabriku-logo-only.png?v=3" alt="" class="h-8 w-11 object-contain" />
                        <img src="/images/fabriku-word.png?v=3" alt="Fabriku" class="h-4 w-20 object-contain object-left" />
                    </Link>
                    <p class="mt-3 max-w-md text-sm leading-6 text-slate-700">
                        Produksi, stok, dan website usaha untuk UMKM yang menjual produk, jasa, atau keduanya.
                    </p>
                </div>

                <nav aria-label="Tautan footer" class="flex flex-wrap gap-x-5 gap-y-1 text-sm font-semibold">
                    <Link
                        href="/#fitur"
                        class="min-h-11 py-3 text-slate-700 hover:text-indigo-800 focus-visible:outline-2 focus-visible:outline-indigo-700"
                        >Fitur</Link
                    >
                    <Link
                        href="/#website"
                        class="min-h-11 py-3 text-slate-700 hover:text-indigo-800 focus-visible:outline-2 focus-visible:outline-indigo-700"
                        >Website usaha</Link
                    >
                    <Link
                        href="/blog"
                        class="min-h-11 py-3 text-slate-700 hover:text-indigo-800 focus-visible:outline-2 focus-visible:outline-indigo-700"
                        >Blog</Link
                    >
                    <Link
                        href="/privasi"
                        class="min-h-11 py-3 text-slate-700 hover:text-indigo-800 focus-visible:outline-2 focus-visible:outline-indigo-700"
                        >Privasi</Link
                    >
                    <Link
                        href="/syarat-ketentuan"
                        class="min-h-11 py-3 text-slate-700 hover:text-indigo-800 focus-visible:outline-2 focus-visible:outline-indigo-700"
                        >Syarat</Link
                    >
                </nav>

                <p class="border-t border-slate-300 pt-5 text-xs text-slate-600 md:col-span-2">
                    © {{ new Date().getFullYear() }} Fabriku. Dibuat untuk UMKM Indonesia.
                </p>
            </div>
        </footer>
    </div>
</template>
