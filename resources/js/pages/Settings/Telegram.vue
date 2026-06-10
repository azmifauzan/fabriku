<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { AlertCircle, Check, CheckCircle, Copy, Loader2, MessageCircle, RefreshCw, Send, Unlink } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    isConnected: boolean;
    telegramChatId: string | null;
    connectToken: string | null;
    tokenExpiresAt: string | null;
}>();

const loading = ref(false);
const testLoading = ref(false);
const copied = ref(false);
const message = ref('');
const messageType = ref<'success' | 'error'>('success');

const generateToken = async () => {
    loading.value = true;
    message.value = '';

    router.post(
        '/telegram/generate-token',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Token berhasil dibuat! Gunakan token di bawah untuk menghubungkan Telegram.';
                messageType.value = 'success';
            },
            onError: () => {
                message.value = 'Gagal membuat token. Silakan coba lagi.';
                messageType.value = 'error';
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const disconnect = async () => {
    if (!confirm('Apakah Anda yakin ingin memutuskan koneksi Telegram?')) return;

    loading.value = true;
    message.value = '';

    router.post(
        '/telegram/disconnect',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Telegram berhasil diputuskan.';
                messageType.value = 'success';
            },
            onError: () => {
                message.value = 'Gagal memutuskan Telegram. Silakan coba lagi.';
                messageType.value = 'error';
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const sendTest = async () => {
    testLoading.value = true;
    message.value = '';

    router.post(
        '/telegram/test',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Pesan test berhasil dikirim ke Telegram Anda!';
                messageType.value = 'success';
            },
            onError: () => {
                message.value = 'Gagal mengirim pesan test. Pastikan Telegram Anda sudah terhubung.';
                messageType.value = 'error';
            },
            onFinish: () => {
                testLoading.value = false;
            },
        },
    );
};

const copyToken = async () => {
    if (!props.connectToken) return;

    try {
        await navigator.clipboard.writeText(`CONNECT ${props.connectToken}`);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = `CONNECT ${props.connectToken}`;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    }
};

const formatExpiresAt = (dateStr: string | null) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Hubungkan Telegram" />

        <div class="mx-auto max-w-2xl px-4 py-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hubungkan Telegram</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Hubungkan akun Telegram Anda untuk menerima notifikasi bisnis langsung dari Telegram.
                </p>
            </div>

            <!-- Alert Message -->
            <div
                v-if="message"
                :class="[
                    'mb-6 flex items-center gap-3 rounded-lg p-4',
                    messageType === 'success'
                        ? 'bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                        : 'bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                ]"
            >
                <CheckCircle v-if="messageType === 'success'" :size="20" />
                <AlertCircle v-else :size="20" />
                <span>{{ message }}</span>
            </div>

            <!-- Status Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <!-- Connected State -->
                <div v-if="isConnected" class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                            <MessageCircle :size="28" class="text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Telegram Terhubung</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Chat ID: <code class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">{{ telegramChatId }}</code>
                            </p>
                        </div>
                        <div class="ml-auto">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300"
                            >
                                <Check :size="14" />
                                Aktif
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                        <h4 class="mb-2 font-medium text-gray-900 dark:text-white">Cara Menggunakan</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-medium text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                                    >1</span
                                >
                                <span
                                    >Buka
                                    <a href="https://t.me/FabrikuBot" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400"
                                        >@FabrikuBot</a
                                    >
                                    di Telegram</span
                                >
                            </li>
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-medium text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                                    >2</span
                                >
                                <span>Kirim perintah apapun untuk memulai percakapan dengan bot Fabriku</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-medium text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                                    >3</span
                                >
                                <span
                                    >Ketik <code class="rounded bg-gray-200 px-1.5 py-0.5 text-xs dark:bg-gray-700">/help</code> untuk melihat daftar
                                    perintah</span
                                >
                            </li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button
                            @click="sendTest"
                            :disabled="testLoading"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <Loader2 v-if="testLoading" :size="16" class="animate-spin" />
                            <Send v-else :size="16" />
                            Kirim Pesan Test
                        </button>
                        <button
                            @click="disconnect"
                            :disabled="loading"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <Loader2 v-if="loading" :size="16" class="animate-spin" />
                            <Unlink v-else :size="16" />
                            Putuskan Koneksi
                        </button>
                    </div>
                </div>

                <!-- Not Connected State -->
                <div v-else class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                            <MessageCircle :size="28" class="text-gray-400 dark:text-gray-500" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Telegram Belum Terhubung</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Hubungkan akun Telegram Anda untuk menerima notifikasi bisnis via Telegram.
                            </p>
                        </div>
                    </div>

                    <!-- Token Section -->
                    <div
                        v-if="connectToken"
                        class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-900/20"
                    >
                        <h4 class="mb-3 font-medium text-indigo-900 dark:text-indigo-200">Langkah-langkah Menghubungkan:</h4>

                        <ol class="mb-4 space-y-3 text-sm text-indigo-800 dark:text-indigo-300">
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-indigo-200 text-xs font-medium text-indigo-700 dark:bg-indigo-800 dark:text-indigo-200"
                                    >1</span
                                >
                                <span
                                    >Buka <a href="https://t.me/FabrikuBot" target="_blank" class="font-medium underline">@FabrikuBot</a> di
                                    Telegram</span
                                >
                            </li>
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-indigo-200 text-xs font-medium text-indigo-700 dark:bg-indigo-800 dark:text-indigo-200"
                                    >2</span
                                >
                                <span>Salin dan kirim kode berikut ke bot:</span>
                            </li>
                        </ol>

                        <div class="flex items-center gap-2">
                            <code
                                class="flex-1 rounded-lg bg-white px-4 py-3 font-mono text-lg font-semibold text-indigo-900 dark:bg-gray-800 dark:text-indigo-200"
                            >
                                CONNECT {{ connectToken }}
                            </code>
                            <button
                                @click="copyToken"
                                class="rounded-lg bg-indigo-600 p-3 text-white transition-colors hover:bg-indigo-700"
                                title="Salin kode"
                            >
                                <Check v-if="copied" :size="20" />
                                <Copy v-else :size="20" />
                            </button>
                        </div>

                        <p class="mt-3 text-xs text-indigo-600 dark:text-indigo-400">Token berlaku hingga: {{ formatExpiresAt(tokenExpiresAt) }}</p>
                    </div>

                    <!-- Generate Token Button -->
                    <div class="flex justify-center">
                        <button
                            @click="generateToken"
                            :disabled="loading"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                        >
                            <Loader2 v-if="loading" :size="16" class="animate-spin" />
                            <RefreshCw v-else :size="16" />
                            {{ connectToken ? 'Buat Token Baru' : 'Buat Token Koneksi' }}
                        </button>
                    </div>

                    <!-- Info Box -->
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                        <h4 class="mb-2 font-medium text-gray-900 dark:text-white">Keuntungan Menghubungkan Telegram</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center gap-2">
                                <Check :size="16" class="text-green-500" />
                                Terima notifikasi bisnis kapan saja dari Telegram
                            </li>
                            <li class="flex items-center gap-2">
                                <Check :size="16" class="text-green-500" />
                                Cek status pesanan, material, dan laporan langsung dari Telegram
                            </li>
                            <li class="flex items-center gap-2">
                                <Check :size="16" class="text-green-500" />
                                Riwayat chat tersimpan dan tersinkronisasi
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
