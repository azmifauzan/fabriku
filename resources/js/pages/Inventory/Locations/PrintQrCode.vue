<template>
    <AppLayout title="Print QR Code Lokasi">
        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="`/inventory/locations/${location.id}`"
                        class="mb-4 inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Detail Lokasi
                    </Link>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Print QR Code Lokasi</h1>
                </div>

                <!-- Printable Area -->
                <div id="printable-area" class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-800">
                    <div class="text-center">
                        <!-- Location Info -->
                        <div class="mb-6">
                            <div
                                class="mx-auto mb-3 inline-flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30"
                            >
                                <svg class="h-7 w-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ location.name }}
                            </h2>
                            <p class="mt-1 font-mono text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                {{ location.code }}
                            </p>
                            <p v-if="location.capacity" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Kapasitas: {{ location.capacity }} unit
                            </p>
                        </div>

                        <!-- QR Code -->
                        <div class="mb-6 flex justify-center">
                            <div class="rounded-lg border-4 border-gray-200 bg-white p-4 dark:border-gray-700">
                                <div v-if="qrCodeLoading" class="flex h-64 w-64 items-center justify-center">
                                    <div class="h-12 w-12 animate-spin rounded-full border-b-2 border-indigo-600"></div>
                                </div>
                                <div v-else-if="qrCodeError" class="flex h-64 w-64 items-center justify-center text-red-600">
                                    <p class="text-sm">{{ qrCodeError }}</p>
                                </div>
                                <div v-else v-html="qrCodeSvg" class="flex h-64 w-64 items-center justify-center"></div>
                            </div>
                        </div>

                        <!-- Scan instruction -->
                        <p class="text-sm text-gray-500 dark:text-gray-400">Scan QR Code untuk melihat detail lokasi & item yang tersimpan</p>
                    </div>
                </div>

                <!-- Action Buttons (Not Printed) -->
                <div class="mt-6 flex justify-center gap-4 print:hidden">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900 dark:focus:ring-offset-gray-800"
                        @click="printQrCode"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            />
                        </svg>
                        Print
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800"
                        @click="downloadQrCode"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                        Download SVG
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface Props {
    location: {
        id: number;
        code: string;
        name: string;
        capacity?: number;
    };
}

const props = defineProps<Props>();

const qrCodeSvg = ref('');
const qrCodeLoading = ref(true);
const qrCodeError = ref('');

onMounted(async () => {
    try {
        const response = await fetch(`/inventory/locations/${props.location.id}/qrcode/generate`);
        if (response.ok) {
            qrCodeSvg.value = await response.text();
        } else {
            qrCodeError.value = 'Gagal memuat QR Code';
        }
    } catch (error) {
        console.error('Error loading QR code:', error);
        qrCodeError.value = 'Terjadi kesalahan saat memuat QR Code';
    } finally {
        qrCodeLoading.value = false;
    }
});

function printQrCode() {
    window.print();
}

function downloadQrCode() {
    const blob = new Blob([qrCodeSvg.value], { type: 'image/svg+xml' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `qr-lokasi-${props.location.code}.svg`;
    link.click();
    URL.revokeObjectURL(url);
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #printable-area,
    #printable-area * {
        visibility: visible;
    }

    #printable-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
