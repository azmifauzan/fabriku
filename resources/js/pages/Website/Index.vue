<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    ExternalLink,
    Pause,
    Play,
    Sparkles,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import TabDesain from './tabs/TabDesain.vue';
import TabIdentitas from './tabs/TabIdentitas.vue';
import TabKatalog from './tabs/TabKatalog.vue';
import TabPengaturan from './tabs/TabPengaturan.vue';
import TabPermintaan from './tabs/TabPermintaan.vue';

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
    mode: string;
    status: string;
    domain_status: string;
    custom_domain: string | null;
    setup_completed_at: string | null;
    profile: Record<string, string>;
    seo: Record<string, string> | null;
    active_theme_version: ThemeVersion | null;
    theme_versions: ThemeVersion[];
    recipients: Array<{ id: number; name: string }>;
};

type Tab = 'permintaan' | 'identitas' | 'katalog' | 'desain' | 'pengaturan';

const props = defineProps<{
    site: Site | null;
    draftThemeVersion: DraftThemeVersion | null;
    lastManualTheme: any;
    canManage: boolean;
    domainRecords: Array<{ type: string; name: string; value: string }>;
    cnameTarget: string;
    catalog: any[];
    products: any[];
    services: any[];
    users: Array<{ id: number; name: string; email: string }>;
    leads: any[];
    orders: any[];
    contentPages: any[];
}>();

const page = usePage();
const csrfToken = ref('');

onMounted(() => {
    csrfToken.value = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
});

const successMessage = computed(() => (page.props.flash as { success?: string } | undefined)?.success);

// Tab Navigation & URL Sync
const getRequestedTab = (): Tab => {
    const tabParam = new URLSearchParams(window.location.search).get('tab');
    if (tabParam && ['permintaan', 'identitas', 'katalog', 'desain', 'pengaturan'].includes(tabParam)) {
        return tabParam as Tab;
    }
    return 'permintaan';
};

const activeTab = ref<Tab>(getRequestedTab());

function switchTab(tab: Tab) {
    activeTab.value = tab;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url.toString());
}

const tabOrder: Tab[] = ['permintaan', 'identitas', 'katalog', 'desain', 'pengaturan'];
function onTabKeydown(event: KeyboardEvent) {
    const step = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
    if (!step) return;
    event.preventDefault();
    const next = tabOrder[(tabOrder.indexOf(activeTab.value) + step + tabOrder.length) % tabOrder.length];
    switchTab(next);
    (event.currentTarget as HTMLElement).querySelectorAll<HTMLElement>('[role="tab"]')[tabOrder.indexOf(next)]?.focus();
}

// Watch browser back/forward buttons
onMounted(() => {
    window.addEventListener('popstate', () => {
        activeTab.value = getRequestedTab();
    });
});

// Setup Wizard State
const setupActive = ref(!props.site || !props.site.setup_completed_at);
const setupStep = ref<1 | 2 | 3>(!props.site ? 1 : props.site.active_theme_version ? 3 : 2);

// Watch props for site setup completion
watch(
    () => props.site?.setup_completed_at,
    (completed) => {
        setupActive.value = !props.site || !completed;
    },
);

const newLeadsCount = computed(() => props.leads.filter((l) => l.status === 'new').length);

const siteStatusMap: Record<string, { label: string; class: string }> = {
    draft: { label: 'Draf', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' },
    published: { label: 'Terbit', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' },
    paused: { label: 'Dijeda', class: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' },
    suspended: { label: 'Ditangguhkan', class: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' },
};

function togglePublish(status: 'published' | 'paused') {
    router.post('/website/publish', { status }, { preserveScroll: true });
}

function completeSetup() {
    router.post(
        '/website/setup/complete',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                setupActive.value = false;
                switchTab('permintaan');
            },
        },
    );
}

function useDefaultDesignAndProceed() {
    router.post(
        '/website/theme',
        {
            primary: '#2563eb',
            accent: '#3b82f6',
            background: '#ffffff',
            text: '#0f172a',
            hero_title: props.site?.profile?.name || 'Selamat Datang',
            hero_subtitle: props.site?.profile?.description || '',
            preset: 'ruang',
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                setupStep.value = 3;
            },
        },
    );
}
</script>

<template>
    <AppLayout>
        <Head title="Website Usaha" />

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 sm:py-8">
            <!-- Header Situs (Selalu Terlihat) -->
            <header class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold tracking-wider text-teal-700 uppercase dark:text-teal-400">
                            Website Usaha
                        </span>
                        <span
                            v-if="site"
                            :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', siteStatusMap[site.status]?.class ?? 'bg-slate-100 text-slate-700']"
                        >
                            {{ siteStatusMap[site.status]?.label ?? site.status }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">
                        {{ site?.profile?.name || 'Satu Alamat untuk Usaha Anda' }}
                    </h1>

                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ site?.custom_domain ? `https://${site.custom_domain}` : site?.slug ? `https://${site.slug}.fabriku.biz.id` : 'Terima pesanan produk dan prospek layanan langsung dari website.' }}
                    </p>
                </div>

                <!-- Aksi Header: Terbitkan / Jeda & Lihat Website -->
                <div v-if="site" class="flex flex-wrap items-center gap-2">
                    <a
                        :href="site.custom_domain ? `https://${site.custom_domain}` : `https://${site.slug}.fabriku.biz.id`"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        Lihat Website
                        <ExternalLink class="h-3.5 w-3.5" />
                    </a>

                    <button
                        v-if="site.status === 'published'"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-semibold text-amber-800 shadow-2xs hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300 dark:hover:bg-amber-900"
                        @click="togglePublish('paused')"
                    >
                        <Pause class="h-3.5 w-3.5" />
                        Jeda Website
                    </button>

                    <button
                        v-else-if="site.status !== 'suspended'"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-3.5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-600"
                        @click="togglePublish('published')"
                    >
                        <Play class="h-3.5 w-3.5" />
                        Terbitkan Website
                    </button>
                </div>
            </header>

            <!-- Flash Success Message -->
            <div
                v-if="successMessage"
                role="status"
                class="flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-2xs dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200"
            >
                <CheckCircle2 class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span>{{ successMessage }}</span>
            </div>

            <!-- Setup Wizard (Bila Belum Selesai Setup) -->
            <section
                v-if="setupActive"
                class="space-y-6 rounded-xl border border-teal-200 bg-teal-50/60 p-6 shadow-xs dark:border-teal-900 dark:bg-teal-950/30"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-xs font-bold text-teal-800 uppercase dark:text-teal-300">Panduan Penyiapan Awal</span>
                        <h2 class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">
                            {{
                                setupStep === 1
                                    ? 'Tahap 1: Mulai dari Identitas Usaha'
                                    : setupStep === 2
                                      ? 'Tahap 2: Pilih Tampilan Halaman Depan'
                                      : 'Tahap 3: Pilih Produk atau Layanan'
                            }}
                        </h2>
                    </div>

                    <!-- Steps Indicator -->
                    <div class="flex items-center gap-2 text-xs font-semibold">
                        <div
                            v-for="step in [
                                { id: 1, label: 'Identitas' },
                                { id: 2, label: 'Desain' },
                                { id: 3, label: 'Katalog' },
                            ]"
                            :key="step.id"
                            class="flex items-center gap-1.5"
                        >
                            <span
                                :class="[
                                    'grid h-6 w-6 place-items-center rounded-full text-xs font-bold transition-all',
                                    setupStep >= step.id
                                        ? 'bg-teal-800 text-white dark:bg-teal-600'
                                        : 'bg-white text-slate-400 dark:bg-slate-800 dark:text-slate-500',
                                ]"
                            >
                                {{ step.id }}
                            </span>
                            <span :class="setupStep >= step.id ? 'text-teal-900 dark:text-teal-200' : 'text-slate-400'">
                                {{ step.label }}
                            </span>
                            <span v-if="step.id < 3" class="text-slate-300 dark:text-slate-700">/</span>
                        </div>
                    </div>
                </div>

                <!-- Wizard Step 1: Identitas -->
                <div v-if="setupStep === 1">
                    <TabIdentitas :site="site" :can-manage="canManage" />
                </div>

                <!-- Wizard Step 2: Desain -->
                <div v-else-if="setupStep === 2" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-teal-200 pb-3 dark:border-teal-900">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            @click="setupStep = 1"
                        >
                            <ArrowLeft class="h-3.5 w-3.5" />
                            Kembali ke Identitas
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-100/60 px-3 py-1.5 text-xs font-semibold text-teal-900 hover:bg-teal-200/60 dark:border-teal-600 dark:bg-teal-900/60 dark:text-teal-200"
                            @click="useDefaultDesignAndProceed"
                        >
                            <Sparkles class="h-3.5 w-3.5 text-teal-700 dark:text-teal-400" />
                            Gunakan Desain Bawaan & Lanjut
                        </button>
                    </div>

                    <TabDesain
                        v-if="site"
                        :site="site"
                        :draft-theme-version="draftThemeVersion"
                        :last-manual-theme="lastManualTheme"
                        :csrf-token="csrfToken"
                    />
                </div>

                <!-- Wizard Step 3: Katalog -->
                <div v-else-if="setupStep === 3" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-teal-200 pb-3 dark:border-teal-900">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            @click="setupStep = 2"
                        >
                            <ArrowLeft class="h-3.5 w-3.5" />
                            Kembali ke Desain
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-600"
                            @click="completeSetup"
                        >
                            Selesai & Buka Pengelolaan Website
                        </button>
                    </div>

                    <TabKatalog
                        :products="products"
                        :catalog="catalog"
                        :services="services"
                        :mode="site?.mode"
                    />
                </div>
            </section>

            <!-- Navigasi Tab Normal (Setelah Selesai Setup) -->
            <template v-else-if="site">
                <nav
                    aria-label="Tab Pengelolaan Website"
                    role="tablist"
                    class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 text-sm dark:border-slate-800"
                    @keydown="onTabKeydown"
                >
                    <button
                        v-for="t in [
                            { id: 'permintaan', label: 'Permintaan Masuk', badge: newLeadsCount },
                            { id: 'identitas', label: 'Identitas' },
                            { id: 'katalog', label: 'Katalog' },
                            { id: 'desain', label: 'Desain' },
                            { id: 'pengaturan', label: 'Pengaturan' },
                        ]"
                        :key="t.id"
                        type="button"
                        role="tab"
                        :tabindex="activeTab === t.id ? 0 : -1"
                        :aria-selected="activeTab === t.id"
                        :class="[
                            'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition-all focus-visible:outline-2 focus-visible:outline-teal-700',
                            activeTab === t.id
                                ? 'bg-teal-800 text-white shadow-xs dark:bg-teal-700'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
                        ]"
                        @click="switchTab(t.id as Tab)"
                    >
                        {{ t.label }}
                        <span
                            v-if="t.badge && t.badge > 0"
                            class="rounded-full bg-rose-500 px-1.5 py-0.2 text-[10px] font-bold text-white"
                        >
                            {{ t.badge }}
                        </span>
                    </button>
                </nav>

                <!-- Tab Contents -->
                <div class="pt-2">
                    <TabPermintaan
                        v-if="activeTab === 'permintaan'"
                        :orders="orders"
                        :leads="leads"
                    />

                    <TabIdentitas
                        v-else-if="activeTab === 'identitas'"
                        :site="site"
                        :can-manage="canManage"
                    />

                    <TabKatalog
                        v-else-if="activeTab === 'katalog'"
                        :products="products"
                        :catalog="catalog"
                        :services="services"
                        :mode="site.mode"
                    />

                    <TabDesain
                        v-else-if="activeTab === 'desain'"
                        :site="site"
                        :draft-theme-version="draftThemeVersion"
                        :last-manual-theme="lastManualTheme"
                        :csrf-token="csrfToken"
                    />

                    <TabPengaturan
                        v-else-if="activeTab === 'pengaturan'"
                        :site="site"
                        :domain-records="domainRecords"
                        :cnameTarget="cnameTarget"
                        :users="users"
                        :contentPages="contentPages"
                    />
                </div>
            </template>
        </main>
    </AppLayout>
</template>
