<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Material {
    id: number;
    name: string;
    code: string;
    unit: string;
    standard_price: number;
    current_stock: number;
}

interface PatternMaterial {
    material_id: number;
    quantity_needed: number;
    notes?: string;
    material?: Material;
}

interface Pattern {
    id?: number;
    code: string;
    name: string;
    product_type: string;
    size?: string;
    description?: string;
    is_active: boolean;
    estimated_cost?: number;
    materials?: PatternMaterial[];
}

interface Props {
    pattern?: Pattern;
    materials: Material[];
    productTypes: Record<string, string>;
    sizes: string[];
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const form = useForm({
    code: props.pattern?.code || '',
    name: props.pattern?.name || '',
    product_type: props.pattern?.product_type || '',
    size: props.pattern?.size || '',
    description: props.pattern?.description || '',
    is_active: props.pattern?.is_active ?? true,
    materials: props.pattern?.materials?.map(pm => ({
        material_id: pm.material_id,
        quantity_needed: pm.quantity_needed,
        notes: pm.notes || '',
    })) || [],
});

const bomItems = ref<PatternMaterial[]>(form.materials);

// Add new BOM item
const addBomItem = () => {
    bomItems.value.push({
        material_id: 0,
        quantity_needed: 0,
        notes: '',
    });
};

// Remove BOM item
const removeBomItem = (index: number) => {
    bomItems.value.splice(index, 1);
};

// Get material by ID
const getMaterial = (materialId: number): Material | undefined => {
    return props.materials.find(m => m.id === materialId);
};

// Calculate unit cost for a BOM item
const calculateItemCost = (item: PatternMaterial): number => {
    const material = getMaterial(item.material_id);
    if (!material) return 0;
    return material.standard_price * item.quantity_needed;
};

// Calculate total pattern cost
const totalCost = computed(() => {
    return bomItems.value.reduce((sum, item) => {
        return sum + calculateItemCost(item);
    }, 0);
});

// Sync bomItems with form.materials
watch(bomItems, (newBomItems) => {
    form.materials = newBomItems
        .filter(item => item.material_id > 0)
        .map(item => ({
            material_id: item.material_id,
            quantity_needed: item.quantity_needed,
            notes: item.notes || '',
        }));
}, { deep: true });

const submit = () => {
    if (props.isEdit && props.pattern?.id) {
        form.put(`/patterns/${props.pattern.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/patterns', {
            preserveScroll: true,
        });
    }
};

const goBack = () => {
    router.visit('/patterns');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <Head :title="isEdit ? 'Edit Pattern' : 'Buat Pattern Baru'" />

        <!-- Navigation -->
        <nav class="bg-white shadow-sm dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <h1 class="text-xl font-bold text-indigo-600">Fabriku</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                href="/dashboard"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Dashboard
                            </Link>
                            <Link
                                href="/materials"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Bahan Baku
                            </Link>
                            <Link
                                href="/patterns"
                                class="inline-flex items-center border-b-2 border-indigo-500 px-1 pt-1 text-sm font-medium text-gray-900 dark:text-gray-100"
                            >
                                Pattern
                            </Link>
                            <Link
                                href="/cutting-orders"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Cutting Orders
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ isEdit ? 'Edit Pattern' : 'Buat Pattern Baru' }}
            </h2>
        </div>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Pattern Information -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Informasi Pattern
                            </h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Code -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kode Pattern <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="code"
                                        v-model="form.code"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        required
                                    />
                                    <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nama Pattern <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Product Type -->
                                <div>
                                    <label for="product_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Jenis Produk <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="product_type"
                                        v-model="form.product_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        required
                                    >
                                        <option value="">-- Pilih Jenis Produk --</option>
                                        <option v-for="(label, value) in productTypes" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.product_type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.product_type }}
                                    </p>
                                </div>

                                <!-- Size -->
                                <div>
                                    <label for="size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Ukuran
                                    </label>
                                    <select
                                        id="size"
                                        v-model="form.size"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">-- Pilih Ukuran --</option>
                                        <option v-for="size in sizes" :key="size" :value="size">
                                            {{ size }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.size" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.size }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Deskripsi
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                />
                                <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Pattern Aktif
                                </label>
                            </div>
                        </div>

                        <!-- BOM (Bill of Materials) -->
                        <div class="mt-8 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Bill of Materials (BOM)
                                </h3>
                                <button
                                    type="button"
                                    @click="addBomItem"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                                >
                                    + Tambah Bahan
                                </button>
                            </div>

                            <div v-if="bomItems.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada bahan. Klik "Tambah Bahan" untuk menambahkan.
                                </p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(item, index) in bomItems"
                                    :key="index"
                                    class="rounded-lg border border-gray-300 p-4 dark:border-gray-700"
                                >
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                        <!-- Material -->
                                        <div class="sm:col-span-5">
                                            <label :for="`material-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Bahan <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                :id="`material-${index}`"
                                                v-model="item.material_id"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                required
                                            >
                                                <option :value="0">-- Pilih Bahan --</option>
                                                <option v-for="material in materials" :key="material.id" :value="material.id">
                                                    {{ material.name }} ({{ material.code }})
                                                </option>
                                            </select>
                                            <p v-if="item.material_id > 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Stok: {{ getMaterial(item.material_id)?.current_stock || 0 }} {{ getMaterial(item.material_id)?.unit }}
                                            </p>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="sm:col-span-2">
                                            <label :for="`quantity-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Jumlah <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                :id="`quantity-${index}`"
                                                v-model.number="item.quantity_needed"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                required
                                            />
                                            <p v-if="item.material_id > 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ getMaterial(item.material_id)?.unit }}
                                            </p>
                                        </div>

                                        <!-- Cost -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Biaya
                                            </label>
                                            <div class="mt-1 rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-300">
                                                Rp {{ calculateItemCost(item).toLocaleString('id-ID') }}
                                            </div>
                                        </div>

                                        <!-- Notes -->
                                        <div class="sm:col-span-2">
                                            <label :for="`notes-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Catatan
                                            </label>
                                            <input
                                                :id="`notes-${index}`"
                                                v-model="item.notes"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            />
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="flex items-end sm:col-span-1">
                                            <button
                                                type="button"
                                                @click="removeBomItem(index)"
                                                class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Cost -->
                                <div class="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Estimasi Biaya</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                            Rp {{ totalCost.toLocaleString('id-ID') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <p v-if="form.errors.materials" class="mt-1 text-sm text-red-600">
                                {{ form.errors.materials }}
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex items-center justify-end gap-x-4">
                            <button
                                type="button"
                                @click="goBack"
                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700 dark:hover:bg-gray-700"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
