<script setup lang="ts">
import WebsiteThemePreview from '@/components/WebsiteThemePreview.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    item: { name: string; description: string; isActive: boolean; previewUrl: string; editUrl: string; refreshKey: string; custom: unknown };
}>();
const emit = defineEmits<{ preview: []; activate: []; delete: [] }>();
</script>

<template>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <WebsiteThemePreview :preview-url="item.previewUrl" :title="`Thumbnail ${item.name}`" :refresh-key="item.refreshKey" />
        <div class="space-y-4 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-slate-950 dark:text-white">{{ item.name }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ item.description }}</p>
                </div>
                <span v-if="item.isActive" class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-900">Desain aktif</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                    @click="emit('preview')"
                >
                    Pratinjau
                </button>
                <Link
                    :href="item.editUrl"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                    >Edit HTML</Link
                >
                <button
                    v-if="!item.isActive"
                    type="button"
                    class="min-h-11 rounded-lg bg-teal-800 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-700"
                    @click="emit('activate')"
                >
                    Aktifkan
                </button>
                <button
                    v-if="item.custom && !item.isActive"
                    type="button"
                    class="min-h-11 rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950"
                    @click="emit('delete')"
                >
                    Hapus
                </button>
            </div>
        </div>
    </article>
</template>
