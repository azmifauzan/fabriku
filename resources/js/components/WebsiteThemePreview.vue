<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{ versionId: number; version: number }>();
const html = ref('');
const loading = ref(true);
const failed = ref(false);
const controller = new AbortController();

async function loadPreview() {
    loading.value = true;
    failed.value = false;
    html.value = '';
    try {
        const response = await fetch(`/website/theme/${props.versionId}/preview`, {
            headers: { Accept: 'text/html' },
            signal: controller.signal,
        });
        if (!response.ok || response.redirected) throw new Error('Preview unavailable');
        html.value = await response.text();
    } catch {
        if (!controller.signal.aborted) {
            failed.value = true;
            loading.value = false;
        }
    }
}

onMounted(loadPreview);
onUnmounted(() => controller.abort());
</script>

<template>
    <div class="relative aspect-[16/10] overflow-hidden border-b border-slate-200 bg-white dark:border-slate-700" :aria-busy="loading">
        <iframe
            v-if="html"
            :srcdoc="html"
            :title="`Thumbnail desain versi ${version}`"
            sandbox=""
            tabindex="-1"
            aria-hidden="true"
            class="pointer-events-none absolute top-0 left-0 h-[400%] w-[400%] origin-top-left scale-25 border-0"
            @load="loading = false"
        />
        <p v-if="loading" role="status" class="absolute inset-0 flex items-center justify-center bg-slate-50 p-4 text-sm text-slate-600">
            Memuat preview desain…
        </p>
        <div v-if="failed" role="alert" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-50 p-4 text-center">
            <p class="text-sm text-slate-700">Preview belum dapat dimuat.</p>
            <button
                type="button"
                class="min-h-11 rounded-lg border border-slate-500 px-4 py-2 text-sm font-semibold text-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-800"
                @click="loadPreview"
            >
                Coba lagi
            </button>
        </div>
    </div>
</template>
