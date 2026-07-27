<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: string;
    updated: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-gray-800">
        <nav class="border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-[74px] max-w-[880px] items-center justify-between px-5 sm:px-8">
                <Link href="/" aria-label="Fabriku, kembali ke beranda" class="flex shrink-0 items-center gap-2">
                    <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-9 w-14 shrink-0 object-contain" />
                    <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 w-[92px] shrink-0 object-contain object-left" />
                </Link>
                <Link href="/" class="text-sm font-bold hover:underline">← Kembali ke beranda</Link>
            </div>
        </nav>

        <main class="mx-auto max-w-[880px] px-5 py-14 sm:px-8 sm:py-20">
            <p class="mb-3 text-xs font-black tracking-[0.18em] text-indigo-600 uppercase">Fabriku</p>
            <h1 class="text-4xl font-black tracking-[-0.04em] uppercase sm:text-5xl">{{ title }}</h1>
            <p class="mt-3 text-sm font-medium text-slate-500">Terakhir diperbarui: {{ updated }}</p>

            <div class="legal-content mt-12 space-y-8 text-[15px] leading-relaxed font-medium text-slate-700">
                <slot />
            </div>
        </main>

        <footer class="border-t border-gray-200 bg-white">
            <div class="mx-auto max-w-[880px] px-5 py-8 text-sm font-medium text-slate-500 sm:px-8">
                © {{ new Date().getFullYear() }} Fabriku · Dibuat untuk UMKM Indonesia.
            </div>
        </footer>
    </div>
</template>

<style scoped>
.legal-content :deep(h2) {
    font-size: 1.375rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    color: #0f172a;
}

.legal-content :deep(p + h2),
.legal-content :deep(ul + h2) {
    margin-top: 0;
}

.legal-content :deep(ul) {
    list-style-type: disc;
    padding-left: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.legal-content :deep(a) {
    color: #4f46e5;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.legal-content :deep(section) {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
</style>
