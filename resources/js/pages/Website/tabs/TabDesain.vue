<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import Modal from '@/components/Modal.vue';
import WebsiteThemePreview from '@/components/WebsiteThemePreview.vue';
import { formatDate } from '@/lib/formatters';
import { router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Eye,
    History,
    Layout,
    Palette,
    Sparkles,
    Trash2,
    Undo2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type ThemeVersion = {
    id: number;
    version: number;
    source: string;
    created_at: string;
    created_by?: number | null;
    creator?: { id: number; name: string } | null;
    theme?: { preset?: string; css_variables?: Record<string, string> };
    sections?: Array<{ type: string; variables?: Record<string, string> }>;
};

type DraftThemeVersion = {
    id: number;
    version: number;
    pageCount: number;
};

type Site = {
    id: number;
    slug: string;
    status: string;
    profile: Record<string, string>;
    active_theme_version: ThemeVersion | null;
    theme_versions: ThemeVersion[];
};

type LastManualTheme = {
    preset: string;
    css_variables: Record<string, string>;
    hero_title: string;
    hero_subtitle: string;
} | null;

const props = defineProps<{
    site: Site;
    draftThemeVersion: DraftThemeVersion | null;
    lastManualTheme: LastManualTheme;
    csrfToken: string;
}>();

const page = usePage();
const satsetuiError = computed(() => (page.props.errors as Record<string, string> | undefined)?.satsetui);

// Modals state
const previewingVersion = ref<{ id: number; version: number } | null>(null);
const restoringVersion = ref<ThemeVersion | null>(null);
const showDiscardDraftConfirm = ref(false);
const showPublishDraftConfirm = ref(false);
const showSaveFabrikuConfirm = ref(false);

// Active theme details
const activeVersion = computed(() => props.site.active_theme_version);
const activeSourceLabel = computed(() => {
    if (!activeVersion.value) return 'Belum ada desain';
    return activeVersion.value.source === 'satsetui' ? 'Desain Satsetui' : 'Desain Fabriku';
});

// Prefill form from lastManualTheme or active manual theme or defaults
const initialPreset = props.lastManualTheme?.preset ?? (activeVersion.value?.source === 'manual' ? activeVersion.value.theme?.preset : null) ?? 'ruang';
const initialColors = props.lastManualTheme?.css_variables ?? (activeVersion.value?.source === 'manual' ? activeVersion.value.theme?.css_variables : null) ?? {};
const initialHeroTitle = props.lastManualTheme?.hero_title ?? props.site.profile?.name ?? '';
const initialHeroSubtitle = props.lastManualTheme?.hero_subtitle ?? props.site.profile?.description ?? '';

const themeForm = useForm({
    preset: initialPreset,
    primary: initialColors['--fb-primary'] ?? '#2563eb',
    accent: initialColors['--fb-accent'] ?? '#3b82f6',
    background: initialColors['--fb-bg'] ?? '#ffffff',
    text: initialColors['--fb-text'] ?? '#0f172a',
    hero_title: initialHeroTitle,
    hero_subtitle: initialHeroSubtitle,
});

function confirmSaveFabriku() {
    // Show confirm if site is published or if currently active theme is Satsetui
    if (props.site.status === 'published' || activeVersion.value?.source === 'satsetui') {
        showSaveFabrikuConfirm.value = true;
        return;
    }
    executeSaveFabriku();
}

function executeSaveFabriku() {
    showSaveFabrikuConfirm.value = false;
    themeForm.post('/website/theme', {
        preserveScroll: true,
    });
}

function confirmRestore(version: ThemeVersion) {
    restoringVersion.value = version;
}

function executeRestore() {
    if (!restoringVersion.value) return;
    router.post(`/website/theme/${restoringVersion.value.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            restoringVersion.value = null;
        },
    });
}

function executePublishDraft() {
    showPublishDraftConfirm.value = false;
    router.post('/website/satsetui/publish-draft', {}, { preserveScroll: true });
}

function executeDiscardDraft() {
    showDiscardDraftConfirm.value = false;
    router.post('/website/satsetui/discard-draft', {}, { preserveScroll: true });
}

const presets = [
    { id: 'ruang', name: 'Ruang', hint: 'Sederhana, rapi, langsung ke inti' },
    { id: 'etalase', name: 'Etalase', hint: 'Warna kuat untuk produk unggulan' },
    { id: 'studio', name: 'Studio', hint: 'Tipografi besar untuk jasa dan karya' },
];
</script>

<template>
    <div class="space-y-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Desain Website</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Pilih tampilan yang paling sesuai untuk website usaha Anda. Anda dapat berpindah antara desain Fabriku dan Satsetui kapan saja.
            </p>
        </div>

        <!-- Banner Draf Satsetui Menunggu Penerapan -->
        <div
            v-if="draftThemeVersion"
            role="status"
            class="flex flex-col gap-4 rounded-xl border border-amber-300 bg-amber-50/80 p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-amber-900 dark:bg-amber-950/40"
        >
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                    <strong class="text-sm font-semibold text-amber-950 dark:text-amber-100">
                        Draf Desain Satsetui Menunggu Penerapan (v{{ draftThemeVersion.version }})
                    </strong>
                </div>
                <p class="text-xs text-amber-900 dark:text-amber-200">
                    Website Anda tetap menggunakan desain lama sampai draf ini diterapkan. {{ draftThemeVersion.pageCount }} halaman tambahan akan ikut aktif.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-amber-400 bg-white px-3 py-2 text-xs font-semibold text-amber-950 shadow-2xs hover:bg-amber-50 dark:border-amber-700 dark:bg-slate-900 dark:text-amber-200 dark:hover:bg-slate-800"
                    @click="previewingVersion = { id: draftThemeVersion.id, version: draftThemeVersion.version }"
                >
                    <Eye class="h-3.5 w-3.5" />
                    Lihat Draf
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-800 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600"
                    @click="showPublishDraftConfirm = true"
                >
                    <CheckCircle2 class="h-3.5 w-3.5" />
                    Terapkan Draf
                </button>

                <button
                    type="button"
                    title="Buang draf ini (tetap ada di riwayat)"
                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-2 text-xs font-medium text-amber-900 hover:bg-amber-100/70 dark:text-amber-300 dark:hover:bg-amber-900/40"
                    @click="showDiscardDraftConfirm = true"
                >
                    <Trash2 class="h-3.5 w-3.5 text-amber-700 dark:text-amber-400" />
                    Buang
                </button>
            </div>
        </div>

        <!-- Kartu Desain Aktif -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400">Desain Aktif Saat Ini</p>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div
                        :class="[
                            'grid h-10 w-10 place-items-center rounded-xl',
                            activeVersion?.source === 'satsetui'
                                ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                                : 'bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300',
                        ]"
                    >
                        <Sparkles v-if="activeVersion?.source === 'satsetui'" class="h-5 w-5" />
                        <Layout v-else class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ activeSourceLabel }}
                                <span v-if="activeVersion">v{{ activeVersion.version }}</span>
                            </h3>
                            <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                Sedang Aktif
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Dibuat {{ activeVersion ? formatDate(activeVersion.created_at) : '-' }}
                            <span v-if="activeVersion?.creator"> oleh {{ activeVersion.creator.name }}</span>
                        </p>
                    </div>
                </div>

                <button
                    v-if="activeVersion"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    @click="previewingVersion = { id: activeVersion.id, version: activeVersion.version }"
                >
                    <Eye class="h-3.5 w-3.5" />
                    Pratinjau Desain Aktif
                </button>
            </div>
        </div>

        <!-- Error Satsetui jika ada -->
        <p
            v-if="satsetuiError"
            role="alert"
            class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-100"
        >
            {{ satsetuiError }}
        </p>

        <!-- Dua Pilihan Setara: Satsetui & Fabriku -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Pilihan 1: Desain Satsetui -->
            <div class="flex flex-col justify-between rounded-xl border border-purple-200 bg-purple-50/30 p-6 shadow-xs dark:border-purple-900/50 dark:bg-purple-950/20">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-purple-100 px-2.5 py-1 text-xs font-bold text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                            Pilihan A
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Desain Satsetui</h3>
                    </div>

                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Kustomisasi bebas dengan wizard template AI Satsetui. Halaman tambahan, gambar, dan tata letak dapat disesuaikan sesuka hati di editor visual.
                    </p>

                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                        <li class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-purple-600 shrink-0" />
                            Cocok untuk branding unik dan layout yang dinamis
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-purple-600 shrink-0" />
                            Mendukung halaman tambahan (Tentang Kami, Kontak, dll.)
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckCircle2 class="h-4 w-4 text-purple-600 shrink-0" />
                            Hasilnya otomatis masuk sebagai draf sebelum diterapkan
                        </li>
                    </ul>
                </div>

                <div class="pt-6">
                    <form action="/website/satsetui/launch" method="post" target="_blank" rel="noopener">
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white shadow-2xs hover:bg-purple-800 dark:bg-purple-800 dark:hover:bg-purple-700"
                        >
                            <Sparkles class="h-4 w-4" />
                            Buka Wizard Satsetui ↗
                        </button>
                    </form>
                    <p class="mt-2 text-center text-xs text-slate-500">Satsetui akan terbuka di tab baru.</p>
                </div>
            </div>

            <!-- Pilihan 2: Desain Fabriku -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center gap-2">
                    <span class="rounded-md bg-teal-100 px-2.5 py-1 text-xs font-bold text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                        Pilihan B
                    </span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Desain Fabriku</h3>
                </div>

                <p class="mb-5 text-sm text-slate-600 dark:text-slate-400">
                    Desain bawaan Fabriku yang bersih, cepat, dan siap pakai langsung dengan katalog produk dan layanan usaha Anda.
                </p>

                <form class="space-y-4" @submit.prevent="confirmSaveFabriku">
                    <!-- Preset Pilihan -->
                    <fieldset class="space-y-1.5">
                        <legend class="text-xs font-semibold text-slate-700 uppercase dark:text-slate-300">Tata Letak Awal</legend>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <label
                                v-for="preset in presets"
                                :key="preset.id"
                                :class="[
                                    'flex cursor-pointer flex-col gap-1 rounded-lg border p-3 text-xs transition-all',
                                    themeForm.preset === preset.id
                                        ? 'border-teal-600 bg-teal-50/50 ring-1 ring-teal-600 dark:border-teal-500 dark:bg-teal-950/40 dark:ring-teal-500'
                                        : 'border-slate-200 hover:border-slate-300 dark:border-slate-700',
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <strong class="font-semibold text-slate-900 dark:text-white">{{ preset.name }}</strong>
                                    <input v-model="themeForm.preset" type="radio" :value="preset.id" class="text-teal-600 focus:ring-teal-500" />
                                </div>
                                <span class="text-slate-500 dark:text-slate-400">{{ preset.hint }}</span>
                            </label>
                        </div>
                    </fieldset>

                    <!-- Judul & Subtitle -->
                    <div>
                        <label for="hero_title" class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                            Judul Utama Halaman Depan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="hero_title"
                            v-model="themeForm.hero_title"
                            type="text"
                            required
                            maxlength="120"
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p v-if="themeForm.errors.hero_title" class="mt-1 text-xs text-rose-600">{{ themeForm.errors.hero_title }}</p>
                    </div>

                    <div>
                        <label for="hero_subtitle" class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                            Kalimat Pembuka
                        </label>
                        <textarea
                            id="hero_subtitle"
                            v-model="themeForm.hero_subtitle"
                            rows="2"
                            maxlength="300"
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                        <p v-if="themeForm.errors.hero_subtitle" class="mt-1 text-xs text-rose-600">{{ themeForm.errors.hero_subtitle }}</p>
                    </div>

                    <!-- Warna Tema -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                            Palet Warna Website
                        </label>
                        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                v-for="color in [
                                    { key: 'primary', label: 'Warna Utama' },
                                    { key: 'accent', label: 'Warna Aksen' },
                                    { key: 'background', label: 'Warna Latar' },
                                    { key: 'text', label: 'Warna Teks' },
                                ]"
                                :key="color.key"
                                class="space-y-1"
                            >
                                <span class="block text-xs text-slate-500">{{ color.label }}</span>
                                <div class="flex items-center gap-1.5">
                                    <input
                                        v-model="(themeForm as any)[color.key]"
                                        type="color"
                                        class="h-8 w-8 cursor-pointer rounded-md border border-slate-300 p-0.5"
                                    />
                                    <input
                                        v-model="(themeForm as any)[color.key]"
                                        type="text"
                                        maxlength="7"
                                        class="w-full rounded-md border border-slate-300 px-2 py-1 font-mono text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3">
                        <button
                            type="submit"
                            :disabled="themeForm.processing"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-semibold text-white shadow-2xs hover:bg-teal-700 disabled:opacity-50 dark:bg-teal-700 dark:hover:bg-teal-600"
                        >
                            <Palette class="h-4 w-4" />
                            Simpan & Terapkan Desain Fabriku
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Riwayat Versi Desain -->
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                        <History class="h-5 w-5 text-teal-700 dark:text-teal-400" />
                        Riwayat Versi Desain
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Setiap perubahan disimpan sebagai versi baru. Anda dapat mempratinjau atau kembali ke versi kapan saja.
                    </p>
                </div>
            </div>

            <div v-if="site.theme_versions.length === 0" class="py-8 text-center text-slate-500">
                <p class="text-sm">Belum ada riwayat versi desain.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase dark:border-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="py-3 pr-4 pl-1">Versi</th>
                            <th class="px-4 py-3">Sumber Desain</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Pembuat</th>
                            <th class="px-4 py-3">Tanggal Dibuat</th>
                            <th class="py-3 pr-1 pl-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="ver in site.theme_versions" :key="ver.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                            <!-- Versi -->
                            <td class="py-3.5 pr-4 pl-1 font-bold text-slate-900 dark:text-white">
                                v{{ ver.version }}
                            </td>

                            <!-- Sumber -->
                            <td class="px-4 py-3.5">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-md px-2.5 py-0.5 text-xs font-semibold',
                                        ver.source === 'satsetui'
                                            ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300'
                                            : 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300',
                                    ]"
                                >
                                    {{ ver.source === 'satsetui' ? 'Satsetui' : 'Fabriku' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5">
                                <span
                                    v-if="ver.id === activeVersion?.id"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                                >
                                    Sedang Aktif
                                </span>
                                <span
                                    v-else-if="ver.id === draftThemeVersion?.id"
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    Draf Satsetui
                                </span>
                                <span v-else class="text-xs text-slate-400">
                                    Arsip
                                </span>
                            </td>

                            <!-- Pembuat -->
                            <td class="px-4 py-3.5 text-xs">
                                {{ ver.creator?.name ?? '-' }}
                            </td>

                            <!-- Tanggal -->
                            <td class="px-4 py-3.5 text-xs text-slate-500">
                                {{ formatDate(ver.created_at) }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 pr-1 pl-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        @click="previewingVersion = { id: ver.id, version: ver.version }"
                                    >
                                        <Eye class="h-3 w-3" />
                                        Pratinjau
                                    </button>

                                    <button
                                        v-if="ver.id !== activeVersion?.id"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-md bg-teal-800 px-2.5 py-1 text-xs font-semibold text-white shadow-2xs hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-600"
                                        @click="confirmRestore(ver)"
                                    >
                                        <Undo2 class="h-3 w-3" />
                                        Gunakan versi ini
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Preview Modal -->
        <Modal :show="previewingVersion !== null" max-width="2xl" @close="previewingVersion = null">
            <div v-if="previewingVersion" class="p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Pratinjau Desain Versi {{ previewingVersion.version }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            Pratinjau menampilkan tata letak dengan data katalog saat ini.
                        </p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                    <WebsiteThemePreview
                        :version-id="previewingVersion.id"
                        :version="previewingVersion.version"
                    />
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="previewingVersion = null"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Modal Konfirmasi Ganti Versi Aktif -->
        <ConfirmationModal
            :show="restoringVersion !== null"
            title="Gunakan Versi Desain Ini"
            :message="
                restoringVersion?.source === 'satsetui'
                    ? `Versi Satsetui v${restoringVersion?.version} akan masuk sebagai draf. Website yang terbit tidak berubah sampai Anda menerapkan draf tersebut.`
                    : `Ganti desain website aktif ke versi v${restoringVersion?.version}? Website Anda yang sedang terbit akan langsung diperbarui menggunakan versi ini.`
            "
            :confirm-text="restoringVersion?.source === 'satsetui' ? 'Ya, Jadikan Draf' : 'Ya, Gunakan Versi Ini'"
            cancel-text="Batal"
            variant="warning"
            @confirm="executeRestore"
            @cancel="restoringVersion = null"
        />

        <!-- Modal Konfirmasi Terapkan Draf Satsetui -->
        <ConfirmationModal
            :show="showPublishDraftConfirm"
            title="Terapkan Draf Satsetui"
            message="Terapkan draf desain Satsetui ke website sekarang? Website Anda yang sedang terbit akan langsung berubah mengikuti desain ini."
            confirm-text="Ya, Terapkan Sekarang"
            cancel-text="Batal"
            variant="warning"
            @confirm="executePublishDraft"
            @cancel="showPublishDraftConfirm = false"
        />

        <!-- Modal Konfirmasi Buang Draf Satsetui -->
        <ConfirmationModal
            :show="showDiscardDraftConfirm"
            title="Buang Draf Satsetui"
            message="Apakah Anda yakin ingin membuang draf desain Satsetui ini? Versi ini akan tetap tersimpan di riwayat versi dan dapat dipulihkan sewaktu-waktu."
            confirm-text="Ya, Buang Draf"
            cancel-text="Batal"
            variant="danger"
            @confirm="executeDiscardDraft"
            @cancel="showDiscardDraftConfirm = false"
        />

        <!-- Modal Konfirmasi Simpan & Terapkan Desain Fabriku -->
        <ConfirmationModal
            :show="showSaveFabrikuConfirm"
            title="Terapkan Desain Fabriku"
            message="Menyimpan desain Fabriku baru akan langsung mengganti desain website aktif Anda saat ini. Apakah Anda yakin ingin menerapkan desain ini?"
            confirm-text="Ya, Terapkan Desain"
            cancel-text="Batal"
            variant="warning"
            @confirm="executeSaveFabriku"
            @cancel="showSaveFabrikuConfirm = false"
        />
    </div>
</template>
