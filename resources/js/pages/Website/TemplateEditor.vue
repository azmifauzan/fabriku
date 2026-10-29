<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{ templateId: number | null; name: string; files: Record<string, string>; revision: string | null; isActive: boolean }>();
const form = useForm({ name: props.name, files: { ...props.files }, revision: props.revision });
const selectedFile = ref('index.html');
const newFilename = ref('');
const fileError = ref('');
const previewError = ref('');
const previewHtml = ref('');
const previewPage = ref('index.html');
const previewing = ref(false);
const saving = ref(false);
const showSaveConfirm = ref(false);
const filenames = computed(() => Object.keys(form.files));
const htmlFiles = computed(() => filenames.value.filter((name) => name.endsWith('.html')));
const errors = computed(() => Object.values(form.errors));
let previewRequest: AbortController | null = null;
let removeNavigationListener: (() => void) | undefined;

function addFile() {
    const name = newFilename.value.trim();
    if (!/^[a-z0-9][a-z0-9-]*\.(html|css)$/.test(name) || Object.keys(form.files).includes(name)) {
        fileError.value = 'Gunakan nama HTML/CSS yang belum ada, dengan huruf kecil, angka, atau tanda hubung.';
        return;
    }
    if (filenames.value.length >= 30) {
        fileError.value = 'Maksimal 30 file.';
        return;
    }
    form.files[name] = name.endsWith('.html') ? '<section>\n    <h1>Halaman baru</h1>\n</section>' : '';
    selectedFile.value = name;
    newFilename.value = '';
    fileError.value = '';
}
function removeFile() {
    if (selectedFile.value === 'index.html') return;
    delete form.files[selectedFile.value];
    selectedFile.value = 'index.html';
    if (!Object.keys(form.files).includes(previewPage.value)) previewPage.value = 'index.html';
}
async function updatePreview() {
    previewRequest?.abort();
    const request = new AbortController();
    previewRequest = request;
    previewing.value = true;
    previewError.value = '';
    try {
        const response = await axios.post(
            '/website/templates/preview',
            { name: form.name, files: form.files, page: previewPage.value },
            {
                headers: { Accept: 'application/json' },
                signal: request.signal,
            },
        );
        previewHtml.value = response.data;
    } catch (error) {
        if (request.signal.aborted) return;
        const messages = axios.isAxiosError(error) ? (error.response?.data?.errors as Record<string, string[]> | undefined) : undefined;
        previewError.value = messages ? Object.values(messages).flat().join(' ') : 'Pratinjau gagal dimuat. Periksa koneksi dan coba lagi.';
    } finally {
        if (!request.signal.aborted) previewing.value = false;
    }
}
function save() {
    showSaveConfirm.value = false;
    saving.value = true;
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.defaults();
        },
        onFinish: () => { saving.value = false; },
    };
    if (props.templateId === null) form.post('/website/templates', options);
    else form.patch(`/website/templates/${props.templateId}`, options);
}
function requestSave() {
    if (props.isActive) showSaveConfirm.value = true;
    else save();
}
function beforeUnload(event: BeforeUnloadEvent) {
    if (form.isDirty) {
        event.preventDefault();
        event.returnValue = '';
    }
}
watch(
    () => props.revision,
    (revision) => {
        form.revision = revision;
        form.defaults('revision', revision);
    },
);
onMounted(() => {
    window.addEventListener('beforeunload', beforeUnload);
    removeNavigationListener = router.on('before', () => {
        if (form.isDirty && !saving.value && !window.confirm('Perubahan belum disimpan. Tinggalkan editor?')) return false;
    });
    void updatePreview();
});
onUnmounted(() => {
    previewRequest?.abort();
    removeNavigationListener?.();
    window.removeEventListener('beforeunload', beforeUnload);
});
</script>

<template>
    <AppLayout>
        <Head :title="`Editor Template · ${name}`" />
        <main class="space-y-5 px-4 py-6 sm:px-6">
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <Link href="/website?tab=desain" class="text-sm font-semibold text-teal-700 underline dark:text-teal-400">← Daftar template</Link>
                    <h1 class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">Editor Template</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Edit HTML dan CSS, periksa pratinjau, lalu simpan perubahan.</p>
                </div>
                <Link
                    href="/website/template-guide"
                    class="min-h-11 rounded-lg border border-slate-300 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200"
                    >Panduan template</Link
                >
            </header>
            <p v-if="isActive" class="rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-950">
                Template ini sedang aktif. Menyimpan perubahan akan memperbarui website yang terbit.
            </p>
            <div v-if="errors.length" role="alert" class="space-y-1 rounded-lg border border-rose-300 bg-rose-50 p-3 text-sm text-rose-900">
                <p v-for="error in errors" :key="error">{{ error }}</p>
            </div>
            <form class="space-y-4" @submit.prevent="requestSave">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div class="w-full sm:max-w-md">
                        <label for="editor-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama template</label>
                        <input
                            id="editor-name"
                            v-model="form.name"
                            required
                            maxlength="100"
                            class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 p-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="min-h-11 rounded-lg bg-teal-800 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan…' : 'Simpan template' }}
                    </button>
                </div>
                <div class="grid min-w-0 gap-5 xl:grid-cols-2">
                    <section class="min-w-0 space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-0 flex-1">
                                <label for="editor-file" class="block text-sm font-medium text-slate-700 dark:text-slate-300">File yang diedit</label
                                ><select
                                    id="editor-file"
                                    v-model="selectedFile"
                                    class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option v-for="filename in filenames" :key="filename">{{ filename }}</option>
                                </select>
                            </div>
                            <button
                                v-if="selectedFile !== 'index.html'"
                                type="button"
                                class="min-h-11 rounded-lg border border-rose-300 px-3 py-2 text-sm text-rose-800 dark:text-rose-300"
                                @click="removeFile"
                            >
                                Hapus file
                            </button>
                        </div>
                        <label for="editor-source" class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >Kode {{ selectedFile }}</label
                        >
                        <textarea
                            id="editor-source"
                            v-model="form.files[selectedFile]"
                            spellcheck="false"
                            wrap="off"
                            class="block h-[55vh] w-full resize-y rounded-lg border border-slate-300 bg-slate-50 p-3 font-mono text-sm leading-6 text-slate-900 focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        ></textarea>
                        <div class="flex flex-wrap gap-2">
                            <label for="new-template-file" class="sr-only">Nama file baru</label
                            ><input
                                id="new-template-file"
                                v-model="newFilename"
                                placeholder="contoh: tentang.html"
                                class="min-h-11 min-w-0 flex-1 rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            /><button
                                type="button"
                                class="min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200"
                                @click="addFile"
                            >
                                Tambah file
                            </button>
                        </div>
                        <p v-if="fileError" role="alert" class="text-sm text-rose-600">{{ fileError }}</p>
                    </section>
                    <section class="min-w-0 space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-0 flex-1">
                                <label for="preview-page" class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >Halaman pratinjau</label
                                ><select
                                    id="preview-page"
                                    v-model="previewPage"
                                    class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option v-for="filename in htmlFiles" :key="filename">{{ filename }}</option>
                                </select>
                            </div>
                            <button
                                type="button"
                                :disabled="previewing"
                                class="min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200"
                                @click="updatePreview"
                            >
                                {{ previewing ? 'Memeriksa…' : 'Perbarui pratinjau' }}
                            </button>
                        </div>
                        <p v-if="previewError" role="alert" class="rounded-lg bg-rose-50 p-3 text-sm text-rose-900">{{ previewError }}</p>
                        <iframe
                            v-if="previewHtml"
                            :srcdoc="previewHtml"
                            sandbox=""
                            title="Pratinjau perubahan template"
                            class="h-[65vh] w-full rounded-lg border border-slate-200 bg-white"
                        ></iframe>
                        <p v-else class="p-6 text-sm text-slate-500">Pratinjau muncul setelah template lolos pemeriksaan.</p>
                    </section>
                </div>
            </form>
            <ConfirmationModal
                :show="showSaveConfirm"
                title="Simpan Template Aktif"
                message="Perubahan akan langsung tampil di website. Simpan sekarang?"
                confirm-text="Simpan perubahan"
                cancel-text="Batal"
                variant="warning"
                @confirm="save"
                @cancel="showSaveConfirm = false"
            />
        </main>
    </AppLayout>
</template>
