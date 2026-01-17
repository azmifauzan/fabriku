<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Location {
    id: number;
    name: string;
    zone: string;
    rack: string;
}

interface Pattern {
    id: number;
    name: string;
    code: string;
    product_type: string;
}

interface PreparationOrder {
    pattern: Pattern;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    preparation_order_id: number;
    quantity_good: number;
    labor_cost: string;
    completed_date: string;
    estimated_completion_date: string;
    status: string;
    preparation_order?: PreparationOrder;
}

interface Item {
    id?: number;
    sku: string;
    name: string;
    production_order_id?: number;
    inventory_location_id: number;
    target_quantity: number;
    current_stock: number;
    minimum_stock: number;
    unit_cost: string;
    selling_price: string;
    quality_grade?: string;
    production_date?: string;
    status: string;
    notes?: string;
}

const props = defineProps<{
    item?: Item;
    locations: Location[];
    productionOrders: ProductionOrder[];
}>();

const { tenant } = useBusinessContext();

const isGarment = computed(() => tenant.value?.business_category === 'garment');

const form = useForm({
    production_order_id: props.item?.production_order_id || null,
    sku: props.item?.sku || '',
    name: props.item?.name || '',
    inventory_location_id: props.item?.inventory_location_id || null,
    target_quantity: props.item?.target_quantity || 0,
    current_stock: props.item?.current_stock || 0,
    minimum_stock: props.item?.minimum_stock || 10,
    unit_cost: props.item?.unit_cost || '0',
    selling_price: props.item?.selling_price || '0',
    quality_grade: props.item?.quality_grade || 'grade_a',
    production_date: props.item?.production_date || '',
    status: props.item?.status || 'available',
    notes: props.item?.notes || '',
});

// Auto-populate fields when production order is selected
const selectedProductionOrder = computed(() => {
    if (!form.production_order_id) return null;
    return props.productionOrders.find((po) => po.id === form.production_order_id);
});

watch(
    () => form.production_order_id,
    (newValue) => {
        if (newValue && selectedProductionOrder.value) {
            const po = selectedProductionOrder.value;

            // Auto-populate target quantity from production order
            form.target_quantity = po.quantity_good || 0;

            // Auto-populate production date from estimated completion date
            if (po.completed_date) {
                form.production_date = po.completed_date;
            } else if (po.estimated_completion_date) {
                form.production_date = po.estimated_completion_date;
            }

            // Auto-populate SKU and name from pattern if available
            if (po.preparation_order?.pattern) {
                const pattern = po.preparation_order.pattern;
                form.sku = `${po.order_number}`;
                form.name = `${pattern.name}`;
            }
        }
    },
);

// Auto-calculate unit cost when production order changes
watch([() => form.production_order_id], () => {
    if (selectedProductionOrder.value) {
        // Unit cost = (Material cost + Labor cost) / Quantity
        // For now, we'll use labor cost from production order
        // Material cost would need to be calculated from preparation order
        const laborCost = parseFloat(selectedProductionOrder.value.labor_cost || '0');
        const quantity = selectedProductionOrder.value.quantity_good || 1;

        // TODO: Add material cost calculation from preparation order's BOM
        const materialCost = 0; // Placeholder

        form.unit_cost = ((materialCost + laborCost) / quantity).toFixed(2);
    }
});

const submit = () => {
    if (props.item?.id) {
        form.put(`/inventory/items/${props.item.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/inventory/items', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.item?.id;

const qualityGrades = [
    { value: 'grade_a', label: 'Grade A' },
    { value: 'grade_b', label: 'Grade B' },
    { value: 'reject', label: 'Reject' },
];

const statuses = [
    { value: 'available', label: 'Available' },
    { value: 'reserved', label: 'Reserved' },
    { value: 'damaged', label: 'Damaged' },
    { value: 'expired', label: 'Expired' },
];
</script>

<template>
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
        <div class="border-b border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ isEditing ? 'Edit Item Inventory' : 'Tambah Item Inventory' }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ isEditing ? 'Ubah informasi item' : 'Tambahkan item inventory baru dari hasil produksi' }}
                    </p>
                </div>
                <Link href="/inventory/items" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                    ← Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Production Order Selection -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Pilih Production Order</h3>
                    <div>
                        <label for="production_order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Production Order <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="production_order_id"
                            v-model="form.production_order_id"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-300': form.errors.production_order_id }"
                        >
                            <option :value="null">Pilih Production Order</option>
                            <option v-for="po in productionOrders" :key="po.id" :value="po.id">
                                {{ po.order_number }} - {{ po.preparation_order?.pattern?.name || 'N/A' }} ({{ po.quantity_good }} pcs) - [{{
                                    po.status
                                }}]
                            </option>
                        </select>
                        <p v-if="form.errors.production_order_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.production_order_id }}
                        </p>
                        <p v-if="selectedProductionOrder" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Tanggal: {{ selectedProductionOrder.completed_date || selectedProductionOrder.estimated_completion_date }}
                        </p>
                    </div>
                </div>

                <!-- Basic Information -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Informasi Dasar</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                SKU <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="sku"
                                v-model="form.sku"
                                type="text"
                                required
                                placeholder="Contoh: PO-2024-001"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.sku }"
                            />
                            <p v-if="form.errors.sku" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.sku }}
                            </p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Item <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="Contoh: Mukena Bali Putih - M"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stock Information -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Data Stock</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="target_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Target Produksi <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="target_quantity"
                                v-model.number="form.target_quantity"
                                type="number"
                                required
                                min="0"
                                readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-300': form.errors.target_quantity }"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dari production order</p>
                            <p v-if="form.errors.target_quantity" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.target_quantity }}
                            </p>
                        </div>

                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Hasil Produksi Aktual <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="current_stock"
                                v-model.number="form.current_stock"
                                type="number"
                                required
                                min="0"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.current_stock }"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Jumlah barang jadi yang sebenarnya</p>
                            <p v-if="form.errors.current_stock" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.current_stock }}
                            </p>
                        </div>

                        <div>
                            <label for="minimum_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Minimum Stock <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="minimum_stock"
                                v-model.number="form.minimum_stock"
                                type="number"
                                required
                                min="0"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.minimum_stock }"
                            />
                            <p v-if="form.errors.minimum_stock" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.minimum_stock }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Harga</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="unit_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Modal (COGS) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="unit_cost"
                                v-model="form.unit_cost"
                                type="number"
                                step="0.01"
                                required
                                min="0"
                                readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-300': form.errors.unit_cost }"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dihitung otomatis dari biaya bahan + biaya produksi</p>
                            <p v-if="form.errors.unit_cost" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.unit_cost }}
                            </p>
                        </div>

                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Jual <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="selling_price"
                                v-model="form.selling_price"
                                type="number"
                                step="0.01"
                                required
                                min="0"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.selling_price }"
                            />
                            <p v-if="form.errors.selling_price" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.selling_price }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Informasi Tambahan</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label for="inventory_location_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Lokasi <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="inventory_location_id"
                                    v-model="form.inventory_location_id"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-300': form.errors.inventory_location_id }"
                                >
                                    <option :value="null">Pilih Lokasi</option>
                                    <option v-for="location in locations" :key="location.id" :value="location.id">
                                        {{ location.name }} ({{ location.zone }}-{{ location.rack }})
                                    </option>
                                </select>
                                <p v-if="form.errors.inventory_location_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.inventory_location_id }}
                                </p>
                            </div>

                            <div v-if="isGarment">
                                <label for="quality_grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Quality Grade <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="quality_grade"
                                    v-model="form.quality_grade"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-300': form.errors.quality_grade }"
                                >
                                    <option v-for="grade in qualityGrades" :key="grade.value" :value="grade.value">
                                        {{ grade.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.quality_grade" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.quality_grade }}
                                </p>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-300': form.errors.status }"
                                >
                                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="production_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Tanggal Produksi </label>
                            <input
                                id="production_date"
                                v-model="form.production_date"
                                type="date"
                                readonly
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-300': form.errors.production_date }"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dari tanggal estimasi selesai di production order</p>
                            <p v-if="form.errors.production_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.production_date }}
                            </p>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Catatan </label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                placeholder="Catatan tambahan (opsional)"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.notes }"
                            />
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col items-stretch justify-end gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:items-center dark:border-gray-700"
                >
                    <Link
                        href="/inventory/items"
                        class="order-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:order-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="order-1 rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 sm:order-2"
                    >
                        {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update Item' : 'Tambah Item' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
