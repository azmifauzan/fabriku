<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Factory, Calendar, User, Note, Info, Sparkles, BookOpen, Layers } from 'lucide-vue-next';

interface Summary {
    batch_id: string;
    created_at: string;
    notes: string | null;
    total_qty: number;
    total_cost: number;
    adjusted_by?: {
        name: string;
    };
}

interface Line {
    id: number;
    inventory_item?: {
        sku: string;
        product_name: string;
    };
    adjustment_quantity: number;
    unit_cost: number;
}

interface MaterialUsage {
    id: number;
    quantity: number;
    material_receipt?: {
        receipt_number: string;
        batch_number: string | null;
        price_per_unit: number;
        material?: {
            code: string;
            name: string;
            unit: string;
        };
    };
}

interface PrepOrder {
    id: number;
    material_usages?: MaterialUsage[];
}

defineProps<{
    summary: Summary;
    lines: Line[];
    prepOrder: PrepOrder | null;
}>();

const fmt = (value: number) => 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
</script>

<template>
    <AppLayout>
        <Head title="Detail Catatan Produksi" />

        <div class="px-6 py-6 min-h-screen bg-gray-50/50 dark:bg-gray-900/50">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-3">
                    <Link
                        href="/simple-production"
                        class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
                    >
                        <ArrowLeft :size="16" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>Detail Catatan Produksi</span>
                            <Sparkles :size="16" class="text-indigo-500" />
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Informasi lengkap hasil produksi dan bahan baku yang digunakan dalam batch ini</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Sidebar Info -->
                    <div class="md:col-span-1 space-y-6">
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 space-y-4">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Transaksi</h3>

                            <!-- Batch ID -->
                            <div>
                                <span class="text-xs font-semibold text-gray-400">Batch ID</span>
                                <p class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">{{ summary.batch_id }}</p>
                            </div>

                            <!-- Date -->
                            <div class="flex items-center gap-2">
                                <Calendar :size="16" class="text-gray-400" />
                                <div>
                                    <span class="text-xs font-semibold text-gray-400 block">Tanggal Produksi</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ new Date(summary.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </span>
                                </div>
                            </div>

                            <!-- User -->
                            <div class="flex items-center gap-2">
                                <User :size="16" class="text-gray-400" />
                                <div>
                                    <span class="text-xs font-semibold text-gray-400 block">Dicatat Oleh</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ summary.adjusted_by?.name || '-' }}</span>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="summary.notes" class="pt-3 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-xs font-semibold text-gray-400">Catatan</span>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed bg-gray-50 p-2.5 rounded-lg dark:bg-gray-900">{{ summary.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Production Details -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Finished Products Produced -->
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-700">
                                <Factory :size="18" class="text-indigo-600 dark:text-indigo-400" />
                                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200">Hasil Produksi</h2>
                            </div>

                            <div v-for="line in lines" :key="line.id" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-2">
                                <div class="min-w-0" v-if="line.inventory_item">
                                    <p class="font-bold text-gray-800 dark:text-gray-200 text-md">{{ line.inventory_item.product_name }}</p>
                                    <p class="text-xs font-semibold text-gray-450 mt-1">SKU: {{ line.inventory_item.sku }}</p>
                                </div>
                                <div class="flex gap-6 sm:text-right shrink-0">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-400 block">Jumlah</span>
                                        <span class="text-md font-bold text-indigo-600 dark:text-indigo-400">+{{ line.adjustment_quantity }} pcs</span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-400 block">Estimasi HPP</span>
                                        <span class="text-md font-bold text-gray-800 dark:text-gray-200">{{ fmt(line.unit_cost) }} / pcs</span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-gray-400 block">Total Nilai</span>
                                        <span class="text-md font-bold text-gray-800 dark:text-gray-200">{{ fmt(line.adjustment_quantity * line.unit_cost) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Materials Consumed -->
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 space-y-4">
                            <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-700">
                                <BookOpen :size="18" class="text-purple-600 dark:text-purple-400" />
                                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200">Bahan Baku Terkonsumsi (FIFO)</h2>
                            </div>

                            <div v-if="!prepOrder || !prepOrder.material_usages || prepOrder.material_usages.length === 0" class="py-6 text-center text-sm text-gray-450 italic">
                                Tidak ada catatan pemakaian bahan baku untuk batch ini.
                            </div>
                            <div v-else class="space-y-4">
                                <div 
                                    v-for="usage in prepOrder.material_usages" 
                                    :key="usage.id"
                                    class="flex items-start sm:items-center justify-between gap-3 py-2 border-b border-gray-50 dark:border-gray-800/50 last:border-b-0"
                                >
                                    <div class="min-w-0" v-if="usage.material_receipt?.material">
                                        <p class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                                            {{ usage.material_receipt.material.name }}
                                        </p>
                                        <p class="text-xs font-semibold text-gray-400 mt-1 flex flex-wrap items-center gap-1.5">
                                            <span>Kode: {{ usage.material_receipt.material.code }}</span>
                                            <span class="text-gray-300 dark:text-gray-700">•</span>
                                            <span class="font-mono bg-gray-50 px-1.5 py-0.5 rounded text-[10px] dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                                                Batch: {{ usage.material_receipt.batch_number || usage.material_receipt.receipt_number }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-red-600 dark:text-red-400">-{{ usage.quantity }} {{ usage.material_receipt?.material?.unit }}</p>
                                        <p class="text-xs font-semibold text-gray-450 mt-1">
                                            {{ fmt(usage.material_receipt?.price_per_unit || 0) }} / {{ usage.material_receipt?.material?.unit }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
