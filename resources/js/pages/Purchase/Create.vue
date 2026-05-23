<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface InventoryItem {
    id: number;
    sku: string;
    product_name: string;
    current_quantity: number;
    unit_cost: number;
}

interface LineItem {
    inventory_item_id: number | null;
    quantity: number;
    unit_cost: number;
}

const props = defineProps<{
    inventoryItems: InventoryItem[];
}>();

const form = ref({
    supplier_name: '',
    purchase_invoice: '',
    purchase_date: new Date().toISOString().slice(0, 10),
    notes: '',
    items: [{ inventory_item_id: null, quantity: 1, unit_cost: 0 }] as LineItem[],
});

const errors = ref<Record<string, string>>({});
const submitting = ref(false);

const addLine = () => {
    form.value.items.push({ inventory_item_id: null, quantity: 1, unit_cost: 0 });
};

const removeLine = (index: number) => {
    form.value.items.splice(index, 1);
};

const onItemSelect = (index: number) => {
    const itemId = form.value.items[index].inventory_item_id;
    if (itemId) {
        const found = props.inventoryItems.find((i) => i.id === itemId);
        if (found && found.unit_cost > 0) {
            form.value.items[index].unit_cost = found.unit_cost;
        }
    }
};

const totalCost = computed(() =>
    form.value.items.reduce((sum, l) => sum + l.quantity * l.unit_cost, 0)
);

const formatRupiah = (value: number) =>
    'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });

const submit = () => {
    submitting.value = true;
    errors.value = {};
    router.post('/purchase-receipts', form.value, {
        onError: (err) => {
            errors.value = err;
            submitting.value = false;
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Catat Pembelian" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Catat Pembelian Barang</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Barang dari supplier akan langsung menambah stok.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Header info -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Informasi Pembelian</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Supplier <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.supplier_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Nama toko / supplier"
                                />
                                <p v-if="errors['supplier_name']" class="mt-1 text-xs text-red-500">{{ errors['supplier_name'] }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">No. Invoice / Struk</label>
                                <input
                                    v-model="form.purchase_invoice"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Opsional"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Beli <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.purchase_date"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                                <p v-if="errors['purchase_date']" class="mt-1 text-xs text-red-500">{{ errors['purchase_date'] }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                                <input
                                    v-model="form.notes"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Opsional"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Line items -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Daftar Barang</h2>
                            <button
                                type="button"
                                @click="addLine"
                                class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400"
                            >
                                <Plus :size="14" />
                                Tambah Baris
                            </button>
                        </div>

                        <p v-if="errors['items']" class="mb-2 text-xs text-red-500">{{ errors['items'] }}</p>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="pb-2 text-left">Produk</th>
                                        <th class="pb-2 px-2 text-right w-24">Qty</th>
                                        <th class="pb-2 px-2 text-right w-36">Harga Modal</th>
                                        <th class="pb-2 px-2 text-right w-32">Subtotal</th>
                                        <th class="pb-2 w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="(line, idx) in form.items" :key="idx">
                                        <td class="py-2">
                                            <select
                                                v-model="line.inventory_item_id"
                                                @change="onItemSelect(idx)"
                                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            >
                                                <option :value="null" disabled>Pilih produk...</option>
                                                <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
                                                    {{ item.product_name }} ({{ item.sku }})
                                                </option>
                                            </select>
                                            <p v-if="errors[`items.${idx}.inventory_item_id`]" class="mt-0.5 text-xs text-red-500">
                                                {{ errors[`items.${idx}.inventory_item_id`] }}
                                            </p>
                                        </td>
                                        <td class="py-2 px-2">
                                            <input
                                                v-model.number="line.quantity"
                                                type="number"
                                                min="1"
                                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-right text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            />
                                        </td>
                                        <td class="py-2 px-2">
                                            <input
                                                v-model.number="line.unit_cost"
                                                type="number"
                                                min="0"
                                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-right text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            />
                                        </td>
                                        <td class="py-2 px-2 text-right text-gray-700 dark:text-gray-300">
                                            {{ formatRupiah(line.quantity * line.unit_cost) }}
                                        </td>
                                        <td class="py-2 text-center">
                                            <button
                                                v-if="form.items.length > 1"
                                                type="button"
                                                @click="removeLine(idx)"
                                                class="text-red-400 hover:text-red-600"
                                            >
                                                <Trash2 :size="15" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-gray-200 dark:border-gray-600">
                                        <td colspan="3" class="pt-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Total</td>
                                        <td class="pt-3 px-2 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ formatRupiah(totalCost) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <a href="/purchase-receipts" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Batal
                        </a>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ submitting ? 'Menyimpan...' : 'Simpan Pembelian' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
