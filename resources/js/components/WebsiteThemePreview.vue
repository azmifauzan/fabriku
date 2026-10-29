<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{ templateId?: number; previewUrl?: string; title?: string; thumbnail?: boolean; refreshKey?: string }>(), {
    thumbnail: true,
});
const container = ref<HTMLElement | null>(null);
const html = ref('');
const loading = ref(true);
const failed = ref(false);
let controller: AbortController | null = null;
let observer: IntersectionObserver | null = null;
let visible = false;

async function loadPreview() {
    controller?.abort();
    const request = new AbortController();
    controller = request;
    loading.value = true;
    failed.value = false;
    html.value = '';
    try {
        const response = await fetch(props.previewUrl ?? `/website/theme/${props.templateId}/preview`, {
            headers: { Accept: 'text/html' },
            signal: request.signal,
        });
        if (!response.ok || response.redirected) throw new Error('Preview unavailable');
        html.value = await response.text();
    } catch {
        if (!request.signal.aborted) {
            failed.value = true;
            loading.value = false;
        }
    }
}
onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                visible = true;
                observer?.disconnect();
                void loadPreview();
            }
        },
        { rootMargin: '200px' },
    );
    if (container.value) observer.observe(container.value);
});
watch(
    () => [props.previewUrl, props.templateId, props.refreshKey],
    () => {
        if (visible) void loadPreview();
    },
);
onUnmounted(() => {
    controller?.abort();
    observer?.disconnect();
});
</script>

<template>
    <div
        ref="container"
        class="relative overflow-hidden bg-white"
        :class="thumbnail ? 'aspect-[16/10] border-b border-slate-200 dark:border-slate-700' : 'h-[65vh]'"
        :aria-busy="loading"
    >
        <iframe
            v-if="html"
            :srcdoc="html"
            :title="title ?? 'Pratinjau template'"
            sandbox=""
            :tabindex="thumbnail ? -1 : 0"
            :aria-hidden="thumbnail ? true : undefined"
            :class="
                thumbnail ? 'pointer-events-none absolute top-0 left-0 h-[400%] w-[400%] origin-top-left scale-25 border-0' : 'h-full w-full border-0'
            "
            @load="loading = false"
        />
        <p v-if="loading" role="status" class="absolute inset-0 flex items-center justify-center bg-slate-50 p-4 text-sm text-slate-600">
            Memuat pratinjau…
        </p>
        <div v-if="failed" role="alert" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-50 p-4 text-center">
            <p class="text-sm text-slate-700">Pratinjau belum dapat dimuat.</p>
            <button
                type="button"
                class="min-h-11 rounded-lg border border-slate-500 px-4 py-2 text-sm font-semibold text-slate-800"
                @click="loadPreview"
            >
                Coba lagi
            </button>
        </div>
    </div>
</template>
