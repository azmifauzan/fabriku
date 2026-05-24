<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Factory, Plus, Trash2, ArrowLeft, AlertCircle, Info, Sparkles, BookOpen } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

interface InventoryItem {
    id: number;
    sku: string;
    product_name: string;
    current_quantity: number;
    unit_cost: number;
}

interface MaterialReceipt {
    id: number;
    receipt_number: string;
    batch_number: string | null;
    remaining_quantity: number;
    price_per_unit: number;
}

interface Material {
    id: number;
    code: string;
    name: string;
    unit: string;
    stock_quantity: number;
    receipts?: MaterialReceipt[];
}

interface Pattern {
    id: number;
    code: string;
    name: string;
    output_quantity: number;
}

interface Location {
    id: number;
    name: string;
    code: string;
}

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    inventoryItems: InventoryItem[];
    materials: Material[];
    patterns: Pattern[];
    locations: Location[];
    categories: Category[];
}>();

const isNewProduct = ref(false);
const selectedPatternId = ref<number | null>(null);

const form = useForm({
    inventory_item_id: null as number | null,
    product_name: '',
    product_code: '',
    location_id: props.locations[0]?.id ?? null,
    category_id: null as number | null,
    quantity: 1,
    notes: '',
    production_date: new Date().toISOString().substring(0, 10),
    expired_date: '',
    pattern_id: null as number | null,
    materials: [
        { material_id: null as number | null, quantity: 1 }
    ]
});

// Watch product type selection
watch(isNewProduct, (val) => {
    if (val) {
        form.inventory_item_id = null;
    } else {
        form.product_name = '';
        form.product_code = '';
        form.category_id = null;
    }
});

// Watch recipe/pattern selection to auto-populate materials if any
watch(selectedPatternId, (patternId) => {
    form.pattern_id = patternId;
    if (patternId) {
        // If a standard recipe is chosen, we can check if there are materials configured in the recipe
        // Since we don't have recipe details in the pattern array directly, let's keep it simple: 
        // User can manually configure, or if there's a matching recipe template, they can use it.
        // For simplicity, we just keep the form flexible!
    }
});

const addMaterialRow = () => {
    form.materials.push({ material_id: null, quantity: 1 });
};

const removeMaterialRow = (index: number) => {
    form.materials.splice(index, 1);
    if (form.materials.length === 0) {
        addMaterialRow();
    }
};

const getMaterialInfo = (materialId: number | null) => {
    if (!materialId) return null;
    return props.materials.find(m => m.id === materialId) ?? null;
};

// Check if there is enough stock for a given material row
const hasInsufficientStock = (materialId: number | null, qty: number) => {
    if (!materialId) return false;
    const material = getMaterialInfo(materialId);
    return material ? material.stock_quantity < qty : false;
};

const submitForm = () => {
    form.post('/simple-production', {
        onSuccess: () => {
            // Handled automatically by Inertia redirects
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Catat Produksi Rumahan" />

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
                            <span>Catat Produksi Rumahan</span>
                            <Sparkles :size="16" class="text-indigo-500" />
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tambahkan stok produk jadi dan kurangi bahan baku dalam satu langkah mudah</p>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Finished Goods Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 space-y-6">
                        <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-700">
                            <Factory :size="18" class="text-indigo-600 dark:text-indigo-400" />
                            <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200">Hasil Produksi (Produk Jadi)</h2>
                        </div>

                        <!-- Error Alert -->
                        <div v-if="form.errors.materials" class="flex items-start gap-2.5 rounded-xl bg-red-50 p-4 text-sm text-red-700 dark:bg-red-950/20 dark:text-red-400">
                            <AlertCircle :size="16" class="mt-0.5 shrink-0" />
                            <p class="font-medium">{{ form.errors.materials }}</p>
                        </div>

                        <!-- Selector: New or Existing Product -->
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input
                                    type="radio"
                                    :value="false"
                                    v-model="isNewProduct"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Produk Eksisting</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input
                                    type="radio"
                                    :value="true"
                                    v-model="isNewProduct"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Buat Produk Baru Inline</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Existing Product Selector -->
                            <div v-if="!isNewProduct" class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Produk Jadi <span class="text-red-500">*</span></label>
                                <select
                                    v-model="form.inventory_item_id"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option :value="null" disabled>-- Pilih Produk Jadi --</option>
                                    <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
                                        {{ item.product_name }} ({{ item.sku }}) - Stok: {{ item.current_quantity }}
                                    </option>
                                </select>
                                <p v-if="form.errors.inventory_item_id" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.inventory_item_id }}</p>
                            </div>

                            <!-- Inline New Product Inputs -->
                            <template v-else>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Produk Baru <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        v-model="form.product_name"
                                        required
                                        placeholder="Contoh: Coklat Praline Premium"
                                        class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                    />
                                    <p v-if="form.errors.product_name" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.product_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kode Produk / SKU (Opsional)</label>
                                    <input
                                        type="text"
                                        v-model="form.product_code"
                                        placeholder="Akan digenerate otomatis jika kosong"
                                        class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kategori Produk (Opsional)</label>
                                    <select
                                        v-model="form.category_id"
                                        class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                        <option :value="null">-- Tanpa Kategori --</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                            </template>

                            <!-- Common Production Details -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Resep / Formula (Opsional)</label>
                                <select
                                    v-model="selectedPatternId"
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option :value="null">-- Tanpa Resep (Input Manual) --</option>
                                    <option v-for="pat in patterns" :key="pat.id" :value="pat.id">{{ pat.name }} ({{ pat.code }})</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Lokasi Penyimpanan <span class="text-red-500">*</span></label>
                                <select
                                    v-model="form.location_id"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }} ({{ loc.code }})</option>
                                </select>
                                <p v-if="form.errors.location_id" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.location_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jumlah Yang Dihasilkan <span class="text-red-500">*</span></label>
                                <input
                                    type="number"
                                    v-model.number="form.quantity"
                                    required
                                    min="1"
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                />
                                <p v-if="form.errors.quantity" class="text-xs text-red-500 mt-1 font-medium">{{ form.errors.quantity }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal Produksi <span class="text-red-500">*</span></label>
                                <input
                                    type="date"
                                    v-model="form.production_date"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal Kadaluarsa (Expired Date)</label>
                                <input
                                    type="date"
                                    v-model="form.expired_date"
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Catatan Produksi</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    placeholder="Masukkan nomor batch produksi, catatan rasa, dll..."
                                    class="w-full px-4 py-2.5 border border-gray-250 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Material Usage Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800 space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <BookOpen :size="18" class="text-purple-600 dark:text-purple-400" />
                                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200">Bahan Baku Yang Dikonsumsi</h2>
                            </div>
                            <button
                                type="button"
                                @click="addMaterialRow"
                                class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 cursor-pointer"
                            >
                                <Plus :size="14" />
                                Tambah Bahan
                            </button>
                        </div>

                        <!-- Multi-row materials -->
                        <div class="space-y-4">
                            <div
                                v-for="(row, idx) in form.materials"
                                :key="idx"
                                class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end rounded-xl border border-gray-100 p-4 dark:border-gray-700 relative"
                            >
                                <!-- Material Dropdown -->
                                <div class="sm:col-span-6">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Pilih Bahan Baku</label>
                                    <select
                                        v-model="row.material_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-250 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                        <option :value="null" disabled>-- Pilih Bahan Baku --</option>
                                        <option v-for="mat in materials" :key="mat.id" :value="mat.id">
                                            {{ mat.name }} ({{ mat.code }}) - Stok: {{ mat.stock_quantity }} {{ mat.unit }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Quantity to deduct -->
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Jumlah Dipakai</label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            v-model.number="row.quantity"
                                            required
                                            min="0.001"
                                            step="any"
                                            class="w-full px-3 py-2 border border-gray-250 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-750 dark:bg-gray-900 dark:text-gray-100"
                                        />
                                        <span v-if="row.material_id" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">
                                            {{ getMaterialInfo(row.material_id)?.unit }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="sm:col-span-2 flex justify-end">
                                    <button
                                        type="button"
                                        @click="removeMaterialRow(idx)"
                                        class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg dark:hover:bg-red-950/20 shrink-0 cursor-pointer"
                                        title="Hapus baris"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>

                                <!-- Insufficient stock alert -->
                                <div
                                    v-if="row.material_id && hasInsufficientStock(row.material_id, row.quantity)"
                                    class="col-span-12 flex items-center gap-1.5 mt-2 text-xs font-bold text-red-600 dark:text-red-400"
                                >
                                    <AlertCircle :size="14" />
                                    <span>Peringatan: Jumlah yang dimasukkan melebihi stok yang tersedia ({{ getMaterialInfo(row.material_id)?.stock_quantity }} {{ getMaterialInfo(row.material_id)?.unit }}).</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            href="/simple-production"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-sm font-semibold text-white shadow-md hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-all cursor-pointer"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Catatan Produksi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
