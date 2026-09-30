<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';

defineProps<{
    title: string;
    updated: string;
}>();
</script>

<template>
    <PublicLayout>
        <article class="mx-auto max-w-4xl px-5 py-12 sm:px-8 sm:py-16 lg:px-10">
            <header class="border-b border-slate-300 pb-7">
                <p class="text-sm font-bold text-indigo-800">Informasi Fabriku</p>
                <h1 class="mt-3 max-w-3xl text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">
                    {{ title }}
                </h1>
                <p class="mt-4 text-sm text-slate-700">Terakhir diperbarui: {{ updated }}</p>
            </header>

            <div class="legal-content mt-9 space-y-8 text-base leading-7 text-slate-800">
                <slot />
            </div>
        </article>
    </PublicLayout>
</template>

<style scoped>
.legal-content :deep(h2) {
    color: #163761;
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.legal-content :deep(p + h2),
.legal-content :deep(ul + h2) {
    margin-top: 0;
}

.legal-content :deep(ul) {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    list-style-type: disc;
    padding-left: 1.25rem;
}

.legal-content :deep(a) {
    color: #3730a3;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.legal-content :deep(section) {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
</style>
