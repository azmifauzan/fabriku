<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Activity,
    AlertCircle,
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    Cpu,
    Eye,
    HelpCircle,
    Layers,
    Mail,
    RefreshCw,
    RotateCw,
    Search,
    Send,
    Sliders,
    Sparkles,
    Trash2,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    logs: {
        data: Array<any>;
        links: Array<any>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search: string;
        status: string;
        category: string;
    };
    stats: {
        total_sent: number;
        total_failed: number;
        tenants_reached: number;
        last_sent_at: string | null;
    };
    settings: {
        llm_base_url: string;
        llm_model: string;
        llm_temperature: number;
        llm_max_tokens: number;
        has_api_key: boolean;
        campaign_is_active: boolean;
        campaign_system_prompt: string;
        schedule_info: string;
    };
    features: Record<string, Array<any>>;
    categories: Record<string, any>;
}>();

const { showSuccess, showError, confirm } = useSweetAlert();

// Active tab
const activeTab = ref<'tracking' | 'settings' | 'catalog'>('tracking');

// Filters
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const category = ref(props.filters.category || '');

const applyFilters = () => {
    router.get(
        '/admin/campaigns',
        {
            search: search.value,
            status: status.value,
            category: category.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    category.value = '';
    applyFilters();
};

// LLM Settings Form
const showApiKey = ref(false);
const settingsForm = useForm({
    llm_base_url: props.settings.llm_base_url || 'https://api.openai.com/v1',
    llm_api_key: '',
    llm_model: props.settings.llm_model || 'gpt-4o-mini',
    llm_temperature: props.settings.llm_temperature ?? 0.7,
    llm_max_tokens: props.settings.llm_max_tokens ?? 1200,
    campaign_is_active: props.settings.campaign_is_active ?? true,
    campaign_system_prompt: props.settings.campaign_system_prompt || '',
});

const saveSettings = () => {
    settingsForm.post('/admin/campaigns/settings', {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess('Berhasil!', 'Pengaturan LLM & Campaign berhasil disimpan.');
        },
        onError: () => {
            showError('Gagal!', 'Periksa kembali isian formulir pengaturan.');
        },
    });
};

// LLM Connection Testing
const testingLlm = ref(false);
const llmTestResult = ref<{
    tested: boolean;
    success: boolean;
    message: string;
    latency_ms: number;
    model?: string;
    preview?: string;
} | null>(null);

const testLlmConnection = async () => {
    testingLlm.value = true;
    llmTestResult.value = null;

    try {
        const plainMetaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await axios.post('/admin/campaigns/test-llm', {
            llm_base_url: settingsForm.llm_base_url,
            llm_api_key: settingsForm.llm_api_key,
            llm_model: settingsForm.llm_model,
        }, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(plainMetaToken ? { 'X-CSRF-TOKEN': plainMetaToken } : {}),
            },
            timeout: 60000,
        });

        const data = response.data;

        llmTestResult.value = {
            tested: true,
            success: data.success,
            message: data.message,
            latency_ms: data.latency_ms ?? 0,
            model: data.model,
            preview: data.response_preview,
        };

        if (data.success) {
            showSuccess('Koneksi Berhasil!', `${data.message} Latensi: ${data.latency_ms}ms.`);
        } else {
            showError('Koneksi Gagal!', data.message);
        }
    } catch (e: any) {
        const errorMsg =
            e.response?.data?.message ||
            (e.response?.status === 419 ? 'Sesi CSRF telah kedaluwarsa. Silakan refresh halaman browser.' : null) ||
            (e.response?.status === 401 ? 'Sesi login telah berakhir. Silakan login kembali ke admin panel.' : null) ||
            (e.response?.status === 504 ? 'Request timeout saat menghubungi endpoint LLM.' : null) ||
            e.message ||
            'Gagal menghubungi server.';

        llmTestResult.value = {
            tested: true,
            success: false,
            message: errorMsg,
            latency_ms: 0,
        };
        showError('Gagal!', errorMsg);
    } finally {
        testingLlm.value = false;
    }
};

// Preview Modal
const previewModalOpen = ref(false);
const previewLoading = ref(false);
const previewData = ref<any>(null);

const openPreview = async (logId: number) => {
    previewLoading.value = true;
    previewModalOpen.value = true;
    previewData.value = null;

    try {
        const response = await axios.get(`/admin/campaigns/${logId}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        previewData.value = response.data;
    } catch (e: any) {
        showError('Gagal!', e.response?.data?.message || 'Tidak dapat memuat konten email.');
        previewModalOpen.value = false;
    } finally {
        previewLoading.value = false;
    }
};

// Send Batch Immediately
const triggerBatch = async () => {
    const ok = await confirm(
        'Kirim Batch Campaign Sekarang?',
        'Sistem akan mengirimkan campaign email edukasi fitur ke seluruh admin tenant aktif saat ini.',
        'Ya, Kirim Sekarang',
        'question',
        '#6366f1',
    );

    if (ok) {
        router.post('/admin/campaigns/send-batch', {}, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Proses Selesai', 'Batch email campaign telah berhasil diproses.');
            },
        });
    }
};

// Resend Single Email
const resendCampaign = async (logId: number, recipient: string) => {
    const ok = await confirm(
        'Kirim Ulang Email?',
        `Kirim ulang email kampanye ini ke ${recipient}?`,
        'Ya, Kirim Ulang',
        'info',
        '#6366f1',
    );

    if (ok) {
        router.post(`/admin/campaigns/${logId}/resend`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Terkirim!', `Email kampanye berhasil dikirim ulang ke ${recipient}.`);
            },
        });
    }
};

// Test Email Modal
const testEmailModalOpen = ref(false);
const testEmailForm = useForm({
    email: '',
    category: 'garment',
    feature_key: '',
});

const submitTestEmail = () => {
    testEmailForm.post('/admin/campaigns/send-test-email', {
        preserveScroll: true,
        onSuccess: () => {
            testEmailModalOpen.value = false;
            showSuccess('Berhasil!', `Email uji coba berhasil dikirim ke ${testEmailForm.email}.`);
            testEmailForm.reset('email');
        },
        onError: () => {
            showError('Gagal!', 'Periksa kembali alamat email tujuan.');
        },
    });
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Email Campaign & LLM Settings" />

    <AdminLayout>
        <!-- Header Section -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <Mail class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                    Campaign Email & Pengaturan AI
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400 text-sm">
                    Kirim tips & edukasi fitur Fabriku mingguan ke admin tenant via AI (OpenAI-compatible) dan pantau riwayat pengiriman.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="testEmailModalOpen = true"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    <Send class="h-4 w-4 text-gray-500" />
                    Test Kirim Email
                </button>
                <button
                    type="button"
                    @click="triggerBatch"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <Sparkles class="h-4 w-4" />
                    Kirim Batch Sekarang
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-6">
                <button
                    @click="activeTab = 'tracking'"
                    :class="[
                        'flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition',
                        activeTab === 'tracking'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                    ]"
                >
                    <Activity class="h-4 w-4" />
                    Tracking & Riwayat Campaign
                    <span
                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                    >
                        {{ props.logs.total }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'settings'"
                    :class="[
                        'flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition',
                        activeTab === 'settings'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                    ]"
                >
                    <Sliders class="h-4 w-4" />
                    Pengaturan LLM (AI) & Jadwal
                </button>

                <button
                    @click="activeTab = 'catalog'"
                    :class="[
                        'flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition',
                        activeTab === 'catalog'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                    ]"
                >
                    <Layers class="h-4 w-4" />
                    Katalog Fitur Fabriku
                </button>
            </nav>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 1: TRACKING & RIWAYAT -->
        <!-- ==================================================== -->
        <div v-if="activeTab === 'tracking'" class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Terkirim</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_sent }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <CheckCircle2 class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Gagal Dikirim</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_failed }}</p>
                        </div>
                        <div class="rounded-lg bg-rose-50 p-3 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                            <AlertCircle class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant Terjangkau</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.tenants_reached }}</p>
                        </div>
                        <div class="rounded-lg bg-indigo-50 p-3 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                            <Building2 class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jadwal Otomatis</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">Senin 10:00 WIB</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ settings.campaign_is_active ? 'Status: Aktif' : 'Status: Nonaktif' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-purple-50 p-3 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
                            <Clock class="h-6 w-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <div class="sm:col-span-2 relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari penerima, subjek, tenant, atau fitur..."
                            @keyup.enter="applyFilters"
                            class="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <select
                            v-model="status"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Semua Status</option>
                            <option value="sent">Terkirim (Berhasil)</option>
                            <option value="failed">Gagal</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <select
                            v-model="category"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="(cat, key) in categories" :key="key" :value="key">
                                {{ cat.label || key }}
                            </option>
                        </select>

                        <button
                            v-if="search || status || category"
                            type="button"
                            @click="clearFilters"
                            class="rounded-lg border border-gray-300 p-2 text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            title="Reset Filter"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                            <tr>
                                <th class="px-5 py-3.5">Waktu Kirim</th>
                                <th class="px-5 py-3.5">Tenant & Kategori</th>
                                <th class="px-5 py-3.5">Admin Penerima</th>
                                <th class="px-5 py-3.5">Fitur & Subjek</th>
                                <th class="px-5 py-3.5">Model AI</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30"
                            >
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-medium text-gray-600 dark:text-gray-300">
                                    {{ formatDate(log.sent_at || log.created_at) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ log.tenant ? log.tenant.name : 'Test / Manual' }}
                                    </div>
                                    <span class="inline-block mt-0.5 rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                        {{ log.business_category || 'general' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="text-gray-900 dark:text-white font-medium">
                                        {{ log.recipient_name || '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ log.recipient_email }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 max-w-xs">
                                    <div class="font-medium text-indigo-600 dark:text-indigo-400 truncate">
                                        {{ log.feature_name }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                        {{ log.subject }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-[11px] dark:bg-gray-700">
                                        {{ log.llm_model_used || 'default' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span
                                        v-if="log.status === 'sent'"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400"
                                    >
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Terkirim
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 dark:bg-rose-950/40 dark:text-rose-400"
                                        :title="log.error_message"
                                    >
                                        <AlertCircle class="h-3.5 w-3.5" />
                                        Gagal
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openPreview(log.id)"
                                            class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                                            title="Lihat Email"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="resendCampaign(log.id, log.recipient_email)"
                                            class="rounded-lg p-1.5 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                            title="Kirim Ulang"
                                        >
                                            <RotateCw class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="logs.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    <Mail class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                                    <p class="font-medium">Belum ada riwayat email campaign yang tercatat.</p>
                                    <p class="text-xs text-gray-400 mt-1">Klik tombol "Kirim Batch Sekarang" atau "Test Kirim Email" untuk memulai.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="logs.links && logs.links.length > 3"
                    class="flex items-center justify-between border-t border-gray-200 bg-white px-5 py-3.5 dark:border-gray-700 dark:bg-gray-800"
                >
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Menampilkan <span class="font-medium">{{ logs.data.length }}</span> dari
                        <span class="font-medium">{{ logs.total }}</span> riwayat
                    </p>
                    <div class="flex gap-1">
                        <button
                            v-for="(link, i) in logs.links"
                            :key="i"
                            :disabled="!link.url || link.active"
                            @click="router.get(link.url, {}, { preserveState: true })"
                            v-html="link.label"
                            :class="[
                                'rounded px-2.5 py-1 text-xs font-medium transition',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-gray-600 hover:bg-gray-100 disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-700',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 2: PENGATURAN LLM & JADWAL -->
        <!-- ==================================================== -->
        <div v-if="activeTab === 'settings'" class="max-w-4xl space-y-6">
            <!-- Schedule Info Banner -->
            <div class="rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-purple-50 p-5 dark:border-indigo-900/50 dark:from-indigo-950/40 dark:to-purple-950/40">
                <div class="flex items-start gap-3.5">
                    <div class="rounded-lg bg-indigo-600 p-2.5 text-white">
                        <Calendar class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-indigo-950 dark:text-indigo-200">
                            Jadwal Otomatis Mingguan
                        </h3>
                        <p class="text-xs text-indigo-800/90 dark:text-indigo-300/80 mt-1">
                            Kampanye email dijadwalkan berjalan secara otomatis <strong>setiap hari Senin pukul 10:00 WIB</strong> (Asia/Jakarta).
                            Sistem akan memilih fitur yang relevan dengan jenis industri tenant, menghasilkan narasi copywriting via LLM, dan mengirimkannya ke email admin tenant.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Settings Form Card -->
            <form @submit.prevent="saveSettings" class="space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 space-y-5">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <Cpu class="h-5 w-5 text-indigo-600" />
                                Konfigurasi Model AI (OpenAI-Compatible)
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Mendukung endpoint OpenAI standar, DeepSeek, Groq, OpenRouter, vLLM, Ollama, dan penyedia AI lainnya.
                            </p>
                        </div>

                        <!-- Active Toggle -->
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                v-model="settingsForm.campaign_is_active"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700"
                            ></div>
                            <span class="ml-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ settingsForm.campaign_is_active ? 'Kampanye Aktif' : 'Nonaktif' }}
                            </span>
                        </label>
                    </div>

                    <!-- Base URL -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            LLM Base URL
                        </label>
                        <input
                            v-model="settingsForm.llm_base_url"
                            type="text"
                            required
                            placeholder="https://api.openai.com/v1"
                            class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <p class="mt-1 text-[11px] text-gray-500">
                            Contoh: <code class="text-indigo-600 dark:text-indigo-400">https://api.openai.com/v1</code>, <code class="text-indigo-600 dark:text-indigo-400">https://api.deepseek.com/v1</code>, atau <code class="text-indigo-600 dark:text-indigo-400">https://api.groq.com/openai/v1</code>
                        </p>
                    </div>

                    <!-- API Key -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                LLM API Key
                            </label>
                            <span v-if="settings.has_api_key" class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                <CheckCircle2 class="h-3 w-3" /> API Key tersimpan di sistem
                            </span>
                        </div>
                        <div class="relative mt-1.5">
                            <input
                                v-model="settingsForm.llm_api_key"
                                :type="showApiKey ? 'text' : 'password'"
                                :placeholder="settings.has_api_key ? '••••••••••••••••••••••••••••• (kosongkan jika tidak diubah)' : 'sk-...'"
                                class="w-full rounded-lg border border-gray-300 pl-3.5 pr-20 py-2.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <button
                                type="button"
                                @click="showApiKey = !showApiKey"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded px-2 py-1 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                {{ showApiKey ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                        </div>
                    </div>

                    <!-- Model & Temperature Grid -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Model Name
                            </label>
                            <input
                                v-model="settingsForm.llm_model"
                                type="text"
                                required
                                placeholder="gpt-4o-mini"
                                class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-[11px] text-gray-500">
                                Contoh: gpt-4o-mini, deepseek-chat
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Temperature ({{ settingsForm.llm_temperature }})
                            </label>
                            <input
                                v-model.number="settingsForm.llm_temperature"
                                type="range"
                                min="0"
                                max="1.5"
                                step="0.1"
                                class="mt-2.5 w-full accent-indigo-600"
                            />
                            <p class="mt-1 text-[11px] text-gray-500">
                                0.7 direkomendasikan untuk copywriting
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Max Tokens
                            </label>
                            <input
                                v-model.number="settingsForm.llm_max_tokens"
                                type="number"
                                min="200"
                                max="4000"
                                required
                                class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-[11px] text-gray-500">
                                Standar: 1200 tokens
                            </p>
                        </div>
                    </div>

                    <!-- System Prompt Customization -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            System Prompt / Gaya Bahasa AI
                        </label>
                        <textarea
                            v-model="settingsForm.campaign_system_prompt"
                            rows="3"
                            placeholder="Instruksi kepribadian copywriter..."
                            class="mt-1.5 w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                    </div>

                    <!-- Live Test Output Result -->
                    <div
                        v-if="llmTestResult"
                        :class="[
                            'rounded-lg border p-4 text-xs',
                            llmTestResult.success
                                ? 'border-emerald-200 bg-emerald-50/70 text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200'
                                : 'border-rose-200 bg-rose-50/70 text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200',
                        ]"
                    >
                        <div class="flex items-center gap-2 font-semibold">
                            <CheckCircle2 v-if="llmTestResult.success" class="h-4 w-4 text-emerald-600" />
                            <AlertCircle v-else class="h-4 w-4 text-rose-600" />
                            {{ llmTestResult.message }}
                            <span v-if="llmTestResult.latency_ms" class="ml-auto font-mono text-[11px]">
                                Latensi: {{ llmTestResult.latency_ms }} ms
                            </span>
                        </div>
                        <p v-if="llmTestResult.preview" class="mt-2 text-[11px] italic opacity-90">
                            "{{ llmTestResult.preview }}"
                        </p>
                    </div>

                    <!-- Action Bar -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button
                            type="button"
                            @click="testLlmConnection"
                            :disabled="testingLlm"
                            class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100 disabled:opacity-50 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60"
                        >
                            <RefreshCw :class="['h-3.5 w-3.5', testingLlm ? 'animate-spin' : '']" />
                            {{ testingLlm ? 'Sedang Menghubungi LLM...' : 'Test Koneksi LLM' }}
                        </button>

                        <button
                            type="submit"
                            :disabled="settingsForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ==================================================== -->
        <!-- TAB 3: KATALOG FITUR FABRIKU -->
        <!-- ==================================================== -->
        <div v-if="activeTab === 'catalog'" class="space-y-8">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <Sparkles class="h-4 w-4 text-indigo-600" />
                    Katalog Materi Kampanye Edukasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Berikut adalah fitur-fitur Fabriku yang telah dipetakan berdasarkan kategori bisnis tenant. AI akan memilih fitur yang paling cocok secara bergiliran sehingga admin tenant mendapatkan wawasan baru setiap minggunya.
                </p>
            </div>

            <div v-for="(categoryFeatures, catKey) in features" :key="catKey" class="space-y-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        {{ categories[catKey]?.label || catKey.toUpperCase() }}
                    </h3>
                    <span class="rounded bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                        {{ categoryFeatures.length }} Fitur
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div
                        v-for="feat in categoryFeatures"
                        :key="feat.key"
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ feat.name }}
                                </h4>
                                <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-[10px] text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    {{ feat.key }}
                                </span>
                            </div>

                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ feat.description }}
                            </p>

                            <div class="mt-3 rounded-lg bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-700/50 dark:text-gray-300">
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">Solusi Masalah:</span>
                                {{ feat.pain_point }}
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500">
                            <span>Link Tujuan: <code class="text-indigo-600 dark:text-indigo-400">{{ feat.cta_path }}</code></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- MODAL: PREVIEW EMAIL -->
        <!-- ==================================================== -->
        <div
            v-if="previewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        >
            <div class="flex h-[92vh] w-full max-w-4xl flex-col rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <Mail class="h-5 w-5 text-indigo-600" />
                            Pratinjau Email Campaign
                        </h3>
                        <p v-if="previewData" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Penerima: <strong>{{ previewData.recipient_email }}</strong> ({{ previewData.tenant_name }}) • Model: {{ previewData.llm_model_used }}
                        </p>
                    </div>
                    <button
                        @click="previewModalOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Body with isolated iframe -->
                <div class="relative flex-1 overflow-hidden bg-gray-100 dark:bg-gray-950 p-4">
                    <div v-if="previewLoading" class="flex h-full items-center justify-center text-gray-500">
                        <RefreshCw class="h-8 w-8 animate-spin text-indigo-600" />
                    </div>
                    <iframe
                        v-else-if="previewData && previewData.content_html"
                        :srcdoc="previewData.content_html"
                        class="h-full w-full rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800"
                    ></iframe>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between border-t border-gray-200 px-6 py-3.5 dark:border-gray-700">
                    <span v-if="previewData" class="text-xs text-gray-500">
                        Subjek: <strong class="text-gray-700 dark:text-gray-300">{{ previewData.subject }}</strong>
                    </span>
                    <button
                        type="button"
                        @click="previewModalOpen = false"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- MODAL: TEST KIRIM EMAIL -->
        <!-- ==================================================== -->
        <div
            v-if="testEmailModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <Send class="h-4 w-4 text-indigo-600" />
                        Test Kirim Email Campaign
                    </h3>
                    <button
                        @click="testEmailModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submitTestEmail" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Email Tujuan
                        </label>
                        <input
                            v-model="testEmailForm.email"
                            type="email"
                            required
                            placeholder="nama@perusahaan.com"
                            class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Simulasi Kategori Bisnis
                        </label>
                        <select
                            v-model="testEmailForm.category"
                            class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option v-for="(cat, key) in categories" :key="key" :value="key">
                                {{ cat.label || key }}
                            </option>
                        </select>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="testEmailModalOpen = false"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="testEmailForm.processing"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <Send class="h-3.5 w-3.5" />
                            {{ testEmailForm.processing ? 'Mengirim...' : 'Kirim Sekarang' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
