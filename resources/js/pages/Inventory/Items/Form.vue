<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { useBusinessContext } from '@/composables/useBusinessContext';

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

interface Item {
    id?: number;
    sku: string;
    name: string;
    category: string;
    inventory_location_id: number;
    pattern_id?: number;
    current_stock: number;
    reserved_stock: number;
    minimum_stock: number;
    unit_cost: string;
    selling_price: string;
    quality_grade: string;
    batch_number?: string;
    production_date?: string;
    expiry_date?: string;
    storage_requirements?: string;
    status: string;
    notes?: string;
}

const props = defineProps<{
    item?: Item;
    locations: Location[];
    patterns: Pattern[];
}>();

const { terminology, tenant } = useBusinessContext();

const isFood = computed(() => tenant.value?.business_category === 'food');

const form = useForm({
    sku: props.item?.sku || '',
    name: props.item?.name || '',
    category: props.item?.category || 'finished_goods',
    inventory_location_id: props.item?.inventory_location_id || null,
    pattern_id: props.item?.pattern_id || null,
    current_stock: props.item?.current_stock || 0,
    reserved_stock: props.item?.reserved_stock || 0,
    minimum_stock: props.item?.minimum_stock || 10,
    unit_cost: props.item?.unit_cost || '0',
    selling_price: props.item?.selling_price || '0',
    quality_grade: props.item?.quality_grade || 'grade_a',
    batch_number: props.item?.batch_number || '',
    production_date: props.item?.production_date || '',
    expiry_date: props.item?.expiry_date || '',
    storage_requirements: props.item?.storage_requirements || '',
    status: props.item?.status || 'available',
    notes: props.item?.notes || '',
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

const categories = [
    { value: 'finished_goods', label: 'Barang Jadi' },
    { value: 'work_in_progress', label: 'Work in Progress' },
    { value: 'raw_materials', label: 'Bahan Mentah' },
];

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

const storageRequirements = [
    { value: 'room_temp', label: 'Room Temperature' },
    { value: 'cool', label: 'Cool (15-20°C)' },
    { value: 'chilled', label: 'Chilled (2-8°C)' },
    { value: 'frozen', label: 'Frozen (<0°C)' },
];
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6">
            <Link
                href="/inventory/items"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-4"
            >
                <svg
                    class="w-4 h-4 mr-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Kembali ke Daftar
            </Link>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEditing ? 'Edit Item Inventory' : 'Tambah Item Inventory' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ isEditing ? 'Ubah informasi item' : 'Tambahkan item inventory baru' }}
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <form @submit.prevent="submit" class="space-y-6 p-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Informasi Dasar
                    </h3>
                    <div class="space-y-4">
                        <!-- SKU & Name -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="sku"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    SKU <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="sku"
                                    v-model="form.sku"
                                    type="text"
                                    required
                                    placeholder="Contoh: INV-001"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.sku }"
                                />
                                <p
                                    v-if="form.errors.sku"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.sku }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Nama Item <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Contoh: Mukena Bali Putih - M"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.name }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Category & Pattern -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="category"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="category"
                                    v-model="form.category"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.category }"
                                >
                                    <option
                                        v-for="cat in categories"
                                        :key="cat.value"
                                        :value="cat.value"
                                    >
                                        {{ cat.label }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.category"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.category }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="pattern_id"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ terminology.pattern || 'Pattern' }}
                                </label>
                                <select
                                    id="pattern_id"
                                    v-model="form.pattern_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.pattern_id }"
                                >
                                    <option :value="null">Pilih Pattern (opsional)</option>
                                    <option
                                        v-for="pattern in patterns"
                                        :key="pattern.id"
                                        :value="pattern.id"
                                    >
                                        {{ pattern.code }} - {{ pattern.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.pattern_id"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.pattern_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Location & Quality Grade -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="inventory_location_id"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Lokasi <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="inventory_location_id"
                                    v-model="form.inventory_location_id"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.inventory_location_id }"
                                >
                                    <option :value="null">Pilih Lokasi</option>
                                    <option
                                        v-for="location in locations"
                                        :key="location.id"
                                        :value="location.id"
                                    >
                                        {{ location.name }} ({{ location.zone }}-{{ location.rack }})
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.inventory_location_id"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.inventory_location_id }}
                                </p>
                            </div>

                            <div v-if="!isFood">
                                <label
                                    for="quality_grade"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Quality Grade <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="quality_grade"
                                    v-model="form.quality_grade"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.quality_grade }"
                                >
                                    <option
                                        v-for="grade in qualityGrades"
                                        :key="grade.value"
                                        :value="grade.value"
                                    >
                                        {{ grade.label }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.quality_grade"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.quality_grade }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Informasi Stock
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                for="current_stock"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Stock Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="current_stock"
                                v-model.number="form.current_stock"
                                type="number"
                                required
                                min="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.current_stock }"
                            />
                            <p
                                v-if="form.errors.current_stock"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.current_stock }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="reserved_stock"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Stock Reserved
                            </label>
                            <input
                                id="reserved_stock"
                                v-model.number="form.reserved_stock"
                                type="number"
                                min="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.reserved_stock }"
                            />
                            <p
                                v-if="form.errors.reserved_stock"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.reserved_stock }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="minimum_stock"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Minimum Stock <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="minimum_stock"
                                v-model.number="form.minimum_stock"
                                type="number"
                                required
                                min="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.minimum_stock }"
                            />
                            <p
                                v-if="form.errors.minimum_stock"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.minimum_stock }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Harga
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                for="unit_cost"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Harga Modal (COGS) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="unit_cost"
                                v-model="form.unit_cost"
                                type="number"
                                step="0.01"
                                required
                                min="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.unit_cost }"
                            />
                            <p
                                v-if="form.errors.unit_cost"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.unit_cost }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="selling_price"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Harga Jual <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="selling_price"
                                v-model="form.selling_price"
                                type="number"
                                step="0.01"
                                required
                                min="0"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.selling_price }"
                            />
                            <p
                                v-if="form.errors.selling_price"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.selling_price }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Informasi Tambahan
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="batch_number"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Batch Number
                                </label>
                                <input
                                    id="batch_number"
                                    v-model="form.batch_number"
                                    type="text"
                                    placeholder="Contoh: BATCH-2024-001"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.batch_number }"
                                />
                                <p
                                    v-if="form.errors.batch_number"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.batch_number }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.status }"
                                >
                                    <option
                                        v-for="status in statuses"
                                        :key="status.value"
                                        :value="status.value"
                                    >
                                        {{ status.label }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.status"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="production_date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Tanggal Produksi
                                </label>
                                <input
                                    id="production_date"
                                    v-model="form.production_date"
                                    type="date"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.production_date }"
                                />
                                <p
                                    v-if="form.errors.production_date"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.production_date }}
                                </p>
                            </div>

                            <div v-if="isFood">
                                <label
                                    for="expiry_date"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Tanggal Kadaluarsa
                                </label>
                                <input
                                    id="expiry_date"
                                    v-model="form.expiry_date"
                                    type="date"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': form.errors.expiry_date }"
                                />
                                <p
                                    v-if="form.errors.expiry_date"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ form.errors.expiry_date }}
                                </p>
                            </div>
                        </div>

                        <div v-if="isFood">
                            <label
                                for="storage_requirements"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Storage Requirements
                            </label>
                            <select
                                id="storage_requirements"
                                v-model="form.storage_requirements"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.storage_requirements }"
                            >
                                <option value="">Pilih Storage Requirement</option>
                                <option
                                    v-for="req in storageRequirements"
                                    :key="req.value"
                                    :value="req.value"
                                >
                                    {{ req.label }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.storage_requirements"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.storage_requirements }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="notes"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Catatan
                            </label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                placeholder="Catatan tambahan (opsional)"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :class="{ 'border-red-300': form.errors.notes }"
                            />
                            <p
                                v-if="form.errors.notes"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link
                        href="/inventory/items"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update Item' : 'Tambah Item' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
