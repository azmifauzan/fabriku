<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
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
    description?: string;
    estimated_cost?: number;
    materials?: PatternMaterial[];
}

interface Props {
    pattern?: Pattern;
    materials: Material[];
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const { term, termLower } = useBusinessContext();

const patternLabel = computed(() => term('pattern', 'Pattern'));
const materialLabel = computed(() => term('material', 'Bahan Baku'));
const materialLabelLower = computed(() => termLower('material', 'bahan'));

const form = useForm({
    code: props.pattern?.code || '',
    name: props.pattern?.name || '',
    description: props.pattern?.description || '',
    materials:
        props.pattern?.materials?.map((pm) => ({
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
    return props.materials.find((m) => m.id === materialId);
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
watch(
    bomItems,
    (newBomItems) => {
        form.materials = newBomItems
            .filter((item) => item.material_id > 0)
            .map((item) => ({
                material_id: item.material_id,
                quantity_needed: item.quantity_needed,
                notes: item.notes || '',
            }));
    },
    { deep: true },
);

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
    <AppLayout>
        <Head :title="isEdit ? `Edit ${patternLabel}` : `Buat ${patternLabel} Baru`" />

        <!-- Page Content -->
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ isEdit ? `Edit ${patternLabel}` : `Buat ${patternLabel} Baru` }}
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ isEdit ? `Ubah informasi ${patternLabel.toLowerCase()}` : `Tambahkan ${patternLabel.toLowerCase()} baru` }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="goBack"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                            >
                                ← Kembali
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Pattern Information -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informasi {{ patternLabel }}</h3>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <!-- Code -->
                                    <div>
                                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Kode {{ patternLabel }} <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="code"
                                            v-model="form.code"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            required
                                        />
                                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.code }}
                                        </p>
                                    </div>

                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nama {{ patternLabel }} <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            required
                                        />
                                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Deskripsi </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    ></textarea>
                                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- BOM (Bill of Materials) -->
                            <div class="mt-8 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bill of Materials (BOM)</h3>
                                    <button
                                        type="button"
                                        @click="addBomItem"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                                    >
                                        + Tambah {{ materialLabel }}
                                    </button>
                                </div>

                                <div
                                    v-if="bomItems.length === 0"
                                    class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center dark:border-gray-600"
                                >
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada {{ materialLabelLower }}. Klik "Tambah {{ materialLabel }}" untuk menambahkan.
                                    </p>
                                </div>

                                <div v-else class="space-y-4">
                                    <div
                                        v-for="(item, index) in bomItems"
                                        :key="index"
                                        class="rounded-lg border border-gray-300 p-4 dark:border-gray-600"
                                    >
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                            <!-- Material -->
                                            <div class="sm:col-span-5">
                                                <label :for="`material-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ materialLabel }} <span class="text-red-500">*</span>
                                                </label>
                                                <select
                                                    :id="`material-${index}`"
                                                    v-model="item.material_id"
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                    required
                                                >
                                                    <option :value="0">-- Pilih {{ materialLabel }} --</option>
                                                    <option v-for="material in materials" :key="material.id" :value="material.id">
                                                        {{ material.name }} ({{ material.code }})
                                                    </option>
                                                </select>
                                                <p v-if="item.material_id > 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Stok: {{ getMaterial(item.material_id)?.current_stock || 0 }}
                                                    {{ getMaterial(item.material_id)?.unit }}
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
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                    required
                                                />
                                                <p v-if="item.material_id > 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ getMaterial(item.material_id)?.unit }}
                                                </p>
                                            </div>

                                            <!-- Cost -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Biaya </label>
                                                <div
                                                    class="mt-1 rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:bg-gray-700 dark:text-white"
                                                >
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
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
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
                                            <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ totalCost.toLocaleString('id-ID') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <p v-if="form.errors.materials" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.materials }}
                                </p>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-6 flex flex-col items-stretch justify-end gap-3 sm:flex-row sm:items-center">
                                <button
                                    type="button"
                                    @click="goBack"
                                    class="order-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:order-1 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-600"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="order-1 inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 sm:order-2"
                                >
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
