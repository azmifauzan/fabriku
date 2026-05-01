<script setup lang="ts">
import { Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { Camera, Upload, X, Package, Truck, RotateCcw, FileText, Plus, Tag } from 'lucide-vue-next';
import CameraCaptureModal from '@/components/CameraCaptureModal.vue';

interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number;
}

interface Pattern {
    id: number;
    name: string;
    code: string;
    output_quantity: number;
}

interface PreparationOrder {
    pattern: Pattern;
    output_quantity: number;
    output_unit: string;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    preparation_order_id: number;
    labor_cost: string;
    completed_date: string;
    estimated_completion_date: string;
    status: string;
    material_cost?: number;
    preparation_order?: PreparationOrder;
}

interface Category {
    id: number;
    name: string;
}

interface Item {
    id?: number;
    sku: string;
    name: string;
    production_order_id?: number;
    source_type?: string;
    category_id?: number;
    inventory_location_id: number;
    target_quantity: number;
    current_stock: number;
    unit_cost: string;
    selling_price: string;
    production_date?: string;
    status: string;
    notes?: string;
    image_url?: string;
}

const props = defineProps<{
    item?: Item;
    locations: Location[];
    productionOrders: ProductionOrder[];
    categories: Category[];
    allowManualEntry?: boolean;
    sourceTypes?: Record<string, string>;
}>();

// Entry type: 'production' or 'manual'
const entryType = ref<'production' | 'manual'>(
    props.item?.production_order_id ? 'production' : (props.item?.id ? 'manual' : 'production')
);

// Source type for manual entry
const selectedSourceType = ref<string>(props.item?.source_type || 'opening_balance');

const form = useForm({
    production_order_id: props.item?.production_order_id || null,
    source_type: props.item?.source_type || 'production',
    category_id: props.item?.category_id || null,
    sku: props.item?.sku || '',
    name: props.item?.name || '',
    inventory_location_id: props.item?.inventory_location_id || null,
    target_quantity: props.item?.target_quantity || 0,
    current_stock: props.item?.current_stock || 0,
    unit_cost: props.item?.unit_cost || '0',
    selling_price: props.item?.selling_price || '0',
    notes: props.item?.notes || '',
    image: null as File | null,
});

// Watch entry type changes to reset related fields
watch(entryType, (newValue) => {
    if (newValue === 'manual') {
        form.production_order_id = null;
        form.source_type = selectedSourceType.value;
    } else {
        form.source_type = 'production';
    }
});

watch(selectedSourceType, (newValue) => {
    if (entryType.value === 'manual') {
        form.source_type = newValue;
    }
});

// Auto-populate fields when production order is selected
const selectedProductionOrder = computed(() => {
    if (!form.production_order_id) return null;
    return props.productionOrders.find((po) => po.id === form.production_order_id);
});

// Format date helper
const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
};

// Get unit from production order
const productionUnit = computed(() => {
    return selectedProductionOrder.value?.preparation_order?.output_unit || 'pcs';
});

// Is manual entry mode
const isManualEntry = computed(() => entryType.value === 'manual');

watch(
    () => form.production_order_id,
    (newValue) => {
        if (newValue && selectedProductionOrder.value) {
            const po = selectedProductionOrder.value;

            // Auto-populate name from pattern if available
            if (po.preparation_order?.pattern) {
                const pattern = po.preparation_order.pattern;
                form.name = `${pattern.name}`;
            }
            
            // Auto-populate target_quantity from preparation order output_quantity
            if (po.preparation_order?.output_quantity) {
                form.target_quantity = po.preparation_order.output_quantity;
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

        // Material cost calculated from backend
        const materialCost = selectedProductionOrder.value.material_cost || 0;

        form.unit_cost = (materialCost + laborCost).toFixed(2);
    }
});

const submit = () => {
    if (props.item?.id) {
        form.put(`/inventory/items/${props.item.id}`, {
            preserveScroll: true,
            forceFormData: true,
        });
    } else {
        form.post('/inventory/items', {
            preserveScroll: true,
            forceFormData: true,
        });
    }
};

const isEditing = !!props.item?.id;

const showCameraModal = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const previewImage = ref<string | null>(null);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        processFile(target.files[0]);
    }
};

const handleCameraCapture = (file: File) => {
    processFile(file);
};

const processFile = (file: File) => {
    form.image = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImage.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const clearImage = () => {
    form.image = null;
    previewImage.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Source type icons
const getSourceTypeIcon = (type: string) => {
    switch (type) {
        case 'opening_balance':
            return FileText;
        case 'purchase':
            return Truck;
        case 'return':
            return RotateCcw;
        default:
            return Package;
    }
};

// Add category modal
const showAddCategoryModal = ref(false);
const categoryList = ref<Category[]>([...props.categories]);
const addCategoryName = ref('');
const addCategoryDescription = ref('');
const addCategoryProcessing = ref(false);
const addCategoryErrors = ref<{ name?: string }>({});

const resetAddCategoryForm = () => {
    addCategoryName.value = '';
    addCategoryDescription.value = '';
    addCategoryErrors.value = {};
};

const addCategory = async () => {
    if (!addCategoryName.value.trim()) return;

    addCategoryProcessing.value = true;
    addCategoryErrors.value = {};

    try {
        const response = await axios.post('/inventory/categories', {
            name: addCategoryName.value,
            description: addCategoryDescription.value || null,
        });
        const newCategory: Category = response.data;
        categoryList.value.push(newCategory);
        form.category_id = newCategory.id;
        showAddCategoryModal.value = false;
        resetAddCategoryForm();
    } catch (error: any) {
        if (error.response?.data?.errors?.name) {
            addCategoryErrors.value = { name: error.response.data.errors.name[0] };
        } else {
            addCategoryErrors.value = { name: 'Gagal menyimpan kategori.' };
        }
    } finally {
        addCategoryProcessing.value = false;
    }
};
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
                <!-- Entry Type Selection -->
                <div v-if="allowManualEntry">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Sumber Inventory</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Production Order Option -->
                        <label 
                            class="relative flex cursor-pointer rounded-lg border p-4 transition-all"
                            :class="entryType === 'production' 
                                ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500 dark:bg-indigo-900/20' 
                                : 'border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700'"
                        >
                            <input 
                                type="radio" 
                                v-model="entryType" 
                                value="production"
                                class="sr-only" 
                            />
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full" 
                                     :class="entryType === 'production' ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300'">
                                    <Package class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Dari Production Order</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Item hasil produksi internal/eksternal</p>
                                </div>
                            </div>
                        </label>

                        <!-- Manual Entry Option -->
                        <label 
                            class="relative flex cursor-pointer rounded-lg border p-4 transition-all"
                            :class="entryType === 'manual' 
                                ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500 dark:bg-indigo-900/20' 
                                : 'border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700'"
                        >
                            <input 
                                type="radio" 
                                v-model="entryType" 
                                value="manual"
                                class="sr-only" 
                            />
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full" 
                                     :class="entryType === 'manual' ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300'">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Manual Entry / Stock Awal</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Input manual untuk stock awal, pembelian, retur</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Source Type Selection (for Manual Entry) -->
                <div v-if="isManualEntry && sourceTypes" class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <h4 class="mb-3 font-medium text-blue-900 dark:text-blue-300">Tipe Sumber</h4>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <label 
                            v-for="(label, type) in sourceTypes" 
                            :key="type"
                            v-show="type !== 'production'"
                            class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 transition-all"
                            :class="selectedSourceType === type 
                                ? 'border-blue-500 bg-blue-100 dark:bg-blue-800/30' 
                                : 'border-blue-200 bg-white hover:bg-blue-50 dark:border-blue-700 dark:bg-blue-900/10 dark:hover:bg-blue-900/20'"
                        >
                            <input 
                                type="radio" 
                                v-model="selectedSourceType" 
                                :value="type"
                                class="text-blue-600 focus:ring-blue-500" 
                            />
                            <component :is="getSourceTypeIcon(type)" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            <span class="text-sm text-blue-900 dark:text-blue-200">{{ label }}</span>
                        </label>
                    </div>
                    <p class="mt-3 text-xs text-blue-700 dark:text-blue-400">
                        <span v-if="selectedSourceType === 'opening_balance'">
                            <strong>Stock Awal:</strong> Gunakan untuk mencatat stock yang sudah ada sebelum menggunakan sistem.
                        </span>
                        <span v-else-if="selectedSourceType === 'purchase'">
                            <strong>Pembelian:</strong> Gunakan untuk barang jadi yang dibeli langsung dari supplier.
                        </span>
                        <span v-else-if="selectedSourceType === 'return'">
                            <strong>Retur:</strong> Gunakan untuk barang yang dikembalikan oleh customer.
                        </span>
                    </p>
                </div>

                <!-- Production Order Selection (only for production type) -->
                <div v-if="!isManualEntry">
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
                                {{ po.order_number }} - {{ po.preparation_order?.pattern?.name || 'N/A' }} - [{{ po.status }}]
                            </option>
                        </select>
                        <p v-if="form.errors.production_order_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.production_order_id }}
                        </p>
                        <p v-if="productionOrders.length === 0" class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                            Tidak ada production order yang tersedia. Gunakan "Manual Entry" untuk menambah stock awal.
                        </p>
                    </div>
                </div>

                <!-- Production Order Info Box -->
                <div v-if="!isManualEntry && selectedProductionOrder" class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-900/20">
                    <h3 class="mb-3 text-sm font-semibold text-indigo-900 dark:text-indigo-300">Informasi Production Order</h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">Nomor Order</p>
                            <p class="mt-1 font-medium text-indigo-900 dark:text-indigo-200">{{ selectedProductionOrder.order_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">Produk</p>
                            <p class="mt-1 font-medium text-indigo-900 dark:text-indigo-200">
                                {{ selectedProductionOrder.preparation_order?.pattern?.name || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">Status</p>
                            <span class="mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold" 
                                  :class="selectedProductionOrder.status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'">
                                {{ selectedProductionOrder.status === 'completed' ? 'Selesai' : 'Terkirim' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">Tanggal Selesai</p>
                            <p class="mt-1 font-medium text-indigo-900 dark:text-indigo-200">
                                {{ formatDate(selectedProductionOrder.completed_date || selectedProductionOrder.estimated_completion_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">Target Produksi</p>
                            <p class="mt-1 font-medium text-indigo-900 dark:text-indigo-200">
                                {{ selectedProductionOrder.preparation_order?.output_quantity || 0 }} {{ productionUnit }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">Biaya Tenaga Kerja</p>
                            <p class="mt-1 font-medium text-indigo-900 dark:text-indigo-200">
                                Rp {{ parseFloat(selectedProductionOrder.labor_cost || '0').toLocaleString('id-ID') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Product Name (for Manual Entry) -->
                <div v-if="isManualEntry">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Informasi Produk</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Produk <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="Contoh: Mukena Bali Putih, Kue Lapis Legit, dll"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kategori
                            </label>
                            <div class="flex gap-2">
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-300': form.errors.category_id }"
                                >
                                    <option :value="null">Pilih Kategori (opsional)</option>
                                    <option v-for="cat in categoryList" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <button
                                    type="button"
                                    @click="showAddCategoryModal = true"
                                    class="flex items-center gap-1 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm text-indigo-700 transition-colors hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/40"
                                    title="Tambah kategori baru"
                                >
                                    <Plus class="h-4 w-4" />
                                    <span class="hidden sm:inline">Tambah</span>
                                </button>
                            </div>
                            <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.category_id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stock Information -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Data Stock</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Jumlah Target: only shown for production order entry -->
                        <div v-if="!isManualEntry">
                            <label for="target_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Target Produksi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="target_quantity"
                                    v-model.number="form.target_quantity"
                                    type="number"
                                    required
                                    min="0"
                                    readonly
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-16 text-sm shadow-sm transition-all bg-gray-50 dark:bg-gray-800 dark:text-gray-400"
                                    :class="{ 'border-red-300': form.errors.target_quantity }"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ productionUnit }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dari production order</p>
                            <p v-if="form.errors.target_quantity" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.target_quantity }}
                            </p>
                        </div>

                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ isManualEntry ? 'Jumlah Stock' : 'Hasil Produksi Aktual' }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="current_stock"
                                    v-model.number="form.current_stock"
                                    type="number"
                                    required
                                    min="0"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-16 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-300': form.errors.current_stock }"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ productionUnit }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ isManualEntry ? 'Jumlah stock saat ini' : 'Jumlah barang jadi yang sebenarnya' }}
                            </p>
                            <p v-if="form.errors.current_stock" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.current_stock }}
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
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">Rp</span>
                                <input
                                    id="unit_cost"
                                    v-model="form.unit_cost"
                                    type="number"
                                    step="0.01"
                                    required
                                    min="0"
                                    :readonly="!isManualEntry"
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm shadow-sm transition-all"
                                    :class="[
                                        { 'border-red-300': form.errors.unit_cost },
                                        !isManualEntry ? 'bg-gray-50 dark:bg-gray-800 dark:text-gray-400' : 'focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white'
                                    ]"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ isManualEntry ? 'Masukkan harga modal per unit' : 'Dihitung otomatis dari biaya bahan + biaya produksi' }}
                            </p>
                            <p v-if="form.errors.unit_cost" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.unit_cost }}
                            </p>
                        </div>

                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Jual <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">Rp</span>
                                <input
                                    id="selling_price"
                                    v-model="form.selling_price"
                                    type="number"
                                    step="0.01"
                                    required
                                    min="0"
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-300': form.errors.selling_price }"
                                />
                            </div>
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
                                    {{ location.name }} ({{ location.code }})
                                </option>
                            </select>
                            <p v-if="form.errors.inventory_location_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.inventory_location_id }}
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Produk</label>
                            <div class="mt-2 flex items-center gap-6">
                                <div v-if="previewImage || item?.image_url" class="relative h-32 w-32 flex-shrink-0 overflow-hidden rounded-lg border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-700">
                                    <img :src="previewImage || item?.image_url" alt="Preview" class="h-full w-full object-cover" />
                                    <!-- Clear Image Button -->
                                    <button 
                                        v-if="form.image || previewImage"
                                        type="button" 
                                        @click="clearImage"
                                        class="absolute top-1 right-1 rounded-full bg-red-600 p-1 text-white shadow-sm hover:bg-red-700"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                                <div class="flex-1 space-y-3">
                                    <div class="flex flex-wrap gap-3">
                                        <!-- File Upload Button -->
                                        <label class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                            <Upload class="h-4 w-4" />
                                            <span>Upload File</span>
                                            <input
                                                ref="fileInput"
                                                type="file"
                                                accept="image/*"
                                                @change="handleFileChange"
                                                class="hidden"
                                            />
                                        </label>

                                        <!-- Camera Button -->
                                        <button 
                                            type="button"
                                            @click="showCameraModal = true"
                                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-600 transition-all hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                                        >
                                            <Camera class="h-4 w-4" />
                                            <span>Ambil Foto</span>
                                        </button>
                                    </div>

                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB
                                    </p>
                                    <p v-if="form.image" class="text-sm text-gray-900 dark:text-gray-100">
                                        File terpilih: <span class="font-medium">{{ form.image.name }}</span>
                                    </p>
                                    <p v-if="form.errors.image" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.image }}
                                    </p>
                                </div>
                            </div>
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
        
        <!-- Camera Capture Modal -->
        <CameraCaptureModal 
            :show="showCameraModal"
            @close="showCameraModal = false"
            @capture="handleCameraCapture"
        />

        <!-- Add Category Modal -->
        <Teleport to="body">
            <div v-if="showAddCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div class="w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <Tag class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Kategori</h3>
                        </div>
                        <button
                            type="button"
                            @click="showAddCategoryModal = false; resetAddCategoryForm();"
                            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <label for="new_category_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Kategori <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="new_category_name"
                                v-model="addCategoryName"
                                type="text"
                                placeholder="Contoh: Pakaian Wanita, Tas, Sepatu"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': addCategoryErrors.name }"
                                @keyup.enter="addCategory"
                            />
                            <p v-if="addCategoryErrors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ addCategoryErrors.name }}
                            </p>
                        </div>

                        <div>
                            <label for="new_category_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Deskripsi <span class="text-gray-400 text-xs font-normal">(opsional)</span>
                            </label>
                            <input
                                id="new_category_description"
                                v-model="addCategoryDescription"
                                type="text"
                                placeholder="Deskripsi singkat kategori"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <button
                            type="button"
                            @click="showAddCategoryModal = false; resetAddCategoryForm();"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="addCategory"
                            :disabled="addCategoryProcessing || !addCategoryName.trim()"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ addCategoryProcessing ? 'Menyimpan...' : 'Simpan Kategori' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
