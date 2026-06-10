<template>
    <AppLayout title="Print QR Code">
        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="`/inventory/items/${item.id}`"
                        class="mb-4 inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Detail Item
                    </Link>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Print QR Code</h1>
                </div>

                <!-- Printable Area -->
                <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-800" id="printable-area">
                    <div class="text-center">
                        <!-- Item Info -->
                        <div class="mb-6">
                            <h2 class="mb-2 text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ item.product_name || item.name }}
                            </h2>
                            <p class="text-lg text-gray-600 dark:text-gray-400">
                                SKU: <span class="font-mono font-semibold">{{ item.sku }}</span>
                            </p>
                            <p v-if="item.product_code" class="text-sm text-gray-500 dark:text-gray-500">Kode Produk: {{ item.product_code }}</p>
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

                        <!-- Additional Info -->
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p v-if="item.inventory_location">Lokasi: {{ item.inventory_location.name }}</p>
                            <p class="mt-1">Stock: {{ item.current_quantity }} {{ item.unit || 'pcs' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Not Printed) -->
                <div class="mt-6 flex justify-center gap-4 print:hidden">
                    <button
                        type="button"
                        @click="printQrCode"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900 dark:focus:ring-offset-gray-800"
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
                        @click="downloadQrCode"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900 dark:focus:ring-offset-gray-800"
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
    item: {
        id: number;
        sku: string;
        product_name?: string;
        name?: string;
        product_code?: string;
        current_quantity: number;
        unit?: string;
        inventory_location?: {
            name: string;
        };
    };
}

const props = defineProps<Props>();

const qrCodeSvg = ref('');
const qrCodeLoading = ref(true);
const qrCodeError = ref('');

onMounted(async () => {
    try {
        const response = await fetch(`/inventory/items/${props.item.id}/qrcode/generate`);
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
    link.download = `qr-${props.item.sku}.svg`;
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
