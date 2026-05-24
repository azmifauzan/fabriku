<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Factory, Eye, Search, Sparkles } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface ProductionItem {
    batch_id: string;
    created_at: string;
    notes: string | null;
    total_qty: number;
    total_cost: number;
    item_count: number;
    inventory_item?: {
        sku: string;
        product_name: string;
    };
    adjusted_by?: {
        name: string;
    };
}

interface Pagination<T> {
    data: T[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    productions: Pagination<ProductionItem>;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search ?? '');

watch(search, (value) => {
    router.get('/simple-production', { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const fmt = (value: number) => 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Catatan Produksi" />

        <div class="px-6 py-6 min-h-screen bg-gray-50/50 dark:bg-gray-900/50">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>Riwayat Catatan Produksi</span>
                            <Sparkles :size="18" class="text-indigo-500" />
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Daftar semua kegiatan produksi sederhana yang pernah dicatat</p>
                    </div>
                    <div>
                        <Link
                            href="/simple-production/create"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all transform hover:-translate-y-0.5"
                        >
                            <Factory :size="16" />
                            Catat Produksi Baru
                        </Link>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex items-center rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                    <div class="relative w-full max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <Search :size="16" class="text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Cari SKU atau nama produk..."
                            class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </div>
                </div>

                <!-- Table Content -->
                <div v-if="productions.data.length === 0" class="rounded-2xl border border-gray-100 bg-white py-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-800">
                    <Factory :size="48" class="mx-auto text-gray-300 dark:text-gray-600 mb-4 animate-bounce" />
                    <h3 class="text-md font-bold text-gray-800 dark:text-gray-200">Belum ada riwayat produksi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">
                        Klik tombol "Catat Produksi Baru" di atas untuk mencatat penambahan produk jadi dan konsumsi bahan baku Anda.
                    </p>
                    <Link
                        href="/simple-production/create"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                    >
                        Mulai Mencatat
                    </Link>
                </div>

                <div v-else class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50/50 text-xs font-bold uppercase tracking-wider text-gray-400 dark:border-gray-700 dark:bg-gray-900/20">
                                        <th class="p-4">Tanggal</th>
                                        <th class="p-4">Batch ID</th>
                                        <th class="p-4">Produk Jadi</th>
                                        <th class="p-4 text-right">Jumlah</th>
                                        <th class="p-4 text-right">Nilai Produksi</th>
                                        <th class="p-4">Dicatat Oleh</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="prod in productions.data" 
                                        :key="prod.batch_id" 
                                        class="border-b border-gray-50/50 hover:bg-gray-50/20 text-sm text-gray-700 dark:border-gray-750 dark:text-gray-300 dark:hover:bg-gray-800/10"
                                    >
                                        <td class="p-4 font-medium">
                                            {{ new Date(prod.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                        </td>
                                        <td class="p-4 font-mono text-xs text-gray-400">
                                            {{ prod.batch_id.substring(0, 8) }}...
                                        </td>
                                        <td class="p-4">
                                            <div class="min-w-0" v-if="prod.inventory_item">
                                                <p class="font-bold text-gray-800 dark:text-gray-200">{{ prod.inventory_item.product_name }}</p>
                                                <p class="text-xs font-semibold text-gray-400 mt-0.5">{{ prod.inventory_item.sku }}</p>
                                            </div>
                                            <span v-else class="text-gray-400 italic">Generic Production</span>
                                        </td>
                                        <td class="p-4 text-right font-bold text-indigo-600 dark:text-indigo-400">
                                            +{{ prod.total_qty }} pcs
                                        </td>
                                        <td class="p-4 text-right font-bold text-gray-800 dark:text-gray-200">
                                            {{ fmt(prod.total_cost) }}
                                        </td>
                                        <td class="p-4 text-gray-600 dark:text-gray-400 font-medium">
                                            {{ prod.adjusted_by?.name || '-' }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <Link
                                                :href="`/simple-production/${prod.batch_id}`"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none dark:border-gray-700 dark:bg-gray-850 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors"
                                            >
                                                <Eye :size="12" />
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="productions.last_page > 1" class="flex items-center justify-between py-2">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Menampilkan {{ productions.data.length }} dari {{ productions.total }} riwayat
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="link in productions.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3.5 py-1.5 rounded-lg text-xs font-bold border transition-all',
                                        link.active 
                                            ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' 
                                            : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50 dark:bg-gray-850 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold border border-gray-100 bg-gray-50 text-gray-400 dark:bg-gray-900/50 dark:border-gray-800"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
