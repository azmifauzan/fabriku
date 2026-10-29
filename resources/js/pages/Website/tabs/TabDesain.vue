<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import Modal from '@/components/Modal.vue';
import WebsiteThemePreview from '@/components/WebsiteThemePreview.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Template = { id: number; name: string; source: string; isActive: boolean; updatedAt: string | null };
defineProps<{
    site: { active_theme_version: { id: number; theme?: { builtin_preset?: string } } | null };
    templates: Template[];
    canManage: boolean;
    csrfToken: string;
}>();
const page = usePage();
const satsetuiError = computed(() => (page.props.errors as Record<string, string> | undefined)?.satsetui);
const uploadForm = useForm<{ name: string; template: File | null }>({ name: '', template: null });
const fileInput = ref<HTMLInputElement | null>(null);
const preview = ref<{ name: string; url: string } | null>(null);
const activation = ref<{ name: string; url: string } | null>(null);
const activating = ref(false);
const presets = [
    { id: 'ruang', name: 'Ruang', hint: 'Sederhana dan langsung ke inti' },
    { id: 'etalase', name: 'Etalase', hint: 'Warna kuat untuk produk unggulan' },
    { id: 'studio', name: 'Studio', hint: 'Tipografi besar untuk jasa dan karya' },
];

function uploadTemplate() {
    uploadForm.post('/website/theme/upload', {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}
function activate() {
    if (!activation.value || activating.value) return;
    activating.value = true;
    router.post(
        activation.value.url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                activation.value = null;
            },
            onFinish: () => {
                activating.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="space-y-6">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-950 dark:text-white">Template Website</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Pilih tampilan, unggah desain sendiri, atau ubah HTML dan CSS melalui editor.
                </p>
            </div>
            <Link
                href="/website/template-guide"
                class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200"
                >Panduan template</Link
            >
        </header>
        <p v-if="satsetuiError" role="alert" class="rounded-lg border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900">{{ satsetuiError }}</p>
        <template v-if="canManage">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="preset in presets"
                    :key="preset.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                >
                    <WebsiteThemePreview :preview-url="`/website/theme/preset/${preset.id}/preview`" :title="`Thumbnail ${preset.name}`" />
                    <div class="space-y-4 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-slate-950 dark:text-white">{{ preset.name }}</h3>
                                <p class="mt-1 text-xs text-slate-500">Fabriku · {{ preset.hint }}</p>
                            </div>
                            <span
                                v-if="site.active_theme_version?.theme?.builtin_preset === preset.id"
                                class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-900"
                                >Aktif</span
                            >
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                                @click="preview = { name: preset.name, url: `/website/theme/preset/${preset.id}/preview` }"
                            >
                                Pratinjau
                            </button>
                            <Link
                                :href="`/website/templates/presets/${preset.id}/edit`"
                                class="min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                                >Edit HTML</Link
                            >
                            <button
                                v-if="site.active_theme_version?.theme?.builtin_preset !== preset.id"
                                type="button"
                                class="min-h-11 rounded-lg bg-teal-800 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-700"
                                @click="activation = { name: preset.name, url: `/website/templates/presets/${preset.id}/activate` }"
                            >
                                Aktifkan
                            </button>
                        </div>
                    </div>
                </article>
                <article
                    v-for="item in templates"
                    :key="item.id"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                >
                    <WebsiteThemePreview :template-id="item.id" :title="`Thumbnail ${item.name}`" :refresh-key="item.updatedAt ?? ''" />
                    <div class="space-y-4 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-slate-950 dark:text-white">{{ item.name }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ item.source === 'satsetui' ? 'Satsetui' : 'Template kustom' }}</p>
                            </div>
                            <span v-if="item.isActive" class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-900"
                                >Aktif</span
                            >
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                                @click="preview = { name: item.name, url: `/website/theme/${item.id}/preview` }"
                            >
                                Pratinjau
                            </button>
                            <Link
                                :href="`/website/templates/${item.id}/edit`"
                                class="min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                                >Edit HTML</Link
                            >
                            <button
                                v-if="!item.isActive"
                                type="button"
                                class="min-h-11 rounded-lg bg-teal-800 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-700"
                                @click="activation = { name: item.name, url: `/website/templates/${item.id}/activate` }"
                            >
                                Aktifkan
                            </button>
                        </div>
                    </div>
                </article>
            </div>
            <section class="grid gap-6 lg:grid-cols-2">
                <form
                    class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                    @submit.prevent="uploadTemplate"
                >
                    <div>
                        <h3 class="font-semibold text-slate-950 dark:text-white">Unggah Template ZIP</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Sertakan index.html, halaman HTML lain, dan CSS. Maksimal 2 MB. Template diperiksa sebelum masuk ke daftar.
                        </p>
                    </div>
                    <div>
                        <label for="template-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama template</label>
                        <input
                            id="template-name"
                            v-model="uploadForm.name"
                            required
                            maxlength="100"
                            class="mt-1 block min-h-11 w-full rounded-lg border border-slate-300 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p v-if="uploadForm.errors.name" class="mt-1 text-sm text-rose-600" role="alert">{{ uploadForm.errors.name }}</p>
                    </div>
                    <div>
                        <label for="template-file" class="block text-sm font-medium text-slate-700 dark:text-slate-300">File ZIP</label>
                        <input
                            id="template-file"
                            ref="fileInput"
                            type="file"
                            accept=".zip,application/zip"
                            required
                            class="mt-2 block min-h-11 w-full text-sm text-slate-700 dark:text-slate-300"
                            @change="uploadForm.template = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                        <p v-if="uploadForm.errors.template" class="mt-1 text-sm text-rose-600" role="alert">{{ uploadForm.errors.template }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="uploadForm.processing"
                        class="min-h-11 rounded-lg bg-teal-800 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 disabled:opacity-50"
                    >
                        {{ uploadForm.processing ? 'Memeriksa…' : 'Periksa dan simpan' }}
                    </button>
                </form>
                <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="font-semibold text-slate-950 dark:text-white">Buat di Satsetui</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Buat desain di Satsetui dan kirim ke Fabriku. Hasilnya masuk ke daftar template untuk dipratinjau, diedit, dan diaktifkan.
                    </p>
                    <form action="/website/satsetui/launch" method="post" target="_blank" rel="noopener">
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <button type="submit" class="min-h-11 rounded-lg bg-teal-800 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                            Buka Satsetui ↗
                        </button>
                    </form>
                </div>
            </section>
        </template>
        <p v-else class="text-sm text-slate-600 dark:text-slate-400">Pengelolaan template tersedia untuk pemilik dan pengelola usaha.</p>
        <Modal :show="preview !== null" @close="preview = null">
            <div v-if="preview" class="p-5">
                <h3 class="mb-4 pr-8 font-semibold text-slate-950 dark:text-white">{{ preview.name }}</h3>
                <WebsiteThemePreview :preview-url="preview.url" :title="`Pratinjau ${preview.name}`" :thumbnail="false" />
            </div>
        </Modal>
        <ConfirmationModal
            :show="activation !== null"
            title="Aktifkan Template"
            :message="`Gunakan ${activation?.name ?? ''} untuk website? Tampilan website yang terbit akan langsung berubah.`"
            confirm-text="Aktifkan"
            cancel-text="Batal"
            variant="warning"
            @confirm="activate"
            @cancel="activation = null"
        />
    </div>
</template>
